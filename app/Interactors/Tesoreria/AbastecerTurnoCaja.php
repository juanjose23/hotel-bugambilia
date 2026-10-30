<?php

declare(strict_types=1);

namespace App\Interactors\Tesoreria;

use App\BusinessLogic\Tesoreria\ValidarOperacionTesoreria;
use App\Repository\Models\Tesoreria\OperacionTesoreria;
use App\Repository\Persistencia\Tesoreria\CajaRepositorio;
use App\Repository\Persistencia\Tesoreria\TesoreriaRepositorio;
use DomainException;
use Illuminate\Support\Str;

final readonly class AbastecerTurnoCaja
{
    public function __construct(private TesoreriaRepositorio $repo, private CajaRepositorio $cajas, private AutorizarTesoreria $autorizar, private EjecutarOperacionTesoreria $operacion, private ValidarOperacionTesoreria $validar, private GestionarFondos $fondos) {}

    public function solicitar(int $actor, int $origen, float $importe, string $referencia, string $clave): void
    {
        $this->autorizar->ejecutar($actor, 'Solicitar:FondoTurno');
        $this->validar->importe($importe);
        $referencia = $this->validar->referencia($referencia);
        $this->operacion->ejecutar($actor, 'solicitar_fondo_turno', $clave, [$origen, $importe, $referencia], function (OperacionTesoreria $o) use ($actor, $origen, $importe, $referencia): array {
            $a = $this->cajas->activaUsuario($actor);
            if ($a === null) {
                throw new DomainException('Abre tu turno antes de solicitar el fondo.');
            }$s = $this->cajas->bloquearSesion($a->id);
            $f = $this->repo->fondo($origen);
            if ($s->estado !== 'abierta' || ! $f->activo || $this->cajas->saldo($s, $f->moneda_id) === null) {
                throw new DomainException('El turno y la moneda del fondo deben estar habilitados.');
            }
            $v = $this->repo->crearAbastecimiento(['sesion_id' => $s->id, 'fondo_id' => $origen, 'solicitante_id' => $actor, 'importe' => $importe, 'referencia' => $referencia]);

            return ['abastecimiento_id' => $v->id];
        });
    }

    public function resolver(int $actor, int $id, bool $aprobar, string $motivo, string $clave, ?int $extracto = null): void
    {
        $this->autorizar->ejecutar($actor, 'Autorizar:FondoTurno');
        $motivo = $this->validar->texto($motivo);
        $this->operacion->ejecutar($actor, 'resolver_fondo_turno', $clave, [$id, $aprobar, $motivo, $extracto], function (OperacionTesoreria $o) use ($actor, $id, $aprobar, $motivo, $extracto): array {
            $v = $this->repo->abastecimiento($id);
            if ($v->estado !== 'pendiente' || $v->solicitante_id === $actor) {
                throw new DomainException('Otro usuario debe resolver la solicitud pendiente.');
            }
            if ($aprobar) {
                $s = $this->cajas->bloquearSesion($v->sesion_id);
                $x = $extracto !== null ? $this->repo->extracto($extracto) : null;
                $f = $this->repo->fondo($v->fondo_id);
                if ($s->estado !== 'abierta' || $this->cajas->saldo($s, $f->moneda_id) === null) {
                    throw new DomainException('El turno ya no está disponible para recibir el fondo.');
                }
                if ($f->tipo === 'banco' && ($x === null || $x->fondo_id !== $f->id || (float) $x->importe !== -(float) $v->importe || $x->referencia !== $v->referencia || $this->repo->extractoUsado($x->id))) {
                    throw new DomainException('El retiro del banco requiere el débito coincidente del extracto.');
                }
                $m = $this->fondos->anotar($f, $o, $actor, -(float) $v->importe, 'Fondo entregado al turno '.$s->id, $v->referencia);
                if ($f->tipo === 'banco') {
                    $this->repo->crearConciliacion(['extracto_id' => $x->id, 'movimiento_id' => $m, 'usuario_id' => $actor]);
                }
                $this->cajas->crearMovimiento(['clave_operacion' => Str::uuid()->toString(), 'sesion_id' => $s->id, 'moneda_id' => $f->moneda_id, 'usuario_id' => $actor, 'tipo' => 'ingreso', 'importe' => $v->importe, 'concepto' => 'Fondo recibido de '.$f->nombre.'; solicitud '.$v->id, 'referencia' => 'ABASTECIMIENTO-'.$v->id]);
            }
            $this->repo->actualizarAbastecimiento($v, ['estado' => $aprobar ? 'aprobado' : 'rechazado', 'aprobado_por' => $actor, 'decision' => $motivo]);

            return ['abastecimiento_id' => $id];
        });
    }
}
