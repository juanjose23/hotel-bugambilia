<?php

declare(strict_types=1);

namespace App\Repository\Observers\Restaurante;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Shared\RegistrarSolicitudLimpieza;
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

        // Generar solicitud de limpieza inicial/preparación si es necesario
        app(RegistrarSolicitudLimpieza::class)->execute(
            $mesa,
            $mesa->id,
            'normal',
            "Limpieza de alistamiento de mesa para pedido: {$pedido->codigo}"
        );
    }

    /**
     * Se ejecuta cuando un pedido es actualizado (por ejemplo, al pagarse/terminar).
     */
    public function updated(Pedido $pedido): void
    {
        if ($pedido->isDirty('estado') && in_array($pedido->estado, [EstadoPedido::Pagado->value, EstadoPedido::Cancelado->value], true)) {
            $mesa = $pedido->mesa;
            if (! $mesa) {
                return;
            }

            // Marcar mesa como en limpieza
            $mesa->update(['estado' => EstadoEspacio::Limpieza]);

            // Generar solicitud de limpieza post-servicio
            app(RegistrarSolicitudLimpieza::class)->execute(
                $mesa,
                $mesa->id,
                'urgente',
                "Mesa liberada. Limpieza y desinfección post-pedido: {$pedido->codigo}"
            );
        }
    }
}
