<?php

declare(strict_types=1);

namespace App\Filament\Pages\Limpieza\Schemas;

use App\Filament\Shared\Forms\Limpieza\OrigenLavanderiaSelects;
use App\Repository\Queries\Limpieza\Lavanderia\ObtenerCategoriasBlancosLavanderia;
use App\Repository\Queries\Limpieza\Lavanderia\ObtenerOpcionesBlancosLavanderia;
use App\Repository\Queries\Limpieza\Lavanderia\ObtenerOpcionesLotesLavanderia;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

final class EntradaLavanderiaForm
{
    /**
     * @return list<Component>
     */
    public static function schema(
        ObtenerCategoriasBlancosLavanderia $categoriasBlancos,
        ObtenerOpcionesBlancosLavanderia $opcionesBlancos,
        ObtenerOpcionesLotesLavanderia $opcionesLotes,
    ): array {
        return [
            Section::make('Entrada a lavandería con trazabilidad')
                ->description('Registre blancos o piezas que entran a lavandería indicando obligatoriamente su origen (habitación, espacio, bodega o carrito) y lote de inventario.')
                ->columns(2)
                ->schema([
                    OrigenLavanderiaSelects::tipo(),

                    OrigenLavanderiaSelects::origen(),

                    Select::make('categoria_id')
                        ->label('Filtrar por Categoría')
                        ->placeholder('Todas las categorías (Lencería, Blancos, etc.)')
                        ->options(fn (Get $get): array => $categoriasBlancos->execute(
                            tipoOrigen: is_string($get('tipo_origen')) ? $get('tipo_origen') : null,
                            origenId: is_numeric($get('origen_id')) ? (int) $get('origen_id') : null,
                        ))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->native(false)
                        ->afterStateUpdated(function (mixed $state, mixed $set, mixed $get) use ($opcionesBlancos): void {
                            if (is_callable($set) && is_callable($get) && $get('precargar_todos')) {
                                $catId = is_numeric($state) ? (int) $state : null;
                                $tipoOrigen = is_string($get('tipo_origen')) ? $get('tipo_origen') : null;
                                $origenId = is_numeric($get('origen_id')) ? (int) $get('origen_id') : null;
                                $items = $opcionesBlancos->obtenerVariantesParaPrecarga(
                                    categoriaId: $catId,
                                    tipoOrigen: $tipoOrigen,
                                    origenId: $origenId,
                                );
                                $set('items', $items);
                            }
                        }),

                    Toggle::make('precargar_todos')
                        ->label('Precargar todos los productos')
                        ->helperText('Carga automáticamente todos los productos de la categoría seleccionada con sus lotes activos para ingresar cantidades.')
                        ->live()
                        ->afterStateUpdated(function (mixed $state, mixed $set, mixed $get) use ($opcionesBlancos): void {
                            if (is_callable($set) && is_callable($get)) {
                                if ($state) {
                                    $catId = $get('categoria_id') && is_numeric($get('categoria_id')) ? (int) $get('categoria_id') : null;
                                    $tipoOrigen = is_string($get('tipo_origen')) ? $get('tipo_origen') : null;
                                    $origenId = is_numeric($get('origen_id')) ? (int) $get('origen_id') : null;
                                    $items = $opcionesBlancos->obtenerVariantesParaPrecarga(
                                        categoriaId: $catId,
                                        tipoOrigen: $tipoOrigen,
                                        origenId: $origenId,
                                    );
                                    $set('items', $items);
                                }
                            }
                        }),

                    Repeater::make('items')
                        ->label('Blancos / Productos a Ingresar')
                        ->columnSpanFull()
                        ->columns([
                            'default' => 1,
                            'md' => 12,
                        ])
                        ->schema([
                            Select::make('producto_variante_id')
                                ->label('Blanco / Producto')
                                ->placeholder('Seleccione el blanco')
                                ->options(fn (Get $get): array => $opcionesBlancos->execute(
                                    categoriaId: $get('../../categoria_id') && is_numeric($get('../../categoria_id')) ? (int) $get('../../categoria_id') : null,
                                    tipoOrigen: is_string($get('../../tipo_origen')) ? $get('../../tipo_origen') : null,
                                    origenId: is_numeric($get('../../origen_id')) ? (int) $get('../../origen_id') : null,
                                ))
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->native(false)
                                ->afterStateUpdated(function (mixed $state, mixed $set, Get $get) use ($opcionesLotes): void {
                                    if (is_callable($set)) {
                                        $varId = is_numeric($state) ? (int) $state : null;
                                        $tipoOrigen = is_string($get('../../tipo_origen')) ? $get('../../tipo_origen') : null;
                                        $origenId = is_numeric($get('../../origen_id')) ? (int) $get('../../origen_id') : null;
                                        $lotes = $varId ? $opcionesLotes->execute($varId, $tipoOrigen, $origenId) : [];
                                        $primerLoteId = array_key_first($lotes);
                                        $set('lote_id', $primerLoteId);
                                    }
                                })
                                ->columnSpan([
                                    'default' => 12,
                                    'md' => 5,
                                ]),

                            Select::make('lote_id')
                                ->label('Lote de Inventario')
                                ->placeholder('Seleccione lote')
                                ->options(fn (Get $get): array => $opcionesLotes->execute(
                                    productoVarianteId: $get('producto_variante_id') && is_numeric($get('producto_variante_id')) ? (int) $get('producto_variante_id') : null,
                                    tipoOrigen: is_string($get('../../tipo_origen')) ? $get('../../tipo_origen') : null,
                                    origenId: is_numeric($get('../../origen_id')) ? (int) $get('../../origen_id') : null,
                                ))
                                ->searchable()
                                ->preload()
                                ->required()
                                ->native(false)
                                ->columnSpan([
                                    'default' => 12,
                                    'md' => 3,
                                ]),

                            TextInput::make('cantidad')
                                ->label('Cantidad')
                                ->numeric()
                                ->minValue(0.01)
                                ->maxValue(fn (Get $get): float => is_numeric($get('max_qty')) && (float) $get('max_qty') > 0 ? (float) $get('max_qty') : 999999.0)
                                ->placeholder('Ej. 10')
                                ->columnSpan([
                                    'default' => 12,
                                    'md' => 2,
                                ]),

                            Hidden::make('max_qty')
                                ->default(0),

                            TextInput::make('notas')
                                ->label('Notas (Opcional)')
                                ->placeholder('Ej. Manchado...')
                                ->columnSpan([
                                    'default' => 12,
                                    'md' => 2,
                                ]),
                        ])
                        ->addActionLabel('Agregar otro producto')
                        ->defaultItems(1),

                    TextInput::make('notas')
                        ->label('Notas Generales de la Entrada')
                        ->placeholder('Ej. Retiro de cortinas para lavado, lencería de evento, cambio general...')
                        ->columnSpanFull(),
                ]),
        ];
    }
}
