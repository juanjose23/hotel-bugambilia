<?php

declare(strict_types=1);

namespace App\Interactors\Tesoreria;

use App\BusinessLogic\Tesoreria\ValidarOperacionTesoreria;
use App\Enums\Cuentas\MetodoPago;
use App\Interactors\Cuentas\Cobros\RegistrarPagoCuenta;
use App\Interactors\Cuentas\Gestion\CerrarCuentaYGenerarVenta;
use App\Repository\Models\Tesoreria\OperacionTesoreria;
use App\Repository\Persistencia\Tesoreria\TesoreriaRepositorio;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final readonly class GestionarComprobantes
{
    public function __construct(private TesoreriaRepositorio $repo, private AutorizarTesoreria $autorizar, private EjecutarOperacionTesoreria $operacion, private ValidarOperacionTesoreria $validar, private GestionarFondos $fondos, private ArchivosTesoreria $archivos, private RegistrarPagoCuenta $pago, private CerrarCuentaYGenerarVenta $cierre) {}

    public function presentar(int $actor, int $cuenta, int $banco, float $importe, string $fecha, string $referencia, UploadedFile $archivo, string $clave, ?int $aclaracion = null): void
    {
        if (! config('tesoreria.habilitado')) {
            abort(404);
        }
        $user = $this->repo->user($actor);
        $this->repo->cuentaPropia($cuenta, $user);
        $this->validar->importe($importe);
        $fecha = $this->validar->fecha($fecha);
        $referencia = $this->validar->referencia($referencia);
        $ruta = $this->archivos->guardar($archivo);
        $hash = $this->archivos->huella($ruta);
        try {
            $resultado = $this->operacion->ejecutar($actor, 'presentar_comprobante', $clave, [$cuenta, $banco, $importe, $fecha, $referencia, $hash, $aclaracion], function (OperacionTesoreria $o) use ($actor, $cuenta, $banco, $importe, $fecha, $referencia, $ruta, $hash, $aclaracion): array {
                $c = $this->repo->cuenta($cuenta);
                $f = $this->repo->fondo($banco);
                if (! $f->activo || $f->tipo !== 'banco' || $f->moneda_id !== $c->moneda_id || $importe > (float) $c->saldo || ! $this->repo->reservaAdmitePago($c)) {
                    throw new DomainException('Revisa el banco, la moneda y el saldo. Si la reserva ya venció o se canceló, solicita revisión al hotel.');
                }
                if ($aclaracion !== null) {
                    $v = $this->repo->comprobante($aclaracion);
                    if ($v->usuario_id !== $actor || $v->cuenta_id !== $cuenta || $v->estado !== 'aclaracion' || $v->fondo_id !== $banco || $v->referencia !== $referencia || (float) $v->importe !== $importe || $v->fecha->toDateString() !== $fecha) {
                        throw new DomainException('Solo puedes adjuntar una aclaración a tu comprobante solicitado, conservando los datos originales.');
                    }
                    $this->repo->actualizarComprobante($v, ['estado' => 'pendiente']);
                } else {
                    if ($this->repo->referenciaUsada($banco, $referencia)) {
                        throw new DomainException('Esta referencia ya fue reportada. Consulta su estado o solicita aclaración.');
                    }
                    $v = $this->repo->crearComprobante(['codigo' => Str::uuid()->toString(), 'cuenta_id' => $cuenta, 'fondo_id' => $banco, 'usuario_id' => $actor, 'importe' => $importe, 'fecha' => $fecha, 'referencia' => $referencia]);
                }
                $this->repo->crearEvidencia(['comprobante_id' => $v->id, 'ruta' => $ruta, 'sha256' => $hash, 'usuario_id' => $actor]);

                return ['comprobante_id' => $v->id, 'ruta' => $ruta];
            });
            if (($resultado['ruta'] ?? null) !== $ruta) {
                Storage::disk('private')->delete($ruta);
            }
        } catch (\Throwable $e) {
            Storage::disk('private')->delete($ruta);
            throw $e;
        }
    }

    public function revisar(int $actor, int $id, string $decision, string $motivo, string $clave, ?int $extracto = null, bool $emitirFactura = false, ?int $serieId = null): void
    {
        $this->autorizar->ejecutar($actor, 'Revisar:Comprobante');
        if ($emitirFactura) {
            $this->autorizar->ejecutar($actor, 'Create:Factura');
        }
        $motivo = $this->validar->texto($motivo);
        if (! in_array($decision, ['aprobado', 'rechazado', 'aclaracion'], true)) {
            throw new DomainException('Selecciona aprobar, rechazar o pedir aclaración.');
        }
        $this->operacion->ejecutar($actor, 'revisar_comprobante', $clave, [$id, $decision, $motivo, $extracto, $emitirFactura, $serieId], function (OperacionTesoreria $o) use ($actor, $id, $decision, $motivo, $extracto, $emitirFactura, $serieId): array {
            // Lock account first to match the existing payment transaction order.
            $cuentaId = $this->repo->cuentaDeComprobante($id);
            $cuenta = $this->repo->cuenta($cuentaId);
            $v = $this->repo->comprobante($id);
            if ($v->estado !== 'pendiente' || $v->usuario_id === $actor) {
                throw new DomainException('Otro usuario debe revisar el comprobante pendiente.');
            }
            if ($decision === 'aprobado') {
                if ($extracto === null) {
                    throw new DomainException('Elige el movimiento verificado en el extracto bancario.');
                }
                $e = $this->repo->extracto($extracto);
                $f = $this->repo->fondo($v->fondo_id);
                if ($e->fondo_id !== $f->id || (float) $e->importe !== (float) $v->importe || $e->referencia !== $v->referencia || $e->fecha->toDateString() !== $v->fecha->toDateString() || $cuenta->moneda_id !== $f->moneda_id || $this->repo->extractoUsado($e->id) || ! $this->repo->reservaAdmitePago($cuenta) || (float) $cuenta->saldo < (float) $v->importe) {
                    throw new DomainException('Referencia, fecha, importe y moneda deben coincidir con una partida bancaria pendiente y una cuenta vigente.');
                }
                $this->archivos->huella($this->repo->ultimaEvidencia($id)->ruta);
                $p = $this->pago->ejecutar($cuenta, MetodoPago::TRANSFERENCIA, (float) $v->importe, referenciaTransaccion: $v->referencia, monedaId: $f->moneda_id, usuarioId: $actor);
                $m = $this->fondos->anotar($f, $o, $actor, (float) $v->importe, 'Transferencia verificada de cuenta '.$cuenta->numero_cuenta, $v->referencia);
                $this->repo->crearConciliacion(['extracto_id' => $e->id, 'movimiento_id' => $m, 'usuario_id' => $actor]);
                $cuenta->refresh();
                if ((float) $cuenta->saldo <= 0 && $cuenta->estado->puedeCerrarse()) {
                    $this->cierre->ejecutar($cuenta, $actor, ['emitir_factura' => $emitirFactura, 'factura_serie_id' => $serieId]);
                } elseif ($emitirFactura) {
                    throw new DomainException('La cuenta debe estar liquidada y habilitada para cierre antes de emitir la factura.');
                }
                $v->setAttribute('pago_cuenta_id', $p->id);
                $v->extracto_id = $e->id;
            }
            $v->estado = $decision;
            $v->decision = $motivo;
            $v->setAttribute('revisado_por', $actor);
            $v->revisado_at = now();
            $this->repo->guardarComprobante($v);
            $this->repo->crearDecision(['comprobante_id' => $id, 'usuario_id' => $actor, 'decision' => $decision, 'motivo' => $motivo]);

            return ['comprobante_id' => $id];
        });
    }
}
