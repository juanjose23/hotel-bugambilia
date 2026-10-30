<?php

declare(strict_types=1);

namespace App\Interactors\Tesoreria;

use App\BusinessLogic\Tesoreria\ValidarOperacionTesoreria;
use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Repository\Models\Tesoreria\OperacionTesoreria;
use App\Repository\Persistencia\Tesoreria\CajaRepositorio;
use App\Repository\Persistencia\Tesoreria\TesoreriaRepositorio;
use DomainException;
use Illuminate\Support\Str;

final readonly class GestionarDevoluciones
{
    public function __construct(private TesoreriaRepositorio $repo, private CajaRepositorio $cajas, private AutorizarTesoreria $autorizar, private EjecutarOperacionTesoreria $operacion, private ValidarOperacionTesoreria $validar) {}

    public function solicitar(int $actor, int $pago, float $importe, string $motivo, string $clave): void
    {
        $this->autorizar->ejecutar($actor, 'Solicitar:Devolucion');
        $centavos = $this->validar->importe($importe);
        $motivo = $this->validar->texto($motivo);
        $this->operacion->ejecutar($actor, 'solicitar_devolucion', $clave, [$pago, $importe, $motivo], function (OperacionTesoreria $o) use ($actor, $pago, $importe, $motivo, $centavos): array {
            $p = $this->repo->pago($pago);
            $s = $this->cajas->activaUsuario($actor);
            if ($s === null || $s->estado !== 'abierta' || $p->estado !== EstadoPago::APLICADO || $p->forma_pago !== MetodoPago::EFECTIVO || ! $this->cajas->pagoTieneEfectivoRegistrado($p->id) || $p->moneda_id === null || $this->cajas->saldo($s, $p->moneda_id) === null) {
                throw new DomainException('La devolución requiere un pago de efectivo registrado y tu turno abierto en esa moneda.');
            }
            if ($this->repo->devolucionesReservadas($pago) + $centavos > (int) round((float) $p->monto * 100)) {
                throw new DomainException('El importe supera el efectivo pendiente de devolver del pago original.');
            }
            $d = $this->repo->crearDevolucion(['pago_cuenta_id' => $pago, 'sesion_id' => $s->id, 'solicitante_id' => $actor, 'importe' => $importe, 'motivo' => $motivo]);

            return ['devolucion_id' => $d->id];
        });
    }

    public function resolver(int $actor, int $id, bool $aprobar, string $motivo, string $clave): void
    {
        $this->autorizar->ejecutar($actor, 'Aprobar:Devolucion');
        $motivo = $this->validar->texto($motivo);
        $this->operacion->ejecutar($actor, 'resolver_devolucion', $clave, [$id, $aprobar, $motivo], function (OperacionTesoreria $o) use ($actor, $id, $aprobar, $motivo): array {
            $d = $this->repo->devolucion($id);
            if ($d->estado !== 'solicitada' || $d->solicitante_id === $actor) {
                throw new DomainException('Otro usuario debe revisar la solicitud pendiente.');
            }
            if ($aprobar) {
                $p = $this->repo->pago($d->pago_cuenta_id);
                if ($p->estado !== EstadoPago::APLICADO || $this->repo->facturaEmitida($p->cuenta_id)) {
                    throw new DomainException('Conserva la trazabilidad y registra antes la anulación o ajuste de la factura emitida.');
                }
                $s = $this->cajas->bloquearSesion($d->sesion_id);
                $saldo = $p->moneda_id !== null ? $this->cajas->saldo($s, $p->moneda_id) : null;
                if ($s->estado !== 'abierta' || $s->usuario_id !== $d->solicitante_id || $saldo === null || $this->cajas->esperado($s, $saldo) < (float) $d->importe) {
                    throw new DomainException('El turno solicitado ya no está abierto o no tiene efectivo suficiente. Rechaza y crea la solicitud en un turno disponible.');
                }
                $this->cajas->crearMovimiento(['clave_operacion' => Str::uuid()->toString(), 'sesion_id' => $s->id, 'moneda_id' => $p->moneda_id, 'usuario_id' => $actor, 'tipo' => 'egreso', 'importe' => $d->importe, 'concepto' => mb_substr('Devolución autorizada '.$d->id.' del pago '.$p->id.': '.$d->motivo, 0, 1000), 'referencia' => 'DEVOLUCION-'.$d->id]);
            }
            $this->repo->actualizarDevolucion($d, ['estado' => $aprobar ? 'aprobada' : 'rechazada', 'aprobado_por' => $actor, 'decision' => $motivo]);

            return ['devolucion_id' => $id];
        });
    }
}
