<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\ZonaDeliveryResource\Pages;

use App\Filament\Resources\Restaurante\ZonaDeliveryResource\ZonaDeliveryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListZonasDelivery extends ListRecords
{
    protected static string $resource = ZonaDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nueva Zona de Delivery')
                ->icon('heroicon-o-plus'),
        ];
    }
}
