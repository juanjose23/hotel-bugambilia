<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\ParStockResource\Pages;

use App\Filament\Resources\Inventario\ParStockResource\ParStockResource;
use Filament\Resources\Pages\CreateRecord;

class CreateParStock extends CreateRecord
{
    protected static string $resource = ParStockResource::class;
}
