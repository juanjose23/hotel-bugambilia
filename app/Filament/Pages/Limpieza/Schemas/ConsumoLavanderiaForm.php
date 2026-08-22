<?php

declare(strict_types=1);

namespace App\Filament\Pages\Limpieza\Schemas;

use App\Filament\Shared\Forms\Limpieza\StockUbicacionSelect;
use App\Repository\Queries\Limpieza\Lavanderia\ObtenerUbicacionesInventarioLavanderia;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

final class ConsumoLavanderiaForm
{
    /**
     * @return list<Component>
     */
    public static function schema(ObtenerUbicacionesInventarioLavanderia $ubicacionesInventarioLavanderia): array
    {
        return [
            Section::make('Registrar Merma / Baja de Blancos')
                ->description('Registre prendas o blancos dañados, manchados permanentemente o rotos para darlos de baja definitiva del inventario.')
                ->columns(2)
                ->schema([
                    StockUbicacionSelect::make(
                        column: 'stock_id',
                        label: 'Prenda / Blanco a Dar de Baja',
                        ubicacionId: fn (Get $get): array => $ubicacionesInventarioLavanderia->execute(),
                    ),

                    TextInput::make('cantidad')
                        ->label('Cantidad')
                        ->numeric()
                        ->required()
                        ->minValue(0.01)
                        ->maxValue(fn (Get $get): float => is_numeric($get('max_qty')) ? (float) $get('max_qty') : 999999.0),

                    Hidden::make('max_qty')
                        ->default(0),

                    TextInput::make('notas')
                        ->label('Notas / Razón de Merma')
                        ->columnSpanFull()
                        ->placeholder('Ej. Sábana rota, mancha irreparable, etc.')
                        ->required(),
                ]),
        ];
    }
}
