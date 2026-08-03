<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas;

use App\BusinessLogic\Cuentas\ValidarPagoCobroCuenta;
use App\BusinessLogic\Monedas\ConvertirMoneda;
use App\Enums\Cuentas\MetodoPago;
use App\Interactors\Restaurante\Pedidos\RegistrarClienteRapido;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Procesa el cobro completo de una cuenta: asigna/registra cliente,
 * convierte moneda a base, valida el pago mínimo, registra el pago y
 * genera la venta si el saldo queda liquidado.
 */
final readonly class ProcesarCobroCuenta
{
    public function __construct(
        private RegistrarClienteRapido $registrarCliente,
        private RegistrarPagoCuenta $registrarPago,
        private CerrarCuentaYGenerarVenta $cerrarCuenta,
        private ValidarPagoCobroCuenta $validarPago,
        private ConvertirMoneda $convertirMoneda,
        private CuentaRepositorioInterface $cuentas,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{cuenta: Cuenta, venta: Venta|null, saldo: float, cerrada: bool}
     *
     * @throws Throwable
     */
    public function ejecutar(Cuenta $cuenta, ?int $usuarioId, array $data): array
    {
        return DB::transaction(function () use ($cuenta, $usuarioId, $data): array {
            $cuenta = $this->asignarCliente($cuenta, $data);

            $formaPago = $data['forma_pago'] ?? null;
            $formaPagoVal = is_numeric($formaPago) ? (int) $formaPago : (is_string($formaPago) ? $formaPago : 1);
            $metodoPago = MetodoPago::tryFrom($formaPagoVal) ?? MetodoPago::EFECTIVO;

            $monedaId = isset($data['moneda_pago_id']) && is_numeric($data['moneda_pago_id'])
                ? (int) $data['moneda_pago_id']
                : null;

            $montoRaw = $data['monto'] ?? 0;
            $montoNIO = $this->convertirMoneda->aBase(is_numeric($montoRaw) ? (float) $montoRaw : 0.0, $monedaId);

            $propinaRaw = isset($data['propina']) && is_numeric($data['propina'])
                ? (float) $data['propina']
                : 0.0;
            $propinaNIO = $propinaRaw > 0 ? $this->convertirMoneda->aBase($propinaRaw, $monedaId) : 0.0;

            $saldo = $cuenta->saldo;
            $cargosObligatoriosTotal = $this->cuentas->totalCargosObligatorios($cuenta);

            $this->validarPago->validar($montoNIO, $saldo, $cargosObligatoriosTotal);

            $this->registrarPago->ejecutar(
                cuenta: $cuenta,
                metodoPago: $metodoPago,
                monto: $montoNIO,
                propina: $propinaNIO,
                referenciaTransaccion: is_string($data['referencia_transaccion'] ?? null) ? $data['referencia_transaccion'] : null,
                observaciones: is_string($data['observaciones'] ?? null) ? $data['observaciones'] : null,
                monedaId: $monedaId,
                usuarioId: $usuarioId,
            );

            $cuenta = $this->cuentas->refrescar($cuenta);
            $saldoRestante = $cuenta->saldo;

            $venta = null;
            if ($saldoRestante <= 0) {
                $tipoComprobante = $data['tipo_comprobante'] ?? 'voucher';
                $ruc = $data['ruc_factura'] ?? null;
                $razonSocial = $data['razon_social_factura'] ?? null;

                $datosFiscales = [
                    'tipo_comprobante' => is_string($tipoComprobante) ? $tipoComprobante : 'voucher',
                    'ruc' => is_string($ruc) ? $ruc : null,
                    'razon_social' => is_string($razonSocial) ? $razonSocial : null,
                    'fecha_emision' => now()->toIso8601String(),
                ];

                $venta = $this->cerrarCuenta->ejecutar($cuenta, $usuarioId, $datosFiscales);
            }

            return [
                'cuenta' => $cuenta,
                'venta' => $venta,
                'saldo' => $saldoRestante,
                'cerrada' => $saldoRestante <= 0,
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function asignarCliente(Cuenta $cuenta, array $data): Cuenta
    {
        if (! empty($data['registrar_nuevo_cliente'])) {
            $datosCliente = [
                'primer_nombre' => is_string($data['nuevo_cliente_nombre'] ?? null) ? $data['nuevo_cliente_nombre'] : '',
            ];

            if (! empty($data['nuevo_cliente_apellido']) && is_string($data['nuevo_cliente_apellido'])) {
                $datosCliente['primer_apellido'] = $data['nuevo_cliente_apellido'];
            }
            if (! empty($data['nuevo_cliente_identificacion']) && is_string($data['nuevo_cliente_identificacion'])) {
                $datosCliente['identificacion'] = $data['nuevo_cliente_identificacion'];
            }
            if (! empty($data['nuevo_cliente_telefono']) && is_string($data['nuevo_cliente_telefono'])) {
                $datosCliente['telefono'] = $data['nuevo_cliente_telefono'];
            }

            $nuevaPersona = $this->registrarCliente->ejecutar($datosCliente);

            return $this->cuentas->actualizar($cuenta, ['cliente_id' => $nuevaPersona->id]);
        }

        if (isset($data['cliente_id']) && is_numeric($data['cliente_id'])) {
            return $this->cuentas->actualizar($cuenta, ['cliente_id' => (int) $data['cliente_id']]);
        }

        return $cuenta;
    }
}
