<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\MovimientoStock\Pages;

use App\Filament\Resources\Inventario\MovimientoStock\MovimientoStockResource;
use App\Filament\Resources\Inventario\MovimientoStock\Widgets\MermasPorCategoriaChart;
use App\Filament\Resources\Inventario\MovimientoStock\Widgets\RotacionInventarioChart;
use Filament\Resources\Pages\ListRecords;

class ListMovimientoStock extends ListRecords
{
    protected static string $resource = MovimientoStockResource::class;

    public function getHeaderWidgets(): array
    {
        return [
            RotacionInventarioChart::class,
            MermasPorCategoriaChart::class,
        ];
    }
}
