<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas\Gestion;

use App\BusinessLogic\Cuentas\ValidarCuenta;
use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Events\Cuentas\CuentaAbierta;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use Illuminate\Support\Facades\DB;

/**
 * Activa una Cuenta existente (SOLICITADA → ABIERTA) o crea una nueva directamente ABIERTA.
 * Aplica a: Check-In de huésped, apertura directa de cuenta de restaurante/servicio.
 * Unifica: AbrirCuentaEstancia + AbrirCuentaParaTitular.
 */
final readonly class AbrirCuenta
{
    public function __construct(
        private ValidarCuenta $validarCuenta,
        private CuentaRepositorioInterface $cuentas,
    ) {}

    public function ejecutar(
        TipoCuenta $tipo,
        ?Cuenta $cuentaExistente = null,
        ?Reserva $reserva = null,
        ?Estancia $estancia = null,
        ?Cliente $cliente = null,
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
                    'limite_autorizado' => $limite ?? $cuentaExistente->limite_autorizado,
                    'estancia_id' => $estancia->id ?? $cuentaExistente->estancia_id,
                    'abierta_at' => now(),
                    'abierta_por' => $usuarioId,
                ]);
            } else {
                // Crea directamente en estado ABIERTA (venta directa, restaurante POS)
                $referencia = (string) ($reserva->id ?? $estancia->id ?? now()->timestamp);
                $numeroCuenta = $this->cuentas->generarNumeroCuenta($referencia);

                $monedaPredeterminada = $this->cuentas->monedaPredeterminada();
                $monedaIdResuelto = $monedaId ?? ($monedaPredeterminada !== null ? $monedaPredeterminada->id : 1);
                $usuarioIdResuelto = ($usuarioId !== null && $this->cuentas->usuarioExiste($usuarioId)) ? $usuarioId : null;

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
                $this->cuentas->marcarSolicitaCuenta($reservaModel->id);
            }

            CuentaAbierta::dispatch($cuenta);

            return $cuenta;
        });
    }

    private function resolverClienteId(
        ?Cuenta $cuentaExistente,
        ?Cliente $cliente,
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

        return $estancia?->reserva?->cliente_id;
    }
}
