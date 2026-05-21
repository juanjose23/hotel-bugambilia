<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\InventarioFisico\Pages;

use App\Filament\Resources\Inventario\InventarioFisico\InventarioFisicoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInventarioFisico extends CreateRecord
{
    protected static string $resource = InventarioFisicoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
