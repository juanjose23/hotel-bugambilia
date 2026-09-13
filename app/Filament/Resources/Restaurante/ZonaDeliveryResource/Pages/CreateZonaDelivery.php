<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\ZonaDeliveryResource\Pages;

use App\Filament\Resources\Restaurante\ZonaDeliveryResource\ZonaDeliveryResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateZonaDelivery extends CreateRecord
{
    protected static string $resource = ZonaDeliveryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
