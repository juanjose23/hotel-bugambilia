<?php

declare(strict_types=1);

namespace App\Interactors\CuentasEstancia;

use App\Enums\Estancias\EstadoCuentaEstancia;
use App\Enums\Estancias\TipoTitular;
use App\Repository\Models\Estancias\CuentaEstancia;
use App\Repository\Models\Estancias\Estancia;
use DomainException;
use Illuminate\Support\Facades\DB;

final class TransferirCuentaAHabitacion
{
    public function ejecutar(
        CuentaEstancia $cuenta,
        Estancia $estancia,
        ?int $usuarioId = null,
    ): CuentaEstancia {
        if ($cuenta->tipo_titular === TipoTitular::HABITACION) {
            throw new DomainException('La cuenta ya está vinculada a una habitación.');
        }

        if ($cuenta->estado !== EstadoCuentaEstancia::ABIERTA) {
            throw new DomainException('Solo se pueden transferir cuentas en estado Abierto.');
        }

        return DB::transaction(function () use ($cuenta, $estancia): CuentaEstancia {
            $cuenta->update([
                'estancia_id' => $estancia->id,
                'reserva_id' => $estancia->reserva_id,
                'tipo_titular' => TipoTitular::HABITACION,
            ]);

            return $cuenta->refresh();
        });
    }
}
