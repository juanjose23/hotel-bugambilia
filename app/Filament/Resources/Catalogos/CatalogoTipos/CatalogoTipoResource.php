<?php

namespace App\Filament\Resources\Catalogos\CatalogoTipos;

use App\Enums\EstadoCatalogo;
use App\Filament\Resources\Catalogos\CatalogoTipos\Pages\ManageCatalogoTipos;
use App\Models\Catalogos\CatalogoTipo;
use App\UseCases\CatalogoTipo\Commands\ActualizarCatalogoTipo;
use App\UseCases\CatalogoTipo\Commands\EliminarCatalogoTipo;
use App\UseCases\CatalogoTipo\Queries\ListarCatalogoTipoes;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\TextSize;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;
class CatalogoTipoResource extends Resource
{
    protected static ?string $model = CatalogoTipo::class;

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Catálogos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Bookmark;
    protected static ?string $modelLabel = 'Tipos de catálogos';
    protected static ?string $pluralModelLabel = 'Tipos de catálogo';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Formulario Tipo de cátalogos')
                    ->description('Detalles sobre los tipos cátalogos.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->placeholder('Ej. Categoría de habitación')
                            ->minLength(3)
                            ->maxLength(150)
                            ->required()
                            ->prefixIcon(Heroicon::Newspaper)
                            ->helperText('Nombre legible que se mostrará en listas y selects.'),

                        TextInput::make('codigo')
                            ->label('Código')
                            ->placeholder('Ej. CATEGORIA_HAB')
                            ->minLength(3)
                            ->maxLength(150)
                            ->required()
                            ->unique()
                            ->prefixIcon(Heroicon::CodeBracketSquare)
                            ->helperText('Identificador único por tipo; usado en integraciones y seeds.'),

                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoCatalogo::options())
                            ->default(EstadoCatalogo::Activo->value)
                            ->prefixIcon(Heroicon::CheckCircle)
                            ->required()
                            ->helperText('Estado lógico del tipo de catálogo (Activo/Inactivo)'),


                    ]),

            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Información General')
                    ->description('Datos principales del tipo de catálogo.')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([

                        TextEntry::make('nombre')
                            ->label('Nombre')
                            ->icon(Heroicon::Tag)
                            ->weight('bold')
                            ->size(TextSize::Large),

                        TextEntry::make('codigo')
                            ->label('Código')
                            ->icon(Heroicon::Hashtag)
                            ->badge()
                            ->color('info'),

                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->icon(fn ($state) => $state
                                ? Heroicon::CheckCircle
                                : Heroicon::CheckBadge
                            )
                            ->color(fn ($state): string =>
                            EstadoCatalogo::colorFor($state)
                            )
                            ->formatStateUsing(fn ($state): string =>
                            EstadoCatalogo::labelFor($state)
                            ),
                    ]),

                Section::make('Información del sistema')
                    ->description('Datos de auditoría del registro.')
                    ->icon(Heroicon::Clock)
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([

                        TextEntry::make('created_at')
                            ->label('Fecha de creación')
                            ->icon(Heroicon::Calendar)
                            ->dateTime('d/m/Y H:i'),

                        TextEntry::make('updated_at')
                            ->label('Última actualización')
                            ->icon(Heroicon::ArrowDownRight)
                            ->dateTime('d/m/Y H:i'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('nombre')->label('Nombre'),
                TextColumn::make('codigo')->label('Código'),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->searchable()
                    ->badge()
                    ->color(fn($state): string => EstadoCatalogo::colorFor($state))
                    ->formatStateUsing(fn($state): string => EstadoCatalogo::labelFor($state))

            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->using(fn(CatalogoTipo $record, array $data) => app(ActualizarCatalogoTipo::class)->execute($record, $data)),
                    DeleteAction::make()
                        ->using(fn(CatalogoTipo $record) => app(EliminarCatalogoTipo::class)->execute($record)),
                ])->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCatalogoTipos::route('/'),
        ];
    }

    /**
     * @return Builder
     */
    public static function getEloquentQuery(): Builder
    {
        return app(ListarCatalogoTipoes::class)->execute([]);
    }
}
