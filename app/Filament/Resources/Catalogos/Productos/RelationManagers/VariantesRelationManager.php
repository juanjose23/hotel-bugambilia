<?php

namespace App\Filament\Resources\Catalogos\Productos\RelationManagers;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Catalogos\EstadoCatalogo;
use App\Filament\Resources\Shared\Filters\FiltroEstado;
use App\Filament\Resources\Shared\InfolistTimestamps;
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
                            ->options(EstadoCatalogo::options())
                            ->default(EstadoCatalogo::Activo->value)
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
                    ->placeholder('—'),

                TextColumn::make('peso')
                    ->label('Peso (kg)')
                    ->numeric(decimalPlaces: 2)
                    ->placeholder('—'),

                TextColumn::make('volumen')
                    ->label('Volumen (lt)')
                    ->numeric(decimalPlaces: 2)
                    ->placeholder('—'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->formatStateUsing(fn (int $state): string => $state == 1 ? 'Activo' : 'Inactivo')
                    ->color(fn (int $state) => $state == 1 ? 'success' : 'danger'),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y')
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

                FiltroEstado::make(EstadoCatalogo::class)->default(1),
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
                                    ->color(fn ($state): ?string => is_string($color = EstadoCatalogo::colorFor($state)) ? $color : null)
                                    ->formatStateUsing(fn ($state): string => EstadoCatalogo::labelFor($state)),

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
                                    ->placeholder('—')
                                    ->icon(Heroicon::Scale),

                                TextEntry::make('peso')
                                    ->label('Peso')
                                    ->suffix(' kg')
                                    ->placeholder('—')
                                    ->icon(Heroicon::Scale),

                                TextEntry::make('volumen')
                                    ->label('Volumen')
                                    ->suffix(' lt')
                                    ->placeholder('—')
                                    ->icon(Heroicon::Beaker),

                                TextEntry::make('atributos')
                                    ->label('Atributos')
                                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state) : $state)
                                    ->placeholder('Sin atributos')
                                    ->columnSpanFull(),

                                ...InfolistTimestamps::make(format: 'd/m/Y H:i', withIcons: true),
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
