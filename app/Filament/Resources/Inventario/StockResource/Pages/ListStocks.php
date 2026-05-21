<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\StockResource\Pages;

use App\Filament\Resources\Inventario\StockResource\StockResource;
use Filament\Resources\Pages\ListRecords;

class ListStocks extends ListRecords
{
    protected static string $resource = StockResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
