<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas;

use App\BusinessLogic\Cuentas\ValidarCuenta;
use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Events\Cuentas\CuentaAbierta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Reservas\Reserva;
use Illuminate\Support\Facades\DB;

/**
 * Activa una Cuenta existente (SOLICITADA → ABIERTA) o crea una nueva directamente ABIERTA.
 * Aplica a: Check-In de huésped, apertura directa de cuenta de restaurante/servicio.
 * Unifica: AbrirCuentaEstancia + AbrirCuentaParaTitular.
 */
final class AbrirCuenta
{
    public function __construct(
        private readonly ValidarCuenta $validarCuenta,
    ) {}

    public function ejecutar(
        TipoCuenta $tipo,
        ?Cuenta $cuentaExistente = null,
        ?Reserva $reserva = null,
        ?Estancia $estancia = null,
        ?Persona $cliente = null,
        ?float $limite = null,
        ?int $monedaId = null,
        ?int $usuarioId = null,
    ): Cuenta {
        return DB::transaction(function () use ($tipo, $cuentaExistente, $reserva, $estancia, $cliente, $limite, $monedaId, $usuarioId): Cuenta {
            // Si ya existe un folio SOLICITADA, simplemente lo activa
            if ($cuentaExistente !== null) {
                $this->validarCuenta->puedeAbrirse($cuentaExistente);

                $cuentaExistente->update([
                    'estado' => EstadoCuenta::ABIERTA,
                    'limite_autorizado' => $limite ?? $cuentaExistente->limite_autorizado,
                    'estancia_id' => $estancia->id ?? $cuentaExistente->estancia_id,
                    'abierta_at' => now(),
                    'abierta_por' => $usuarioId,
                ]);

                $cuenta = $cuentaExistente->refresh();
            } else {
                // Crea directamente en estado ABIERTA (venta directa, restaurante POS)
                $referencia = $reserva->id ?? $estancia->id ?? now()->timestamp;
                $numeroCuenta = sprintf('CTA-%s-%06d', now()->format('Y'), $referencia);

                $cuenta = Cuenta::query()->create([
                    'numero_cuenta' => $numeroCuenta,
                    'tipo_cuenta' => $tipo,
                    'estado' => EstadoCuenta::ABIERTA,
                    'cliente_id' => $cliente?->id,
                    'estancia_id' => $estancia?->id,
                    'reserva_id' => $reserva?->id,
                    'moneda_id' => $monedaId,
                    'limite_autorizado' => $limite,
                    'abierta_at' => now(),
                    'abierta_por' => $usuarioId,
                ]);
            }

            CuentaAbierta::dispatch($cuenta);

            return $cuenta;
        });
    }
}
