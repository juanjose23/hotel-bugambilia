<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas\Cobros;

use App\Enums\Cuentas\EstadoPago;
use App\Interactors\Cuentas\Gestion\RecalcularCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Persistencia\Tesoreria\CajaRepositorio;

final class ReembolsarPagoCuenta
{
    public function __construct(
        private readonly RecalcularCuenta $recalcularCuenta,
        private readonly CuentaRepositorioInterface $cuentas,
    ) {}

    /**
     * @param  list<int>|null  $pagoCuentaIds
     */
    public function ejecutar(
        Cuenta $cuenta,
        float $montoReembolso,
        string $motivo,
        ?int $usuarioId = null,
        ?array $pagoCuentaIds = null,
    ): Cuenta {
        if ($montoReembolso <= 0.0) {
            return $cuenta;
        }

        if (config('tesoreria.habilitado') && app(CajaRepositorio::class)->cuentaTieneEfectivoRegistrado($cuenta->id, $pagoCuentaIds)) {
            throw new \DomainException('La devolución de efectivo necesita un movimiento de salida autorizado; el pago original no se modifica.');
        }
        $pagosAplicados = $this->cuentas->pagosAplicadosDeCuenta($cuenta, $pagoCuentaIds);

        $reembolsoRestante = $montoReembolso;

        /** @var PagoCuenta $pago */
        foreach ($pagosAplicados as $pago) {
            if ($reembolsoRestante <= 0.0) {
                break;
            }

            $montoPago = (float) $pago->monto;

            if ($montoPago <= $reembolsoRestante) {
                $this->cuentas->actualizarPago($pago, [
                    'estado' => EstadoPago::REEMBOLSADO,
                    'observaciones' => trim(($pago->observaciones ?? '')." | Reembolsado: {$motivo}"),
                ]);
                $reembolsoRestante -= $montoPago;
            } else {
                $diferenciaAplicada = round($montoPago - $reembolsoRestante, 2);
                $this->cuentas->actualizarPago($pago, [
                    'monto' => $diferenciaAplicada,
                    'observaciones' => trim(($pago->observaciones ?? '')." | Reembolso parcial de {$reembolsoRestante}: {$motivo}"),
                ]);

                $this->cuentas->crearPago($cuenta, [
                    'forma_pago' => $pago->forma_pago,
                    'moneda_id' => $pago->moneda_id,
                    'monto' => $reembolsoRestante,
                    'estado' => EstadoPago::REEMBOLSADO,
                    'referencia_transaccion' => "REEMB-{$pago->id}",
                    'observaciones' => $motivo,
                    'usuario_id' => $usuarioId,
                ]);

                $reembolsoRestante = 0.0;
            }
        }

        return $this->recalcularCuenta->ejecutar($cuenta, $usuarioId);
    }
}
