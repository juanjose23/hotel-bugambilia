<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\ProcesoCocinaResource\Schemas;

use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Queries\Monedas\ObtenerMonedaPredeterminadaQuery;
use App\Repository\Queries\Restaurante\Cocina\ObtenerDatosProcesoCocinaQuery;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

final class ProcesoCocinaForm
{
    public static function configure(Schema $schema): Schema
    {
        $cocinaQuery = app(ObtenerDatosProcesoCocinaQuery::class);
        $monedaPredeterminada = app(ObtenerMonedaPredeterminadaQuery::class)->ejecutar();
        $simboloMoneda = $monedaPredeterminada !== null ? ($monedaPredeterminada->simbolo ?? 'C$') : 'C$';

        return $schema
            ->components([
                Section::make('Resumen de Rendimiento & Costos de Cocina')
                    ->description('Cálculo consolidado de costo de preparación e insumos por plato.')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('costo_total')
                                ->label('Costo Total de Insumos')
                                ->prefix($simboloMoneda)
                                ->numeric()
                                ->formatStateUsing(fn ($record, $state) => number_format((float) ($record?->items?->sum('costo_asignado') ?? $state ?? 0.00), 2))
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('costo_por_plato')
                                ->label('Costo Unitario por Plato')
                                ->prefix($simboloMoneda)
                                ->numeric()
                                ->formatStateUsing(function ($record): string {
                                    if ($record === null) {
                                        return number_format(0.00, 2);
                                    }
                                    $cant = (int) $record->cantidad_platos;
                                    $costoTot = (float) $record->costo_total;
                                    $costoUnit = $cant > 0 ? $costoTot / $cant : 0.00;

                                    return number_format($costoUnit, 2);
                                })
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('codigo')
                                ->label('Código de Proceso')
                                ->disabled()
                                ->dehydrated(false)
                                ->placeholder('Autogenerado al guardar'),
                        ]),
                    ]),

                Section::make('Datos del Plato a Preparar')
                    ->columns(2)
                    ->schema([
                        Select::make('plato_id')
                            ->label('Platillo / Receta Base')
                            ->options(fn () => $cocinaQuery->platosConReceta())
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (mixed $state, Set $set, Get $get) use ($cocinaQuery): void {
                                $plato = is_numeric($state)
                                    ? Plato::query()->find((int) $state)
                                    : null;

                                $set('producto_origen_id', $plato?->producto_receta_id);
                                $set('cantidad_procesada', 1);

                                if ($plato instanceof Plato) {
                                    $cantidadPlatos = is_numeric($get('cantidad_platos')) ? (int) $get('cantidad_platos') : 1;
                                    $items = $cocinaQuery->ingredientesParaPlato((int) $state, $cantidadPlatos);
                                    $set('items', $items);
                                    $set('costo_total', round(array_sum(array_column($items, 'costo_asignado')), 2));
                                }
                            })
                            ->columnSpan(1),

                        TextInput::make('cantidad_platos')
                            ->label('Cantidad de Platos a Preparar')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (mixed $state, Set $set, Get $get) use ($cocinaQuery): void {
                                $platoId = $get('plato_id');

                                if (! is_numeric($platoId)) {
                                    return;
                                }

                                $cantidadPlatos = is_numeric($state) ? (int) $state : 1;
                                $items = $cocinaQuery->ingredientesParaPlato((int) $platoId, $cantidadPlatos);
                                $set('items', $items);
                                $set('costo_total', round(array_sum(array_column($items, 'costo_asignado')), 2));
                            })
                            ->columnSpan(1),

                        Hidden::make('producto_origen_id')
                            ->required(),

                        Hidden::make('variante_origen_id'),

                        Hidden::make('cantidad_procesada')
                            ->default(1),

                        Textarea::make('observaciones')
                            ->label('Observaciones Técnicas de Cocina')
                            ->columnSpanFull()
                            ->rows(2)
                            ->placeholder('Ej. Preparación previa de salsas / Término de cocción...'),
                    ]),

                Section::make('Insumos & Ingredientes Utilizados')
                    ->description('Registre los insumos o materia prima usados para trazabilidad y costo de preparación.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Select::make('producto_destino_id')
                                    ->label('Ingrediente / Materia prima usada')
                                    ->options(fn () => $cocinaQuery->productosDisponibles())
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (mixed $state, Set $set, Get $get) use ($cocinaQuery): void {
                                        $set('variante_destino_id', null);

                                        if (is_numeric($state)) {
                                            $precio = $cocinaQuery->costoRealUnitario((int) $state) ?? 0.0;
                                            $rawCant = $get('cantidad');
                                            $cant = is_numeric($rawCant) ? (float) $rawCant : 1.0;
                                            $set('costo_asignado', round($cant * $precio, 2));
                                        }
                                    })
                                    ->columnSpan(4),

                                Select::make('variante_destino_id')
                                    ->label('Variante')
                                    ->options(fn (Get $get): array => self::opcionesVariantes($get('producto_destino_id')))
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(3),

                                TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(0.001)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (mixed $state, Set $set, Get $get) use ($cocinaQuery): void {
                                        $prodId = $get('producto_destino_id');
                                        if (is_numeric($prodId) && is_numeric($state)) {
                                            $precio = $cocinaQuery->costoRealUnitario((int) $prodId) ?? 0.0;
                                            $set('costo_asignado', round(((float) $state) * $precio, 2));
                                        }
                                    })
                                    ->columnSpan(2),

                                TextInput::make('peso_unitario')
                                    ->label('Peso Unit. (kg/g)')
                                    ->numeric()
                                    ->columnSpan(2),

                                TextInput::make('peso_total')
                                    ->label('Peso Total (kg/g)')
                                    ->numeric()
                                    ->columnSpan(2),

                                Toggle::make('es_merma')
                                    ->label('Es Merma')
                                    ->live()
                                    ->columnSpan(1),

                                TextInput::make('costo_asignado')
                                    ->label('Costo (C$)')
                                    ->numeric()
                                    ->prefix($simboloMoneda)
                                    ->required()
                                    ->columnSpan(2),
                            ])
                            ->columns(13)
                            ->defaultItems(0)
                            ->addActionLabel('Agregar ingrediente / materia prima usada'),
                    ]),
            ]);
    }

    /** @return array<int, string> */
    private static function opcionesVariantes(mixed $productoId): array
    {
        if (! is_numeric($productoId)) {
            return [];
        }

        return ProductoVariante::query()
            ->where('producto_id', (int) $productoId)
            ->where('estado', 1)
            ->orderBy('codigo')
            ->get()
            ->mapWithKeys(function (ProductoVariante $variante): array {
                $nombre = $variante->nombre_variante ?: $variante->codigo;

                return [(int) $variante->id => "{$variante->codigo} - {$nombre}"];
            })
            ->all();
    }
}
