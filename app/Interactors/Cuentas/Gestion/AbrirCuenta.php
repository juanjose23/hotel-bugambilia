<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas\Gestion;

use App\BusinessLogic\Cuentas\ValidarCuenta;
use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Events\Cuentas\CuentaAbierta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\User;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
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
        private readonly CuentaRepositorioInterface $cuentas,
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

                $cuenta = $this->cuentas->abrir($cuentaExistente, [
                    'estado' => EstadoCuenta::ABIERTA,
                    'cliente_id' => $this->resolverClienteId($cuentaExistente, $cliente, $reserva, $estancia),
                    'limite_autorizado' => $limite ?? $cuentaExistente->limite_autorizado,
                    'estancia_id' => $estancia->id ?? $cuentaExistente->estancia_id,
                    'abierta_at' => now(),
                    'abierta_por' => $usuarioId,
                ]);
            } else {
                // Crea directamente en estado ABIERTA (venta directa, restaurante POS)
                $referencia = $reserva->id ?? $estancia->id ?? now()->timestamp;
                $baseNumero = sprintf('CTA-%s-%06d', now()->format('Y'), $referencia);
                $numeroCuenta = $baseNumero;
                $contador = 1;
                while (Cuenta::withTrashed()->where('numero_cuenta', $numeroCuenta)->exists()) {
                    $contador++;
                    $numeroCuenta = sprintf('%s-%d', $baseNumero, $contador);
                }

                $monedaIdResuelto = $monedaId ?? Moneda::query()->where('es_predeterminada', true)->value('id') ?? Moneda::query()->value('id');
                $usuarioIdResuelto = ($usuarioId !== null && User::query()->where('id', $usuarioId)->exists()) ? $usuarioId : null;

                $cuenta = $this->cuentas->crear([
                    'numero_cuenta' => $numeroCuenta,
                    'tipo_cuenta' => $tipo,
                    'estado' => EstadoCuenta::ABIERTA,
                    'cliente_id' => $this->resolverClienteId($cuentaExistente, $cliente, $reserva, $estancia),
                    'estancia_id' => $estancia?->id,
                    'reserva_id' => $reserva?->id,
                    'moneda_id' => $monedaIdResuelto,
                    'limite_autorizado' => $limite,
                    'abierta_at' => now(),
                    'abierta_por' => $usuarioIdResuelto,
                ]);
            }

            $reservaModel = $reserva ?? $estancia?->reserva;
            if ($reservaModel !== null && ! $reservaModel->solicita_cuenta) {
                $reservaModel->update(['solicita_cuenta' => true]);
            }

            CuentaAbierta::dispatch($cuenta);

            return $cuenta;
        });
    }

    private function resolverClienteId(
        ?Cuenta $cuentaExistente,
        ?Persona $cliente,
        ?Reserva $reserva,
        ?Estancia $estancia,
    ): ?int {
        if ($cuentaExistente !== null && $cuentaExistente->cliente_id !== null) {
            return $cuentaExistente->cliente_id;
        }

        if ($cliente !== null) {
            return $cliente->id;
        }

        if ($reserva !== null) {
            return $reserva->cliente_id;
        }

        $reservaDeEstancia = $estancia?->reserva;

        if ($reservaDeEstancia !== null) {
            return $reservaDeEstancia->cliente_id;
        }

        return null;
    }
}
