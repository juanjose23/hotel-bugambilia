<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Limpieza;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;

final class EjecucionLimpiezaRepository
{
    public function asignarCarrito(LimpiezaEjecucion $ejecucion, int $carritoId): void
    {
        $ejecucion->update(['carrito_id' => $carritoId]);
    }

    public function liberarCarrito(LimpiezaEjecucion $ejecucion): void
    {
        $atributos = [
            'carrito_id' => null,
        ];

        if ($ejecucion->estado === EstadoLimpieza::EnProgreso) {
            $atributos['estado'] = EstadoLimpieza::Pendiente;
            $atributos['colaborador_id'] = null;
            $atributos['hora_inicio'] = null;
        }

        $ejecucion->update($atributos);

        if ($ejecucion->solicitud !== null) {
            $ejecucion->solicitud->update([
                'estado' => EstadoLimpieza::Pendiente,
                'personal_id' => null,
            ]);
        }
    }
}
