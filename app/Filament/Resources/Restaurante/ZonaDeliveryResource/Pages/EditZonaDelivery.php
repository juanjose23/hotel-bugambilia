<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\ZonaDeliveryResource\Pages;

use App\Filament\Resources\Restaurante\ZonaDeliveryResource\ZonaDeliveryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditZonaDelivery extends EditRecord
{
    protected static string $resource = ZonaDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
