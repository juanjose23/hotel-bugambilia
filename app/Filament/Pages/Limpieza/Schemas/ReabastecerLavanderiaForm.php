<?php

declare(strict_types=1);

namespace App\Filament\Pages\Limpieza\Schemas;

use App\Filament\Shared\Forms\Limpieza\DestinoLavanderiaSelects;
use App\Filament\Shared\Forms\Limpieza\StockUbicacionSelect;
use App\Repository\Queries\Limpieza\Lavanderia\ObtenerUbicacionesInventarioLavanderia;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

final class ReabastecerLavanderiaForm
{
    /**
     * @return list<Component>
     */
    public static function schema(ObtenerUbicacionesInventarioLavanderia $ubicacionesInventarioLavanderia): array
    {
        return [
            Section::make('Reponer Blancos / Insumos a Ubicación')
                ->description('Traslade blancos limpios desde lavandería hacia habitaciones o bodegas de piso.')
                ->columns(2)
                ->schema([
                    DestinoLavanderiaSelects::tipo(),

                    DestinoLavanderiaSelects::destino(),

                    Repeater::make('items')
                        ->label('Blancos / Insumos a Trasladar')
                        ->columnSpanFull()
                        ->columns(2)
                        ->schema([
                            StockUbicacionSelect::make(
                                column: 'stock_id',
                                label: 'Insumo (Lote & Disponible)',
                                ubicacionId: fn (Get $get): array => $ubicacionesInventarioLavanderia->execute(incluirSucios: false),
                            ),

                            TextInput::make('cantidad')
                                ->label('Cantidad')
                                ->numeric()
                                ->required()
                                ->minValue(0.01)
                                ->maxValue(fn (Get $get): float => is_numeric($get('max_qty')) ? (float) $get('max_qty') : 999999.0),

                            Hidden::make('max_qty')
                                ->default(0),

                            Hidden::make('producto_variante_id'),
                        ])
                        ->addActionLabel('Agregar otro insumo'),
                ]),
        ];
    }
}
