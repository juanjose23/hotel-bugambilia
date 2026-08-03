<?php

declare(strict_types=1);

namespace App\Notifications\Restaurante;

use App\Filament\Pages\Restaurante\CocinaPedidos;
use App\Filament\Pages\Restaurante\GestionMesas;
use App\Filament\Resources\Restaurante\PedidoResource\PedidoResource;
use App\Filament\Resources\Restaurante\PlatoResource\PlatoResource;
use App\Notifications\Restaurante\Contracts\UrlNotificadorInterface;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\Plato;

final class UrlNotificador implements UrlNotificadorInterface
{
    public function pedido(Pedido $pedido): string
    {
        return PedidoResource::getUrl('edit', ['record' => $pedido->id]);
    }

    public function plato(Plato $plato): string
    {
        return PlatoResource::getUrl('view', ['record' => $plato->id]);
    }

    public function cocinaPedidos(): string
    {
        return CocinaPedidos::getUrl();
    }

    public function gestionMesas(): string
    {
        return GestionMesas::getUrl();
    }
}
