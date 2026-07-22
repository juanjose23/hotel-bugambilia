<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PedidoResource\Pages;

use App\Filament\Resources\Restaurante\PedidoResource\PedidoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPedidos extends ListRecords
{
    protected static string $resource = PedidoResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
