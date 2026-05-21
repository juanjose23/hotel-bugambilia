<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\ParStockResource\Pages;

use App\Filament\Resources\Inventario\ParStockResource\ParStockResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditParStock extends EditRecord
{
    protected static string $resource = ParStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
