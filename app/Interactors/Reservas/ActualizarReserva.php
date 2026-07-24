<?php

declare(strict_types=1);

namespace App\Interactors\Reservas;

use App\Repository\Models\Reservas\Reserva;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;

final class ActualizarReserva
{
    public function __construct(
        private readonly ReservaRepositorioInterface $reservas,
    ) {}

    /** @param array<string, mixed> $datos */
    public function ejecutar(Reserva $reserva, array $datos): Reserva
    {
        return $this->reservas->actualizarDatosGenerales($reserva, [
            'cliente_id' => $datos['cliente_id'] ?? null,
            'nombre_cliente' => $datos['nombre_cliente'],
            'telefono_cliente' => $datos['telefono_cliente'] ?? null,
            'email_cliente' => $datos['email_cliente'] ?? null,
            'solicita_cuenta' => (bool) ($datos['solicita_cuenta'] ?? false),
            'limite_cuenta_solicitado' => is_numeric($datos['limite_cuenta_solicitado'] ?? null)
                ? (float) $datos['limite_cuenta_solicitado']
                : null,
            'notas' => $datos['notas'] ?? null,
        ]);
    }
}
