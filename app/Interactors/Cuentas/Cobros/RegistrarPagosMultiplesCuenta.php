<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas\Cobros;

use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Registra múltiples pagos divididos (Split Payments) a favor de una Cuenta
 * en una única transacción atómica.
 */
final readonly class RegistrarPagosMultiplesCuenta
{
    public function __construct(
        private RegistrarPagoCuenta $registrarPago,
        private CuentaRepositorioInterface $cuentas,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $pagos
     * @return array{
     *     cuenta: Cuenta,
     *     pagos: Collection<int, PagoCuenta>,
     *     totalPagado: float,
     *     saldoRestante: float
     * }
     */
    public function ejecutar(
        Cuenta $cuenta,
        array $pagos,
        ?int $usuarioId = null,
    ): array {
        if ($pagos === []) {
            throw new DomainException('Debe proporcionar al menos un método de pago.');
        }

        return DB::transaction(function () use ($cuenta, $pagos, $usuarioId): array {
            /** @var Collection<int, PagoCuenta> $pagosRegistrados */
            $pagosRegistrados = collect();
            $totalMonto = 0.0;

            foreach ($pagos as $item) {
                $montoRaw = $item['monto'] ?? null;
                $monto = is_numeric($montoRaw) ? (float) $montoRaw : 0.0;
                if ($monto <= 0.0) {
                    throw new DomainException('El monto de cada pago individual debe ser mayor a cero.');
                }

                $formaPago = $item['forma_pago'] ?? null;
                $metodo = $formaPago instanceof MetodoPago
                    ? $formaPago
                    : (is_numeric($formaPago) ? MetodoPago::tryFrom((int) $formaPago) : (is_string($formaPago) ? MetodoPago::tryFrom($formaPago) : null));

                if ($metodo === null) {
                    throw new DomainException('Se especificó un método de pago inválido.');
                }

                $propinaRaw = $item['propina'] ?? null;
                $propina = is_numeric($propinaRaw) ? (float) $propinaRaw : 0.0;

                $referenciaRaw = $item['referencia_transaccion'] ?? null;
                $referencia = is_string($referenciaRaw) && trim($referenciaRaw) !== '' ? trim($referenciaRaw) : null;

                $observacionesRaw = $item['observaciones'] ?? null;
                $observaciones = is_string($observacionesRaw) && trim($observacionesRaw) !== '' ? trim($observacionesRaw) : null;

                $monedaIdRaw = $item['moneda_id'] ?? null;
                $monedaId = is_numeric($monedaIdRaw) ? (int) $monedaIdRaw : null;

                $pago = $this->registrarPago->ejecutar(
                    cuenta: $cuenta,
                    metodoPago: $metodo,
                    monto: $monto,
                    propina: $propina,
                    estado: EstadoPago::APLICADO,
                    referenciaTransaccion: $referencia,
                    observaciones: $observaciones,
                    monedaId: $monedaId,
                    usuarioId: $usuarioId,
                );

                $pagosRegistrados->push($pago);
                $totalMonto += (float) $pago->monto;

                // Refrescar cuenta para la siguiente iteración
                $cuenta = $this->cuentas->refrescar($cuenta);
            }

            return [
                'cuenta' => $cuenta,
                'pagos' => $pagosRegistrados,
                'totalPagado' => round($totalMonto, 2),
                'saldoRestante' => (float) $cuenta->saldo,
            ];
        });
    }
}
