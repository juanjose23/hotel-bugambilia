<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\HabitacionStockResource\Pages;

use App\Filament\Resources\Habitaciones\HabitacionStockResource\HabitacionStockResource;
use Filament\Resources\Pages\ListRecords;

class ListHabitacionStocks extends ListRecords
{
    protected static string $resource = HabitacionStockResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
