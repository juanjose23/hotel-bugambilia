<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PlatoResource\Schemas;

use App\Actions\Restaurante\Platos\GenerarCodigoPlato;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Restaurante\Platos\SincronizarGaleriaPlatoImagenes;
use App\Repository\Queries\Restaurante\Platos\ObtenerCatalogoPlatoQuery;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class PlatoForm
{
    public static function configure(Schema $schema): Schema
    {
        $catalogoQuery = app(ObtenerCatalogoPlatoQuery::class);

        return $schema
            ->components([
                Section::make('Datos del Plato')
                    ->columns(3)
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Codigo')
                            ->required()
                            ->maxLength(20)
                            ->default(fn () => app(GenerarCodigoPlato::class)->ejecutar())
                            ->unique(ignoreRecord: true),

                        TextInput::make('nombre')
                            ->label('Nombre del Plato')
                            ->required()
                            ->maxLength(100)
                            ->columnSpan(2),

                        Select::make('categoria_id')
                            ->label('Categoria')
                            ->options(fn () => $catalogoQuery->categoriasDisponibles())
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('producto_receta_id')
                            ->label('Receta (Producto)')
                            ->options(fn () => $catalogoQuery->productosDisponibles())
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoGeneral::class)
                            ->default(EstadoGeneral::Activo)
                            ->required(),

                        Toggle::make('web')
                            ->label('Visible en Web')
                            ->default(false),

                        Textarea::make('descripcion')
                            ->label('Descripcion')
                            ->columnSpanFull()
                            ->rows(3),

                        TextInput::make('tiempo_preparacion')
                            ->label('Tiempo de Preparacion')
                            ->placeholder('Ej: 15 - 25 min')
                            ->maxLength(50),
                    ]),

                Section::make('Imagenes del Plato')
                    ->schema([
                        FileUpload::make('imagenes')
                            ->label('Fotos')
                            ->directory('restaurante/platos')
                            ->multiple()
                            ->reorderable()
                            ->maxFiles(3)
                            ->maxSize(4096)
                            ->afterStateHydrated(function ($state, $set, $record) use ($catalogoQuery): void {
                                if (! $record) {
                                    return;
                                }

                                $set('imagenes', $catalogoQuery->imagenesDePlato($record)->all());
                            })
                            ->dehydrated(false)
                            ->saveRelationshipsUsing(function ($state, $record): void {
                                if (! $record) {
                                    return;
                                }

                                app(SincronizarGaleriaPlatoImagenes::class)
                                    ->ejecutar($record, $state ?? []);
                            }),
                    ]),
            ]);
    }
}
