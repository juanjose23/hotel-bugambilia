<?php

declare(strict_types=1);

namespace App\Interactors\Facturacion;

use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Models\Facturacion\PasarelaPago;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Persistencia\Facturacion\PagoTransaccionPersistencia;
use App\Repository\Queries\Facturacion\PagoTransaccionQuery;
use App\Repository\Queries\Monedas\ObtenerMonedaPredeterminadaQuery;
use App\Repository\Queries\Monedas\ObtenerTasaCambioQuery;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class RegistrarTransaccionPasarela
{
    public function __construct(
        private ObtenerMonedaPredeterminadaQuery $obtenerMonedaPredeterminada,
        private ObtenerTasaCambioQuery $obtenerTasaCambio,
        private PagoTransaccionQuery $pagoTransaccionQuery,
        private PagoTransaccionPersistencia $pagoTransaccionPersistencia,
    ) {}

    /**
     * @param  array<string, mixed>  $requestPayload
     * @param  array<string, mixed>  $responsePayload
     */
    public function ejecutar(
        PasarelaPago $pasarela,
        float $monto,
        Moneda $moneda,
        string $idempotencyKey,
        ?Cuenta $cuenta = null,
        ?Reserva $reserva = null,
        ?Venta $venta = null,
        ?Factura $factura = null,
        ?PagoCuenta $pagoCuenta = null,
        EstadoTransaccionPago $estado = EstadoTransaccionPago::Pendiente,
        ?string $referenciaPasarela = null,
        array $requestPayload = [],
        array $responsePayload = [],
    ): PagoTransaccion {
        if ($monto <= 0) {
            throw new DomainException('El monto de la transaccion debe ser mayor a cero.');
        }

        return DB::transaction(function () use (
            $pasarela,
            $monto,
            $moneda,
            $idempotencyKey,
            $cuenta,
            $reserva,
            $venta,
            $factura,
            $pagoCuenta,
            $estado,
            $referenciaPasarela,
            $requestPayload,
            $responsePayload,
        ): PagoTransaccion {
            $existente = $this->pagoTransaccionQuery->porIdempotenciaKey($idempotencyKey);

            if ($existente instanceof PagoTransaccion) {
                return $existente;
            }

            $monedaBase = $this->obtenerMonedaPredeterminada->ejecutar() ?? $moneda;
            $tasa = $moneda->id === $monedaBase->id
                ? 1.0
                : $this->obtenerTasaCambio->ejecutar(
                    now()->toDateString(),
                    (string) $moneda->codigo,
                    (string) $monedaBase->codigo,
                );
            $ahora = now();

            return $this->pagoTransaccionPersistencia->crear([
                'pasarela_pago_id' => $pasarela->id,
                'reserva_id' => $reserva?->id,
                'cuenta_id' => $cuenta !== null
                    ? $cuenta->id
                    : ($venta !== null ? $venta->cuenta_id : ($factura !== null ? $factura->cuenta_id : null)),
                'venta_id' => $venta !== null ? $venta->id : ($factura !== null ? $factura->venta_id : null),
                'factura_id' => $factura?->id,
                'pago_cuenta_id' => $pagoCuenta?->id,
                'referencia_interna' => 'PAY-'.$ahora->format('YmdHis').'-'.str()->upper(str()->random(6)),
                'referencia_pasarela' => $referenciaPasarela,
                'idempotency_key' => $idempotencyKey,
                'estado' => $estado,
                'moneda_id' => $moneda->id,
                'moneda_base_id' => $monedaBase->id,
                'monto' => round($monto, 2),
                'monto_base' => round($monto * $tasa, 2),
                'tasa_cambio' => $tasa,
                'solicitada_at' => $ahora,
                'autorizada_at' => $estado === EstadoTransaccionPago::Autorizada ? $ahora : null,
                'capturada_at' => $estado === EstadoTransaccionPago::Capturada ? $ahora : null,
                'fallida_at' => $estado === EstadoTransaccionPago::Fallida ? $ahora : null,
                'request_payload' => $requestPayload !== [] ? $requestPayload : null,
                'response_payload' => $responsePayload !== [] ? $responsePayload : null,
            ]);
        });
    }
}
