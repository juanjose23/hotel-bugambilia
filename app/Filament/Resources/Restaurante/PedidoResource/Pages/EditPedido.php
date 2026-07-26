<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PedidoResource\Pages;

use App\Enums\Restaurante\EstadoPedido;
use App\Filament\Resources\Restaurante\PedidoResource\PedidoResource;
use App\Interactors\Restaurante\Pedidos\EnviarPedidoACocina;
use App\Repository\Models\Restaurante\Pedido;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

final class EditPedido extends EditRecord
{
    protected static string $resource = PedidoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        /** @var Pedido $record */
        $record = $this->getRecord();
        if ($record->estado === EstadoPedido::EN_PREPARACION) {
            app(EnviarPedidoACocina::class)->ejecutar($record);
        }
    }
}
