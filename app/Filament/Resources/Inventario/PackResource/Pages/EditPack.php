<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\PackResource\Pages;

use App\Filament\Resources\Inventario\PackResource\PackResource;
use Filament\Resources\Pages\EditRecord;

class EditPack extends EditRecord
{
    protected static string $resource = PackResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
