<?php

declare(strict_types=1);

namespace App\Repository\Queries\Tesoreria;

use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Facturacion\FacturaSerie;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Tesoreria\Caja;
use App\Repository\Models\Tesoreria\MovimientoCaja;
use App\Repository\Models\Tesoreria\SesionCaja;
use App\Repository\Persistencia\Tesoreria\CajaRepositorio;
use Illuminate\Database\Eloquent\Builder;

final readonly class OperacionCajaQuery
{
    public function __construct(private CajaRepositorio $cajas) {}

    /** @return Builder<Caja> */
    public function listado(): Builder
    {
        return Caja::query()->with(['monedas', 'sesionActiva.usuario'])->orderBy('codigo');
    }

    /** @return array<int,string> */
    public function monedas(): array
    {
        $r = [];
        foreach (Moneda::query()->orderBy('codigo')->get() as $m) {
            $r[$m->id] = $m->codigo;
        }

        return $r;
    }

    /** @return array<int,string> */
    public function monedasCaja(?int $id): array
    {
        if ($id === null) {
            return [];
        } $c = Caja::query()->with('monedas')->find($id);
        $r = [];
        foreach ($c !== null ? $c->monedas : [] as $m) {
            $r[$m->id] = $m->codigo;
        }

        return $r;
    }

    /** @return array<int,string> */
    public function disponibles(): array
    {
        $r = [];
        foreach (Caja::query()->where('activa', true)->whereDoesntHave('sesiones', fn ($q) => $q->whereIn('estado', ['abierta', 'pendiente_revision']))->orderBy('nombre')->get() as $c) {
            $r[$c->id] = $c->nombre.' · '.$c->ubicacion;
        }

        return $r;
    }

    public function miSesion(int $actorId): ?SesionCaja
    {
        return SesionCaja::query()->where('usuario_id', $actorId)->orderByRaw("CASE WHEN estado IN ('abierta','pendiente_revision') THEN 0 ELSE 1 END")->orderByDesc('id')->with(['caja', 'usuario', 'saldos.moneda'])->first();
    }

    /** @return Builder<MovimientoCaja> */
    public function movimientos(int $actorId): Builder
    {
        $s = $this->miSesion($actorId);

        return MovimientoCaja::query()->where('sesion_id', $s !== null ? $s->id : 0)->with(['moneda', 'usuario'])->orderByDesc('id');
    }

    /** @return array<int,string> */
    public function cuentas(): array
    {
        $r = [];
        foreach (Cuenta::query()->whereIn('estado', [1, 2, 3, 4])->where('saldo', '>', 0)->with('moneda')->orderByDesc('id')->limit(100)->get() as $c) {
            $r[$c->id] = $c->numero_cuenta.' · saldo '.number_format((float) $c->saldo, 2).' '.($c->moneda->codigo ?? '');
        }

        return $r;
    }

    /** @return array<int,string> */
    public function series(): array
    {
        $r = [];
        foreach (FacturaSerie::query()->where('activa', true)->orderBy('codigo')->get() as $s) {
            $r[$s->id] = $s->codigo;
        }

        return $r;
    }

    /** @return list<array{moneda_id:int,moneda:string,apertura:float,esperado:float,contado:float|null,diferencia:float|null}> */
    public function resumen(SesionCaja $sesion): array
    {
        $r = [];
        foreach ($this->cajas->saldos($sesion) as $saldo) {
            $r[] = ['moneda_id' => $saldo->moneda_id, 'moneda' => $saldo->moneda->codigo ?? '', 'apertura' => (float) $saldo->apertura, 'esperado' => $sesion->estado === 'abierta' ? $this->cajas->esperado($sesion, $saldo) : (float) $saldo->esperado_cierre, 'contado' => $saldo->contado !== null ? (float) $saldo->contado : null, 'diferencia' => $saldo->diferencia !== null ? (float) $saldo->diferencia : null];
        }

        return $r;
    }

    /** @return array<int,string> */
    public function monedasTurno(int $actorId): array
    {
        $s = $this->miSesion($actorId);
        $r = [];
        if ($s === null) {
            return $r;
        }foreach ($this->resumen($s) as $fila) {
            $r[$fila['moneda_id']] = $fila['moneda'];
        }

        return $r;
    }

    /** @return Builder<SesionCaja> */
    public function historial(int $actorId, bool $auditor): Builder
    {
        return SesionCaja::query()->when(! $auditor, fn ($q) => $q->where('usuario_id', $actorId))->with(['caja', 'usuario', 'saldos.moneda'])->orderByDesc('id');
    }
}
