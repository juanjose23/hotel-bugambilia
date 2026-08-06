<?php

namespace App\Filament\Resources\Catalogos\Productos\RelationManagers;

use App\Actions\Catalogos\GenerarCodigoVarianteProducto;
use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Columns\FechaStandardColumn;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Filament\Shared\Infolists\TimestampsInfolistEntry;
use App\Repository\Models\Catalogos\Producto;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VariantesRelationManager extends RelationManager
{
    protected static string $relationship = 'variantes';

    protected static ?string $title = 'Variantes del producto';

    protected static ?string $label = 'variante';

    protected static ?string $pluralLabel = 'variantes';

    private function generarCodigoVariante(): ?string
    {
        $producto = $this->getOwnerRecord();

        return $producto instanceof Producto
            ? app(GenerarCodigoVarianteProducto::class)->ejecutar($producto)
            : null;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema(components: [
                Section::make('Información General')
                    ->description('Detalles del producto')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código (SKU)')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->default(fn (): ?string => $this->generarCodigoVariante())
                            ->helperText('Generado automáticamente, único y editable.')
                            ->prefixIcon(Heroicon::QrCode),

                        TextInput::make('nombre_variante')
                            ->label('Nombre de la variante')
                            ->required()
                            ->maxLength(200)
                            ->prefixIcon(Heroicon::Identification),

                        Select::make('unidad_medida_id')
                            ->label('Unidad de medida')
                            ->relationship(
                                name: 'unidadMedida',
                                titleAttribute: 'nombre',
                                modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                                    'catalogoTipo',
                                    fn (Builder $q) => $q->where('codigo', CatalogoTipo::UNIDAD_MEDIDA->value)
                                )
                            )
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->prefixIcon(Heroicon::Scale)
                            ->helperText('Unidad específica para esta variante (opcional).'),

                        TextInput::make('peso')
                            ->label('Peso')
                            ->numeric()
                            ->suffix('kg')
                            ->step(0.01)
                            ->prefixIcon(Heroicon::Scale),

                        TextInput::make('volumen')
                            ->label('Volumen')
                            ->numeric()
                            ->suffix('lt')
                            ->step(0.01)
                            ->prefixIcon(Heroicon::Beaker),

                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoGeneral::options())
                            ->default(EstadoGeneral::Activo->value)
                            ->prefixIcon(Heroicon::CheckCircle)
                            ->required(),
                    ]),
                Section::make('Información General')
                    ->description('Detalles del producto')
                    ->columns(1)
                    ->columnSpanFull()
                    ->schema([
                        KeyValue::make('atributos')
                            ->label('Atributos adicionales')
                            ->keyLabel('Atributo')
                            ->valueLabel('Valor')
                            ->nullable()
                            ->helperText('Ej. color, sabor, material, etc.'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')
                    ->label('SKU')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('nombre_variante')
                    ->label('Nombre')
                    ->searchable(),

                TextColumn::make('unidadMedida.nombre')
                    ->label('Unidad')
                    ->placeholder('â€”'),

                TextColumn::make('peso')
                    ->label('Peso (kg)')
                    ->numeric(decimalPlaces: 2)
                    ->placeholder('â€”'),

                TextColumn::make('volumen')
                    ->label('Volumen (lt)')
                    ->numeric(decimalPlaces: 2)
                    ->placeholder('â€”'),

                EstadoBadgeColumn::make(EstadoGeneral::class),

                FechaStandardColumn::make('created_at', 'Creado')
                    ->toggleable(isToggledHiddenByDefault: true),

                FechaStandardColumn::make('updated_at', 'Actualizado')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('unidad_medida_id')
                    ->label('Unidad de medida')
                    ->relationship('unidadMedida', 'nombre', fn (Builder $query) => $query->whereHas(
                        'catalogoTipo',
                        fn (Builder $q) => $q->where('codigo', CatalogoTipo::UNIDAD_MEDIDA->value)
                    ))
                    ->searchable()
                    ->preload(),

                FiltroEstado::make(EstadoGeneral::class)->default(1),
            ])
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->schema([
                        Section::make('Detalles de la variante')
                            ->columns()
                            ->schema([
                                TextEntry::make('codigo')
                                    ->label('SKU')
                                    ->badge()
                                    ->color('primary')
                                    ->copyable()
                                    ->icon(Heroicon::QrCode),

                                TextEntry::make('estado')
                                    ->label('Estado')
                                    ->badge()
                                    ->color(fn ($state): ?string => is_string($color = EstadoGeneral::colorFor($state)) ? $color : null)
                                    ->formatStateUsing(fn ($state): string => EstadoGeneral::labelFor($state)),

                                TextEntry::make('nombre_variante')
                                    ->label('Nombre')
                                    ->icon(Heroicon::Identification)
                                    ->columnSpan(2),

                                TextEntry::make('producto.nombre')
                                    ->label('Producto padre')
                                    ->icon(Heroicon::ShoppingBag)
                                    ->columnSpan(2),

                                TextEntry::make('unidadMedida.nombre')
                                    ->label('Unidad de medida')
                                    ->placeholder('â€”')
                                    ->icon(Heroicon::Scale),

                                TextEntry::make('peso')
                                    ->label('Peso')
                                    ->suffix(' kg')
                                    ->placeholder('â€”')
                                    ->icon(Heroicon::Scale),

                                TextEntry::make('volumen')
                                    ->label('Volumen')
                                    ->suffix(' lt')
                                    ->placeholder('â€”')
                                    ->icon(Heroicon::Beaker),

                                TextEntry::make('atributos')
                                    ->label('Atributos')
                                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state) : $state)
                                    ->placeholder('Sin atributos')
                                    ->columnSpanFull(),

                                ...TimestampsInfolistEntry::make(format: 'd/m/Y H:i', withIcons: true),
                            ]),
                    ]),
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ])
            ->defaultSort('codigo');
    }
}
