<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Tesoreria;

use App\Enums\Facturacion\EstadoConciliacionPago;
use App\Enums\Facturacion\EstadoFactura;
use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Facturacion\PagoConciliacion;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Models\Tesoreria\AbastecimientoCaja;
use App\Repository\Models\Tesoreria\ComprobanteTransferencia;
use App\Repository\Models\Tesoreria\ConciliacionBancaria;
use App\Repository\Models\Tesoreria\DecisionComprobante;
use App\Repository\Models\Tesoreria\DevolucionCaja;
use App\Repository\Models\Tesoreria\EntregaCaja;
use App\Repository\Models\Tesoreria\EvidenciaTransferencia;
use App\Repository\Models\Tesoreria\ExtractoBancario;
use App\Repository\Models\Tesoreria\FondoTesoreria;
use App\Repository\Models\Tesoreria\MovimientoFondo;
use App\Repository\Models\Tesoreria\OperacionTesoreria;
use App\Repository\Models\Tesoreria\ValeCajaChica;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class TesoreriaRepositorio
{
    public function fondo(int $id): FondoTesoreria
    {
        return FondoTesoreria::query()->lockForUpdate()->findOrFail($id);
    }

    public function extracto(int $id): ExtractoBancario
    {
        return ExtractoBancario::query()->lockForUpdate()->findOrFail($id);
    }

    public function vale(int $id): ValeCajaChica
    {
        return ValeCajaChica::query()->lockForUpdate()->findOrFail($id);
    }

    public function comprobante(int $id): ComprobanteTransferencia
    {
        return ComprobanteTransferencia::query()->lockForUpdate()->findOrFail($id);
    }

    public function devolucion(int $id): DevolucionCaja
    {
        return DevolucionCaja::query()->lockForUpdate()->findOrFail($id);
    }

    public function entrega(int $id): EntregaCaja
    {
        return EntregaCaja::query()->lockForUpdate()->findOrFail($id);
    }

    public function pago(int $id): PagoCuenta
    {
        return PagoCuenta::query()->lockForUpdate()->findOrFail($id);
    }

    public function cuenta(int $id): Cuenta
    {
        return Cuenta::query()->lockForUpdate()->findOrFail($id);
    }

    public function transaccion(int $id): PagoTransaccion
    {
        return PagoTransaccion::query()->lockForUpdate()->findOrFail($id);
    }

    public function movimiento(int $id): MovimientoFondo
    {
        return MovimientoFondo::query()->lockForUpdate()->findOrFail($id);
    }

    /**
     * @param  array<string,mixed>  $data
     */
    public function crearFondo(array $data): FondoTesoreria
    {
        return FondoTesoreria::query()->create($data);
    }

    /**
     * @param  array<string,mixed>  $data
     */
    public function crearVale(array $data): ValeCajaChica
    {
        return ValeCajaChica::query()->create($data);
    }

    /**
     * @param  array<string,mixed>  $data
     */
    public function crearComprobante(array $data): ComprobanteTransferencia
    {
        return ComprobanteTransferencia::query()->create($data);
    }

    /**
     * @param  array<string,mixed>  $data
     */
    public function crearDevolucion(array $data): DevolucionCaja
    {
        return DevolucionCaja::query()->create($data);
    }

    /**
     * @param  array<string,mixed>  $data
     */
    public function crearEntrega(array $data): EntregaCaja
    {
        return EntregaCaja::query()->create($data);
    }

    /**
     * @param  array<string,mixed>  $data
     */
    public function crearExtracto(array $data): ExtractoBancario
    {
        return ExtractoBancario::query()->create($data);
    }

    /**
     * @param  array<string,mixed>  $data
     */
    public function crearConciliacion(array $data): ConciliacionBancaria
    {
        return ConciliacionBancaria::query()->create($data);
    }

    /**
     * @param  array<string,mixed>  $data
     */
    public function crearEvidencia(array $data): EvidenciaTransferencia
    {
        return EvidenciaTransferencia::query()->create($data);
    }

    /**
     * @param  array<string,mixed>  $data
     */
    public function crearMovimiento(array $data): MovimientoFondo
    {
        return MovimientoFondo::query()->create($data);
    }

    /** @param list<int> $ids
     * @return Collection<int,FondoTesoreria> */
    public function bloquearFondos(array $ids): Collection
    {
        return FondoTesoreria::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
    }

    public function saldoCentavos(int $id): int
    {
        $s = 0;
        foreach (MovimientoFondo::query()->where('fondo_id', $id)->get() as $m) {
            $s += (int) round((float) $m->importe * 100);
        }

        return $s;
    }

    public function operacion(string $clave): ?OperacionTesoreria
    {
        return OperacionTesoreria::query()->where('clave', $clave)->first();
    }

    /**
     * @param  array<string,mixed>  $data
     */
    public function crearOperacion(array $data): OperacionTesoreria
    {
        return OperacionTesoreria::query()->create($data);
    }

    /**
     * @param  array<string,mixed>  $resultado
     */
    public function finalizarOperacion(OperacionTesoreria $o, array $resultado): void
    {
        $o->update(['resultado' => $resultado]);
    }

    public function referenciaUsada(int $fondo, string $ref): bool
    {
        return ComprobanteTransferencia::query()->where('fondo_id', $fondo)->where('referencia', $ref)->exists();
    }

    public function extractoUsado(int $id): bool
    {
        return ConciliacionBancaria::query()->where('extracto_id', $id)->exists();
    }

    public function movimientoConciliado(int $id): bool
    {
        return ConciliacionBancaria::query()->where('movimiento_id', $id)->exists();
    }

    public function devolucionesReservadas(int $pago): int
    {
        $s = 0;
        foreach (DevolucionCaja::query()->where('pago_cuenta_id', $pago)->whereIn('estado', ['solicitada', 'aprobada'])->get() as $d) {
            $s += (int) round((float) $d->importe * 100);
        }

        return $s;
    }

    public function facturaEmitida(int $cuenta): bool
    {
        return Factura::query()->where('cuenta_id', $cuenta)->where('estado', EstadoFactura::Emitida)->exists();
    }

    public function cuentaPropia(int $id, User $user): Cuenta
    {
        $c = Cuenta::query()->with(['reserva', 'cliente.persona'])->findOrFail($id);
        $cliente = $user->persona?->cliente;
        $propia = $cliente !== null && ($c->cliente_id === $cliente->id || $c->reserva?->cliente_id === $cliente->id);
        $email = $user->email_verified_at !== null && $c->reserva !== null && strcasecmp((string) $user->email, (string) $c->reserva->email_cliente) === 0;
        abort_unless($propia || $email, 403);

        return $c;
    }

    public function user(int $id): User
    {
        return User::query()->findOrFail($id);
    }

    public function ultimaEvidencia(int $id): EvidenciaTransferencia
    {
        return EvidenciaTransferencia::query()->where('comprobante_id', $id)->latest('id')->firstOrFail();
    }

    public function actualizarConciliacionPasarela(int $id, float $esperado, float $recibido, int $actor): void
    {
        PagoConciliacion::query()->updateOrCreate(['pago_transaccion_id' => $id], ['estado' => EstadoConciliacionPago::Conciliada, 'monto_esperado' => $esperado, 'monto_recibido' => $recibido, 'diferencia' => round($recibido - $esperado, 2), 'conciliada_at' => now(), 'conciliada_por' => $actor]);
    }

    public function transaccionConciliada(int $id): bool
    {
        return ConciliacionBancaria::query()->where('pago_transaccion_id', $id)->exists();
    }

    public function reservaAdmitePago(Cuenta $cuenta): bool
    {
        $reserva = $cuenta->reserva;

        return $reserva === null || in_array($reserva->estado, [EstadoReserva::PENDIENTE, EstadoReserva::CONFIRMADA, EstadoReserva::CHECKED_IN], true) && $reserva->fecha_check_in !== null && $reserva->fecha_check_in->toDateString() >= today()->toDateString();
    }

    public function cuentaDeComprobante(int $id): int
    {
        return ComprobanteTransferencia::query()->findOrFail($id)->cuenta_id;
    }

    /**
     * @param  array<string,mixed>  $data
     */
    public function crearDecision(array $data): void
    {
        DecisionComprobante::query()->create($data);
    }

    public function extractoReferencia(int $fondo, string $referencia): bool
    {
        return ExtractoBancario::query()->where('fondo_id', $fondo)->where('referencia', $referencia)->exists();
    }

    public function codigoFondoExiste(string $codigo): bool
    {
        return FondoTesoreria::query()->where('codigo', $codigo)->exists();
    }

    /** @param array<string,mixed> $datos */
    public function actualizarVale(ValeCajaChica $v, array $datos): void
    {
        $v->update($datos);
    }

    /** @param array<string,mixed> $datos */
    public function actualizarDevolucion(DevolucionCaja $d, array $datos): void
    {
        $d->update($datos);
    }

    /** @param array<string,mixed> $datos */
    public function actualizarEntrega(EntregaCaja $e, array $datos): void
    {
        $e->update($datos);
    }

    /** @param array<string,mixed> $datos */
    public function actualizarComprobante(ComprobanteTransferencia $v, array $datos): void
    {
        $v->update($datos);
    }

    public function guardarComprobante(ComprobanteTransferencia $v): void
    {
        $v->save();
    }

    public function abastecimiento(int $id): AbastecimientoCaja
    {
        return AbastecimientoCaja::query()->lockForUpdate()->findOrFail($id);
    }

    /** @param array<string,mixed> $datos */
    public function crearAbastecimiento(array $datos): AbastecimientoCaja
    {
        return AbastecimientoCaja::query()->create($datos);
    }

    /** @param array<string,mixed> $datos */
    public function actualizarAbastecimiento(AbastecimientoCaja $v, array $datos): void
    {
        $v->update($datos);
    }
}
