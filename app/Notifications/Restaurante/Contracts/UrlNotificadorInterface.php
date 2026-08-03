<?php

declare(strict_types=1);

namespace App\Notifications\Restaurante\Contracts;

use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\Plato;

interface UrlNotificadorInterface
{
    public function pedido(Pedido $pedido): string;

    public function plato(Plato $plato): string;

    public function cocinaPedidos(): string;

    public function gestionMesas(): string;
}
