<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\ParStockResource\Pages;

use App\Filament\Resources\Inventario\ParStockResource\ParStockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListParStocks extends ListRecords
{
    protected static string $resource = ParStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
