<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas\Cobros;

use App\BusinessLogic\Cuentas\CalcularVuelto;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Interactors\Restaurante\Mesas\CambiarEstadoMesa;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Queries\Monedas\ObtenerMonedaPorIdQuery;
use App\Repository\Queries\Monedas\ObtenerTasaCambioQuery;
use App\Support\MonedaHelper;

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
        private readonly ProcesarCobroCuenta $procesarCobroCuenta,
        private readonly CambiarEstadoMesa $cambiarEstadoMesa,
        private readonly CuentaRepositorioInterface $cuentas,
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
        $monedaPagoId = isset($data['moneda_pago_id']) && is_numeric($data['moneda_pago_id'])
            ? (int) $data['moneda_pago_id']
            : null;

        $monedaVueltoId = isset($data['moneda_vuelto_id']) && is_numeric($data['moneda_vuelto_id'])
            ? (int) $data['moneda_vuelto_id']
            : 0;

        $monedaPago = $monedaPagoId !== null ? $this->monedaPorId->ejecutar($monedaPagoId) : null;
        $monedaVuelto = $monedaVueltoId > 0 ? $this->monedaPorId->ejecutar($monedaVueltoId) : null;

        $codigoVuelto = MonedaHelper::codigo($monedaVuelto);
        $simboloVuelto = MonedaHelper::simbolo($monedaVuelto);
        $codigoPago = MonedaHelper::codigo($monedaPago);

        $tasa = $this->tasaCambio->ejecutar(now(), $codigoPago, $codigoVuelto);

        $vuelto = $this->calcularVuelto->ejecutar(
            saldoCuenta: (float) $cuenta->saldo,
            codigoMonedaPago: $codigoPago,
            codigoMonedaVuelto: $codigoVuelto,
            simboloMonedaVuelto: $simboloVuelto,
            tasaConversion: $tasa,
            data: $data,
        );

        $vueltoTexto = $vuelto['texto'] ?? '';
        if ($vuelto !== null) {
            $observacionesActuales = trim(
                is_string($data['observaciones'] ?? null) ? $data['observaciones'] : ''
            );
            $data['observaciones'] = $observacionesActuales !== ''
                ? "{$observacionesActuales} | {$vueltoTexto}"
                : $vueltoTexto;
        }

        $resultado = $this->procesarCobroCuenta->ejecutar($cuenta, $usuarioId, $data);

        $mesaNombre = null;
        if ($resultado['cerrada']) {
            $mesa = $this->resolverMesaDeCuenta($resultado['cuenta']);

            if ($mesa instanceof Espacio && $mesa->estado === EstadoEspacio::Ocupado) {
                $this->cambiarEstadoMesa->ejecutar($mesa->id, EstadoEspacio::Sucio);
                $mesaNombre = $mesa->nombre;
            }
        }

        return [
            'cuenta' => $resultado['cuenta'],
            'venta' => $resultado['venta'],
            'saldo' => $resultado['saldo'],
            'cerrada' => $resultado['cerrada'],
            'vueltoTexto' => $vueltoTexto,
            'mesaNombre' => $mesaNombre,
        ];
    }

    private function resolverMesaDeCuenta(Cuenta $cuenta): ?Espacio
    {
        $pedido = $this->cuentas->pedidoClienteDeCuenta($cuenta->id);

        if ($pedido !== null) {
            $pedido->loadMissing('mesa');

            if ($pedido->mesa instanceof Espacio) {
                return $pedido->mesa;
            }
        }

        return null;
    }
}
