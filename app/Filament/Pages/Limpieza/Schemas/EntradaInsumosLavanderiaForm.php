<?php

declare(strict_types=1);

namespace App\Filament\Pages\Limpieza\Schemas;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Queries\Limpieza\Lavanderia\ObtenerOpcionesInsumosQuimicos;
use App\Repository\Queries\Limpieza\Lavanderia\ObtenerOpcionesLotesLavanderia;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

final class EntradaInsumosLavanderiaForm
{
    /**
     * @return list<Component>
     */
    public static function schema(
        ObtenerOpcionesInsumosQuimicos $opcionesInsumos,
        ObtenerOpcionesLotesLavanderia $opcionesLotes,
    ): array {
        return [
            Section::make('Recepción e Ingreso de Insumos Químicos a Lavandería')
                ->description('Registre la llegada de insumos (detergentes, suavizantes, cloro, desmanchador) desde el almacén central o por compra directa.')
                ->columns(2)
                ->schema([
                    Select::make('tipo_origen')
                        ->label('Tipo de Entrada / Origen')
                        ->options([
                            'bodega' => 'Traslado desde Bodega / Almacén Central',
                            'compra' => 'Ingreso Directo / Compra de Proveedor / Stock Inicial',
                        ])
                        ->default('bodega')
                        ->required()
                        ->live()
                        ->native(false)
                        ->prefixIcon(Heroicon::ArrowPath),

                    Select::make('bodega_origen_id')
                        ->label('Almacén / Bodega de Origen')
                        ->options(fn (): array => Ubicacion::query()
                            ->where('tipo', '!=', 'lavanderia')
                            ->where('estado', EstadoGeneral::Activo)
                            ->pluck('nombre', 'id')
                            ->toArray())
                        ->visible(fn (Get $get): bool => $get('tipo_origen') === 'bodega')
                        ->required(fn (Get $get): bool => $get('tipo_origen') === 'bodega')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->native(false)
                        ->placeholder('Seleccione almacén de origen')
                        ->prefixIcon(Heroicon::BuildingOffice),

                    TextInput::make('documento_referencia')
                        ->label('Factura / Remisión / No. Traslado')
                        ->placeholder('Ej. FACT-9821 / TRAS-005')
                        ->maxLength(100)
                        ->columnSpan(fn (Get $get): int => $get('tipo_origen') === 'bodega' ? 2 : 1),

                    Repeater::make('items')
                        ->label('Insumos Químicos y Consumibles a Recibir')
                        ->columnSpanFull()
                        ->columns([
                            'default' => 1,
                            'md' => 12,
                        ])
                        ->schema([
                            Select::make('producto_variante_id')
                                ->label('Insumo Químico / Consumible')
                                ->options(fn (): array => $opcionesInsumos->execute())
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->native(false)
                                ->placeholder('Seleccione insumo...')
                                ->columnSpan([
                                    'default' => 12,
                                    'md' => 5,
                                ]),

                            Select::make('lote_id')
                                ->label('Lote Disponible en Origen')
                                ->options(function (Get $get): array {
                                    $varianteId = $get('producto_variante_id');
                                    $bodegaId = $get('../../bodega_origen_id');

                                    if (! is_numeric($varianteId) || (int) $varianteId <= 0) {
                                        return [];
                                    }

                                    return app(ObtenerOpcionesLotesLavanderia::class)->execute(
                                        (int) $varianteId,
                                        'ubicacion',
                                        is_numeric($bodegaId) ? (int) $bodegaId : null
                                    );
                                })
                                ->visible(fn (Get $get): bool => $get('../../tipo_origen') === 'bodega')
                                ->searchable()
                                ->native(false)
                                ->placeholder('Seleccione lote...')
                                ->columnSpan([
                                    'default' => 12,
                                    'md' => 4,
                                ]),

                            TextInput::make('codigo_lote')
                                ->label('Código de Lote')
                                ->placeholder('Ej. LOTE-2026-DET')
                                ->visible(fn (Get $get): bool => $get('../../tipo_origen') === 'compra')
                                ->columnSpan([
                                    'default' => 12,
                                    'md' => 3,
                                ]),

                            TextInput::make('costo_unitario')
                                ->label('Costo Unitario ($)')
                                ->numeric()
                                ->placeholder('Ej. 45.00')
                                ->visible(fn (Get $get): bool => $get('../../tipo_origen') === 'compra')
                                ->columnSpan([
                                    'default' => 12,
                                    'md' => 2,
                                ]),

                            DatePicker::make('fecha_vencimiento')
                                ->label('Vencimiento')
                                ->native(false)
                                ->visible(fn (Get $get): bool => $get('../../tipo_origen') === 'compra')
                                ->columnSpan([
                                    'default' => 12,
                                    'md' => 2,
                                ]),

                            TextInput::make('cantidad')
                                ->label('Cantidad Recibida')
                                ->numeric()
                                ->required()
                                ->minValue(0.01)
                                ->placeholder('Ej. 5.0')
                                ->columnSpan([
                                    'default' => 12,
                                    'md' => 3,
                                ]),

                            TextInput::make('notas')
                                ->label('Notas / Detalle')
                                ->placeholder('Ej. Garrafa sellada...')
                                ->columnSpan(12),
                        ])
                        ->addActionLabel('Agregar otro insumo químico')
                        ->defaultItems(1),

                    TextInput::make('notas_generales')
                        ->label('Observaciones Generales de la Recepción')
                        ->placeholder('Ej. Entrega programada del proveedor / Pedido semanal...')
                        ->columnSpanFull(),
                ]),
        ];
    }
}
