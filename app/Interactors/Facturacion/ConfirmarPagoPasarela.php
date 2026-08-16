<?php

declare(strict_types=1);

namespace App\Interactors\Facturacion;

use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Facturacion\EstadoConciliacionPago;
use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Interactors\Cuentas\Cobros\RegistrarPagoCuenta;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Persistencia\Facturacion\PagoConciliacionPersistencia;
use App\Repository\Persistencia\Facturacion\PagoTransaccionPersistencia;
use App\Repository\Queries\Facturacion\PagoTransaccionQuery;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class ConfirmarPagoPasarela
{
    public function __construct(
        private RegistrarPagoCuenta $registrarPagoCuenta,
        private PagoTransaccionQuery $pagoTransaccionQuery,
        private PagoTransaccionPersistencia $pagoTransaccionPersistencia,
        private PagoConciliacionPersistencia $pagoConciliacionPersistencia,
    ) {}

    /**
     * @param  array<string, mixed>  $webhookPayload
     */
    public function ejecutar(
        PagoTransaccion $transaccion,
        ?string $referenciaPasarela = null,
        array $webhookPayload = [],
        MetodoPago $metodoPago = MetodoPago::TARJETA_CREDITO,
        ?int $usuarioId = null,
    ): PagoTransaccion {
        return DB::transaction(function () use ($transaccion, $referenciaPasarela, $webhookPayload, $metodoPago, $usuarioId): PagoTransaccion {
            $transaccion = $this->pagoTransaccionQuery->porIdConLock($transaccion->id, ['cuenta', 'moneda']);

            if ($transaccion->estado === EstadoTransaccionPago::Capturada) {
                return $transaccion;
            }

            if ($transaccion->cuenta === null) {
                throw new DomainException('La transaccion no esta vinculada a una cuenta.');
            }

            $pago = $this->registrarPagoCuenta->ejecutar(
                cuenta: $transaccion->cuenta,
                metodoPago: $metodoPago,
                monto: (float) $transaccion->monto,
                estado: EstadoPago::APLICADO,
                referenciaTransaccion: $referenciaPasarela ?? $transaccion->referencia_pasarela ?? $transaccion->referencia_interna,
                observaciones: 'Pago confirmado por pasarela.',
                monedaId: $transaccion->moneda_id,
                usuarioId: $usuarioId,
            );

            $transaccion = $this->pagoTransaccionPersistencia->actualizar($transaccion, [
                'estado' => EstadoTransaccionPago::Capturada,
                'pago_cuenta_id' => $pago->id,
                'referencia_pasarela' => $referenciaPasarela ?? $transaccion->referencia_pasarela,
                'capturada_at' => now(),
                'webhook_payload' => $webhookPayload !== [] ? $webhookPayload : $transaccion->webhook_payload,
            ]);

            $this->pagoConciliacionPersistencia->actualizarOCrear(
                ['pago_transaccion_id' => $transaccion->id],
                [
                    'estado' => EstadoConciliacionPago::Conciliada,
                    'monto_esperado' => $transaccion->monto,
                    'monto_recibido' => $pago->monto,
                    'diferencia' => round((float) $pago->monto - (float) $transaccion->monto, 2),
                    'conciliada_at' => now(),
                    'conciliada_por' => $usuarioId,
                ],
            );

            return $transaccion->load('pagoCuenta', 'conciliacion');
        });
    }
}
