<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas\Cobros;

use App\BusinessLogic\Cuentas\CalcularVuelto;
use App\BusinessLogic\Monedas\ConvertirMoneda;
use App\Enums\Cuentas\MetodoPago;
use App\Interactors\Restaurante\Pedidos\FinalizarPedidosServidosCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Queries\Monedas\ObtenerMonedaPorIdQuery;
use App\Repository\Queries\Monedas\ObtenerTasaCambioQuery;
use App\Support\Barcode\Support\MonedaHelper;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Orquesta el cobro completo de una cuenta: calcula el vuelto, registra el
 * pago/venta vía ProcesarCobroCuenta y, si la cuenta se cierra con comanda
 * de mesa, la pasa a estado Sucio con su solicitud de limpieza.
 */
final readonly class CobrarCuenta
{
    public function __construct(
        private readonly ObtenerMonedaPorIdQuery $monedaPorId,
        private readonly ObtenerTasaCambioQuery $tasaCambio,
        private readonly CalcularVuelto $calcularVuelto,
        private ConvertirMoneda $convertirMoneda,
        private readonly ProcesarCobroCuenta $procesarCobroCuenta,
        private FinalizarPedidosServidosCuenta $finalizarPedidos,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *   cuenta: Cuenta,
     *   venta: Venta|null,
     *   saldo: float,
     *   cerrada: bool,
     *   vueltoTexto: string,
     *   mesaNombre: string|null,
     * }
     */
    public function ejecutar(Cuenta $cuenta, ?int $usuarioId, array $data): array
    {
        return DB::transaction(function () use ($cuenta, $usuarioId, $data): array {
            $monedaPagoId = isset($data['moneda_pago_id']) && is_numeric($data['moneda_pago_id'])
                ? (int) $data['moneda_pago_id']
                : $cuenta->moneda_id;

            $monedaVueltoId = isset($data['moneda_vuelto_id']) && is_numeric($data['moneda_vuelto_id'])
                ? (int) $data['moneda_vuelto_id']
                : 0;

            $monedaPago = $monedaPagoId !== null ? $this->monedaPorId->ejecutar($monedaPagoId) : null;
            $monedaVuelto = $monedaVueltoId > 0 ? $this->monedaPorId->ejecutar($monedaVueltoId) : null;

            $codigoVuelto = MonedaHelper::codigo($monedaVuelto);
            $simboloVuelto = MonedaHelper::simbolo($monedaVuelto);
            $codigoPago = MonedaHelper::codigo($monedaPago);

            $tasa = $this->tasaCambio->ejecutar(now(), $codigoPago, $codigoVuelto);

            $esEfectivo = in_array($data['forma_pago'] ?? null, [MetodoPago::EFECTIVO->value, (string) MetodoPago::EFECTIVO->value], true);
            $saldoEnMonedaPago = $this->convertirMoneda->entre((float) $cuenta->saldo, $cuenta->moneda_id, $monedaPagoId);
            $recibido = is_numeric($data['monto_recibido'] ?? null) ? round((float) $data['monto_recibido'], 2) : 0.0;
            $monto = is_numeric($data['monto'] ?? null) ? round((float) $data['monto'], 2) : 0.0;
            if ($esEfectivo && $recibido > 0 && ($recibido < $monto || ($recibido > $monto && $monto < $saldoEnMonedaPago))) {
                throw new DomainException('Registre el importe exacto en efectivo para un abono parcial. Para liquidar, el recibido debe cubrir el saldo.');
            }
            if ($esEfectivo && config('tesoreria.habilitado') && $recibido > $monto && $monedaVueltoId > 0 && $monedaVueltoId !== $cuenta->moneda_id) {
                throw new DomainException('El vuelto de caja debe entregarse en la moneda del folio.');
            }

            $vuelto = $esEfectivo ? $this->calcularVuelto->ejecutar(
                saldoCuenta: $saldoEnMonedaPago,
                codigoMonedaPago: $codigoPago,
                codigoMonedaVuelto: $codigoVuelto,
                simboloMonedaVuelto: $simboloVuelto,
                tasaConversion: $tasa,
                data: $data,
            ) : null;

            $vueltoTexto = $vuelto['texto'] ?? '';
            if ($vuelto !== null) {
                $observacionesActuales = trim(
                    is_string($data['observaciones'] ?? null) ? $data['observaciones'] : ''
                );
                $data['observaciones'] = $observacionesActuales !== ''
                    ? "{$observacionesActuales} | {$vueltoTexto}"
                    : $vueltoTexto;
            }

            if ($esEfectivo && $recibido > $monto) {
                $data['monto'] = $recibido;
            }

            $resultado = $this->procesarCobroCuenta->ejecutar($cuenta, $usuarioId, $data);

            $mesasFinalizadas = $resultado['cerrada'] ? $this->finalizarPedidos->ejecutar($resultado['cuenta'], $usuarioId) : [];
            $mesaNombre = $mesasFinalizadas[0] ?? null;

            return [
                'cuenta' => $resultado['cuenta'],
                'venta' => $resultado['venta'],
                'saldo' => $resultado['saldo'],
                'cerrada' => $resultado['cerrada'],
                'vueltoTexto' => $vueltoTexto,
                'mesaNombre' => $mesaNombre,
            ];
        });
    }
}
