<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\PackResource\Pages;

use App\Filament\Resources\Inventario\PackResource\PackResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePack extends CreateRecord
{
    protected static string $resource = PackResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
