<?php

declare(strict_types=1);

namespace App\Repository\Observers\Restaurante;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Restaurante\Pedido;

final class PedidoObserver
{
    /**
     * Se ejecuta cuando un pedido es creado (generado).
     */
    public function created(Pedido $pedido): void
    {
        $mesa = $pedido->mesa;
        if (! $mesa) {
            return;
        }

        // Marcar mesa como ocupada
        $mesa->update(['estado' => EstadoEspacio::Ocupado]);
    }

    /**
     * Se ejecuta cuando un pedido es actualizado (al pagarse, cargarse a habitación o cancelarse).
     */
    public function updated(Pedido $pedido): void
    {
        if ($pedido->isDirty('estado') && in_array($pedido->estado, [EstadoPedido::PAGADO, EstadoPedido::CARGADO_A_HABITACION, EstadoPedido::CANCELADO], true)) {
            $mesa = $pedido->mesa;
            if (! $mesa) {
                return;
            }

            // Marcar mesa como Sucia para activar el flujo de limpieza
            $mesa->update(['estado' => EstadoEspacio::Sucio]);
        }
    }
}
