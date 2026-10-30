<?php

declare(strict_types=1);

namespace App\Interactors\Tesoreria;

use App\BusinessLogic\Tesoreria\ValidarOperacionTesoreria;
use App\Repository\Models\Tesoreria\OperacionTesoreria;
use App\Repository\Persistencia\Tesoreria\CajaRepositorio;
use App\Repository\Persistencia\Tesoreria\TesoreriaRepositorio;
use DomainException;
use Illuminate\Support\Str;

final readonly class GestionarEntregas
{
    public function __construct(private TesoreriaRepositorio $repo, private CajaRepositorio $cajas, private AutorizarTesoreria $autorizar, private EjecutarOperacionTesoreria $operacion, private ValidarOperacionTesoreria $validar, private GestionarFondos $fondos) {}

    public function entregar(int $actor, int $destino, float $importe, string $referencia, string $clave): void
    {
        $this->autorizar->ejecutar($actor, 'Entregar:Caja');
        $this->validar->importe($importe);
        $referencia = $this->validar->referencia($referencia);
        $this->operacion->ejecutar($actor, 'entregar_caja', $clave, [$destino, $importe, $referencia], function (OperacionTesoreria $o) use ($actor, $destino, $importe, $referencia): array {
            $actual = $this->cajas->activaUsuario($actor);
            if ($actual === null) {
                throw new DomainException('Abre tu turno para registrar la entrega.');
            }
            $s = $this->cajas->bloquearSesion($actual->id);
            $f = $this->repo->fondo($destino);
            $saldo = $this->cajas->saldo($s, $f->moneda_id);
            if ($s->estado !== 'abierta' || ! $f->activo || $saldo === null || $this->cajas->esperado($s, $saldo) < $importe) {
                throw new DomainException('Revisa el turno abierto, moneda y fondos disponibles.');
            }
            $e = $this->repo->crearEntrega(['sesion_id' => $s->id, 'fondo_id' => $destino, 'entregado_por' => $actor, 'importe' => $importe, 'referencia' => $referencia]);
            $this->cajas->crearMovimiento(['clave_operacion' => Str::uuid()->toString(), 'sesion_id' => $s->id, 'moneda_id' => $f->moneda_id, 'usuario_id' => $actor, 'tipo' => 'egreso', 'importe' => $importe, 'concepto' => 'Entrega en tránsito '.$e->id.' a '.$f->nombre, 'referencia' => 'ENTREGA-'.$e->id]);

            return ['entrega_id' => $e->id];
        });
    }

    public function recibir(int $actor, int $id, string $clave, ?int $extracto = null): void
    {
        $this->autorizar->ejecutar($actor, 'Recibir:Fondos');
        $this->operacion->ejecutar($actor, 'recibir_entrega', $clave, [$id, $extracto], function (OperacionTesoreria $o) use ($actor, $id, $extracto): array {
            $e = $this->repo->entrega($id);
            if ($e->estado !== 'en_transito' || $e->entregado_por === $actor) {
                throw new DomainException('Otro responsable debe confirmar la entrega pendiente.');
            }
            $x = $extracto !== null ? $this->repo->extracto($extracto) : null;
            $f = $this->repo->fondo($e->fondo_id);
            if ($f->tipo === 'banco' && ($x === null || $x->fondo_id !== $f->id || (float) $x->importe !== (float) $e->importe || $x->referencia !== $e->referencia || $this->repo->extractoUsado($x->id))) {
                throw new DomainException('El depósito requiere un abono bancario coincidente y pendiente de conciliación.');
            }
            $m = $this->fondos->anotar($f, $o, $actor, (float) $e->importe, 'Recepción de entrega '.$e->id, $e->referencia);
            if ($f->tipo === 'banco') {
                $this->repo->crearConciliacion(['extracto_id' => $x->id, 'movimiento_id' => $m, 'usuario_id' => $actor]);
            }
            $this->repo->actualizarEntrega($e, ['estado' => 'recibida', 'recibido_por' => $actor]);

            return ['entrega_id' => $id];
        });
    }
}
