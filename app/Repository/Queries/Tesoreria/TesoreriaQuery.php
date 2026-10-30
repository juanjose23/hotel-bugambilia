<?php

declare(strict_types=1);

namespace App\Repository\Queries\Tesoreria;

use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Tesoreria\AbastecimientoCaja;
use App\Repository\Models\Tesoreria\ComprobanteTransferencia;
use App\Repository\Models\Tesoreria\ConciliacionBancaria;
use App\Repository\Models\Tesoreria\DecisionComprobante;
use App\Repository\Models\Tesoreria\DevolucionCaja;
use App\Repository\Models\Tesoreria\EntregaCaja;
use App\Repository\Models\Tesoreria\EvidenciaTransferencia;
use App\Repository\Models\Tesoreria\ExtractoBancario;
use App\Repository\Models\Tesoreria\FondoTesoreria;
use App\Repository\Models\Tesoreria\MovimientoCaja;
use App\Repository\Models\Tesoreria\MovimientoFondo;
use App\Repository\Models\Tesoreria\ValeCajaChica;
use App\Repository\Models\User;
use App\Repository\Persistencia\Tesoreria\TesoreriaRepositorio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

final readonly class TesoreriaQuery
{
    public function __construct(private TesoreriaRepositorio $repo) {}

    /** @return Builder<FondoTesoreria> */
    public function fondos(): Builder
    {
        return FondoTesoreria::query()->with(['moneda', 'responsable'])->withSum('movimientos as saldo', 'importe')->orderBy('codigo');
    }

    /** @return array<int,string> */
    public function opcionesFondos(?string $tipo = null, ?int $responsable = null): array
    {
        $r = [];
        foreach (FondoTesoreria::query()->where('activo', true)->when($tipo !== null, fn ($q) => $q->where('tipo', $tipo))->when($responsable !== null, fn ($q) => $q->where('responsable_id', $responsable))->with('moneda')->get() as $f) {
            $r[$f->id] = $f->nombre.' · '.($f->moneda->codigo ?? 'Sin moneda');
        }

        return $r;
    }

    /** @return array<int,string> */
    public function usuarios(): array
    {
        $r = [];
        foreach (User::query()->where('is_admin', true)->orderBy('name')->get() as $u) {
            $r[$u->id] = $u->name;
        }

        return $r;
    }

    /** @return array<int,string> */
    public function monedas(): array
    {
        $r = [];
        foreach (Moneda::query()->get() as $m) {
            $r[$m->id] = $m->codigo;
        }

        return $r;
    }

    /** @return Builder<MovimientoFondo> */
    public function movimientos(): Builder
    {
        return MovimientoFondo::query()->with('fondo.moneda')->orderByDesc('id');
    }

    /** @return Builder<ValeCajaChica> */
    public function vales(User $user): Builder
    {
        return ValeCajaChica::query()->when(! $user->can('Aprobar:CajaChica') && ! $user->can('Auditar:Fondos'), fn ($q) => $q->where('solicitante_id', $user->id))->with(['fondo.moneda', 'solicitante'])->orderByDesc('id');
    }

    /** @return Builder<ComprobanteTransferencia> */
    public function comprobantes(): Builder
    {
        return ComprobanteTransferencia::query()->with(['cuenta.moneda', 'fondo', 'usuario'])->orderByDesc('id');
    }

    /** @return Builder<ExtractoBancario> */
    public function extractos(): Builder
    {
        return ExtractoBancario::query()->with(['fondo.moneda', 'conciliacion'])->orderByDesc('id');
    }

    /** @return array<int,string> */
    public function extractosPendientes(): array
    {
        $r = [];
        foreach (ExtractoBancario::query()->whereNotIn('id', ConciliacionBancaria::query()->select('extracto_id'))->with('fondo.moneda')->orderByDesc('id')->limit(200)->get() as $e) {
            $r[$e->id] = ($e->fondo->nombre ?? 'Fondo no disponible').' · '.$e->referencia.' · '.$e->importe.' '.($e->fondo?->moneda->codigo ?? 'Sin moneda');
        }

        return $r;
    }

    /** @return array<int,string> */
    public function movimientosPendientes(): array
    {
        $r = [];
        foreach (MovimientoFondo::query()->whereHas('fondo', fn ($q) => $q->where('tipo', 'banco'))->whereNotIn('id', ConciliacionBancaria::query()->select('movimiento_id'))->with('fondo.moneda')->orderByDesc('id')->limit(200)->get() as $m) {
            $r[$m->id] = ($m->fondo->nombre ?? 'Fondo no disponible').' · '.$m->referencia.' · '.$m->importe.' '.($m->fondo?->moneda->codigo ?? 'Sin moneda');
        }

        return $r;
    }

    /** @return array<int,string> */
    public function pasarelasPendientes(): array
    {
        $r = [];
        foreach (PagoTransaccion::query()->where('estado', EstadoTransaccionPago::Capturada)->whereNotIn('id', ConciliacionBancaria::query()->whereNotNull('pago_transaccion_id')->select('pago_transaccion_id'))->with('moneda')->orderByDesc('id')->limit(200)->get() as $p) {
            $r[$p->id] = '#'.$p->id.' · '.$p->referencia_pasarela.' · '.$p->monto.' '.($p->moneda->codigo ?? '');
        }

        return $r;
    }

    public function conciliado(int $extracto): bool
    {
        return $this->repo->extractoUsado($extracto);
    }

    /** @return Builder<DevolucionCaja> */
    public function devoluciones(User $user): Builder
    {
        return DevolucionCaja::query()->when(! $user->can('Aprobar:Devolucion') && ! $user->can('Auditar:Fondos'), fn ($q) => $q->where('solicitante_id', $user->id))->with(['pago.moneda', 'sesion.caja'])->orderByDesc('id');
    }

    /** @return array<int,string> */
    public function pagosEfectivo(): array
    {
        $r = [];
        foreach (PagoCuenta::query()->where('forma_pago', MetodoPago::EFECTIVO)->where('estado', EstadoPago::APLICADO)->whereIn('id', MovimientoCaja::query()->where('tipo', 'cobro')->select('pago_cuenta_id'))->with(['cuenta', 'moneda'])->orderByDesc('id')->limit(200)->get() as $p) {
            $r[$p->id] = 'Pago '.$p->id.' · '.$p->cuenta->numero_cuenta.' · '.$p->monto.' '.($p->moneda->codigo ?? '');
        }

        return $r;
    }

    /** @return Builder<EntregaCaja> */
    public function entregas(User $user): Builder
    {
        return EntregaCaja::query()->when(! $user->can('Recibir:Fondos') && ! $user->can('Auditar:Fondos'), fn ($q) => $q->where('entregado_por', $user->id))->with(['fondo.moneda', 'sesion.usuario'])->orderByDesc('id');
    }

    /** @return list<array{id:int,fondo_id:int,estado:string,importe:string,referencia:string,decision:string|null,fecha:string,evidencia_url:string,aclaracion:bool}> */
    public function comprobantesCliente(int $cuenta, int $actor): array
    {
        $this->repo->cuentaPropia($cuenta, $this->repo->user($actor));
        $r = [];
        foreach (ComprobanteTransferencia::query()->where('cuenta_id', $cuenta)->where('usuario_id', $actor)->latest('id')->get() as $v) {
            $r[] = ['id' => $v->id, 'fondo_id' => $v->fondo_id, 'estado' => $v->estado, 'importe' => $v->importe, 'referencia' => $v->referencia, 'decision' => $v->decision, 'fecha' => $v->fecha->toDateString(), 'evidencia_url' => route('tesoreria.archivo', ['tipo' => 'comprobante', 'id' => $v->id]), 'aclaracion' => $v->estado === 'aclaracion'];
        }

        return $r;
    }

    /** @return list<array{id:int,nombre:string,banco:string|null,identificacion:string|null,moneda_id:int}> */
    public function bancosCliente(): array
    {
        $r = [];
        foreach (FondoTesoreria::query()->where('tipo', 'banco')->where('activo', true)->get() as $f) {
            $r[] = ['id' => $f->id, 'nombre' => $f->nombre, 'banco' => $f->banco, 'identificacion' => $f->identificacion, 'moneda_id' => $f->moneda_id];
        }

        return $r;
    }

    /** @return list<array{id:int,url:string,fecha:string,es_imagen:bool}> */
    public function evidencias(int $id): array
    {
        $r = [];
        foreach (EvidenciaTransferencia::query()->where('comprobante_id', $id)->get() as $e) {
            $r[] = ['id' => $e->id, 'es_imagen' => str_starts_with(Storage::disk('private')->mimeType($e->ruta) ?: '', 'image/'), 'url' => route('tesoreria.archivo', ['tipo' => 'evidencia', 'id' => $e->id]), 'fecha' => $e->created_at?->format('d/m/Y H:i') ?? ''];
        }

        return $r;
    }

    /** @return list<array{decision:string,motivo:string}> */
    public function decisiones(int $id): array
    {
        $r = [];
        foreach (DecisionComprobante::query()->where('comprobante_id', $id)->orderBy('id')->get() as $d) {
            $r[] = ['decision' => $d->decision, 'motivo' => $d->motivo];
        }

        return $r;
    }

    public function archivo(User $user, string $tipo, int $id): string
    {
        abort_unless(config('tesoreria.habilitado') === true, 404);
        if ($tipo === 'comprobante' || $tipo === 'evidencia') {
            $e = $tipo === 'comprobante' ? $this->repo->ultimaEvidencia($id) : EvidenciaTransferencia::query()->findOrFail($id);
            $c = ComprobanteTransferencia::query()->findOrFail($e->comprobante_id);
            if (! $user->can('Revisar:Comprobante') && ! $user->can('Auditar:Fondos')) {
                abort_unless($c->usuario_id === $user->id, 403);
                $this->repo->cuentaPropia($c->cuenta_id, $user);
            }

            return $e->ruta;
        }
        if ($tipo === 'extracto') {
            abort_unless($user->can('Registrar:Extracto') || $user->can('Conciliar:Banco') || $user->can('Auditar:Fondos'), 403);

            return ExtractoBancario::query()->findOrFail($id)->soporte;
        }
        if ($tipo === 'vale') {
            $v = ValeCajaChica::query()->findOrFail($id);
            abort_unless($v->solicitante_id === $user->id || $user->can('Aprobar:CajaChica') || $user->can('Auditar:Fondos'), 403);
            abort_if($v->soporte === null, 404);

            return $v->soporte;
        }
        abort(404);
    }

    /** @return \Generator<int,list<string|int|float|null>> */
    public function auditoria(): \Generator
    {
        foreach (MovimientoFondo::query()->with(['fondo.moneda'])->orderBy('id')->lazyById() as $m) {
            yield ['fondo', $m->id, $m->created_at?->toIso8601String(), ($m->fondo->codigo ?? ''), ($m->fondo?->moneda->codigo ?? 'Sin moneda'), $m->usuario_id, (float) $m->importe, $m->concepto, $m->referencia];
        }
        foreach (MovimientoCaja::query()->with('moneda')->orderBy('id')->lazyById() as $m) {
            yield ['caja', $m->id, $m->created_at?->toIso8601String(), 'SESION-'.$m->sesion_id, $m->moneda->codigo ?? '', $m->usuario_id, in_array($m->tipo, ['egreso', 'vuelto'], true) ? -(float) $m->importe : (float) $m->importe, $m->concepto, $m->referencia];
        }
    }

    /** @return Builder<AbastecimientoCaja> */
    public function abastecimientos(User $user): Builder
    {
        return AbastecimientoCaja::query()->when(! $user->can('Autorizar:FondoTurno') && ! $user->can('Auditar:Fondos'), fn ($q) => $q->where('solicitante_id', $user->id))->with(['fondo.moneda', 'sesion.usuario'])->orderByDesc('id');
    }
}
