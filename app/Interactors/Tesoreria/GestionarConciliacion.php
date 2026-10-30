<?php

declare(strict_types=1);

namespace App\Interactors\Tesoreria;

use App\BusinessLogic\Tesoreria\ValidarOperacionTesoreria;
use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Repository\Models\Tesoreria\OperacionTesoreria;
use App\Repository\Persistencia\Tesoreria\TesoreriaRepositorio;
use DomainException;

final readonly class GestionarConciliacion
{
    public function __construct(private TesoreriaRepositorio $repo, private AutorizarTesoreria $autorizar, private EjecutarOperacionTesoreria $operacion, private ValidarOperacionTesoreria $validar, private GestionarFondos $fondos, private ArchivosTesoreria $archivos) {}

    public function registrar(int $actor, int $fondo, string $fecha, string $referencia, float $importe, string $soporte, string $clave): void
    {
        $this->autorizar->ejecutar($actor, 'Registrar:Extracto');
        $this->validar->importe(abs($importe));
        $fecha = $this->validar->fecha($fecha);
        $referencia = $this->validar->referencia($referencia);
        $hash = $this->archivos->huella($soporte);
        $this->operacion->ejecutar($actor, 'extracto', $clave, [$fondo, $fecha, $referencia, $importe, $hash], function (OperacionTesoreria $o) use ($actor, $fondo, $fecha, $referencia, $importe, $soporte, $hash): array {
            $f = $this->repo->fondo($fondo);
            if ($f->tipo !== 'banco' || ! $f->activo) {
                throw new DomainException('Selecciona una cuenta bancaria activa.');
            }
            if ($this->repo->extractoReferencia($fondo, $referencia)) {
                throw new DomainException('La referencia bancaria ya fue registrada en esta cuenta.');
            }
            $e = $this->repo->crearExtracto(['fondo_id' => $fondo, 'fecha' => $fecha, 'referencia' => $referencia, 'importe' => $importe, 'soporte' => $soporte, 'sha256' => $hash, 'registrado_por' => $actor]);

            return ['extracto_id' => $e->id];
        });
    }

    public function conciliar(int $actor, int $extracto, int $movimiento, string $clave): void
    {
        $this->autorizar->ejecutar($actor, 'Conciliar:Banco');
        $this->operacion->ejecutar($actor, 'conciliar', $clave, [$extracto, $movimiento], function (OperacionTesoreria $o) use ($actor, $extracto, $movimiento): array {
            $e = $this->repo->extracto($extracto);
            $m = $this->repo->movimiento($movimiento);
            if ($e->fondo_id !== $m->fondo_id || $e->importe !== $m->importe || $this->repo->extractoUsado($e->id) || $this->repo->movimientoConciliado($m->id)) {
                throw new DomainException('Cuenta e importe deben coincidir y ambas partidas deben estar pendientes.');
            }
            $c = $this->repo->crearConciliacion(['extracto_id' => $e->id, 'movimiento_id' => $m->id, 'usuario_id' => $actor]);

            return ['conciliacion_id' => $c->id];
        });
    }

    public function liquidarPasarela(int $actor, int $transaccion, int $extracto, float $comision, string $clave): void
    {
        $this->autorizar->ejecutar($actor, 'Conciliar:Banco');
        if ($comision < 0) {
            throw new DomainException('La comisión no puede ser negativa.');
        }if ($comision > 0) {
            $this->validar->importe($comision);
        }
        $this->operacion->ejecutar($actor, 'liquidar_pasarela', $clave, [$transaccion, $extracto, $comision], function (OperacionTesoreria $o) use ($actor, $transaccion, $extracto, $comision): array {
            $t = $this->repo->transaccion($transaccion);
            $e = $this->repo->extracto($extracto);
            $f = $this->repo->fondo($e->fondo_id);
            $neto = round((float) $t->monto - $comision, 2);
            if ($t->estado !== EstadoTransaccionPago::Capturada || $f->tipo !== 'banco' || $f->moneda_id !== $t->moneda_id || $neto <= 0 || $neto !== (float) $e->importe || $this->repo->extractoUsado($e->id) || $this->repo->transaccionConciliada($t->id)) {
                throw new DomainException('La liquidación requiere captura pendiente, misma moneda e importe neto comprobado en el banco.');
            }
            $m = $this->fondos->anotar($f, $o, $actor, $neto, 'Liquidación pasarela '.$t->id.'; comisión '.$comision, $e->referencia);
            $c = $this->repo->crearConciliacion(['extracto_id' => $e->id, 'movimiento_id' => $m, 'pago_transaccion_id' => $t->id, 'comision' => $comision, 'usuario_id' => $actor]);
            $this->repo->actualizarConciliacionPasarela($t->id, (float) $t->monto, $neto, $actor);

            return ['conciliacion_id' => $c->id];
        });
    }
}
