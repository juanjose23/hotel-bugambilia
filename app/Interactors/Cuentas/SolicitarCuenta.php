<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Events\Cuentas\CuentaSolicitada;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Reservas\Reserva;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Crea un folio de cuenta en estado SOLICITADA.
 * Aplica a: reservas con crédito pre-autorizado, solicitudes desde recepción.
 * Unifica: SolicitarCuentaEstancia.
 */
final class SolicitarCuenta
{
    public function ejecutar(
        TipoCuenta $tipo,
        ?Reserva $reserva = null,
        ?Estancia $estancia = null,
        ?Persona $cliente = null,
        ?float $limiteAutorizado = null,
        ?int $usuarioId = null,
    ): Cuenta {
        return DB::transaction(function () use ($tipo, $reserva, $estancia, $cliente, $limiteAutorizado, $usuarioId): Cuenta {
            // Evitar solicitar más de un folio activo por reserva del mismo tipo
            if ($reserva !== null) {
                $existe = Cuenta::query()
                    ->where('reserva_id', $reserva->id)
                    ->where('tipo_cuenta', $tipo)
                    ->whereIn('estado', [EstadoCuenta::SOLICITADA, EstadoCuenta::ABIERTA])
                    ->exists();

                if ($existe) {
                    throw new DomainException('Ya existe un folio activo para esta reserva y tipo de cuenta.');
                }
            }

            $referencia = $reserva->id ?? $estancia->id ?? now()->timestamp;
            $numeroCuenta = sprintf('CTA-%s-%06d', now()->format('Y'), $referencia);

            $cuenta = Cuenta::query()->create([
                'numero_cuenta' => $numeroCuenta,
                'tipo_cuenta' => $tipo,
                'estado' => EstadoCuenta::SOLICITADA,
                'cliente_id' => $cliente?->id,
                'estancia_id' => $estancia?->id,
                'reserva_id' => $reserva?->id,
                'limite_autorizado' => $limiteAutorizado,
                'abierta_at' => now(),
                'abierta_por' => $usuarioId,
            ]);

            CuentaSolicitada::dispatch($cuenta);

            return $cuenta;
        });
    }
}
