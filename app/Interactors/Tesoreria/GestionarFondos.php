<?php

declare(strict_types=1);

namespace App\Interactors\Tesoreria;

use App\BusinessLogic\Tesoreria\ValidarOperacionTesoreria;
use App\Repository\Models\Tesoreria\FondoTesoreria;
use App\Repository\Models\Tesoreria\OperacionTesoreria;
use App\Repository\Persistencia\Tesoreria\TesoreriaRepositorio;
use DomainException;

final readonly class GestionarFondos
{
    public function __construct(private TesoreriaRepositorio $repo, private AutorizarTesoreria $autorizar, private EjecutarOperacionTesoreria $operacion, private ValidarOperacionTesoreria $validar) {}

    public function crear(int $actor, string $codigo, string $nombre, string $tipo, int $moneda, int $responsable, float $saldo, string $origen, float $limite, string $clave, ?string $banco = null, ?string $identificacion = null): void
    {
        $this->autorizar->ejecutar($actor, 'Administrar:Fondos');
        $nombre = $this->validar->texto($nombre, 3, 100);
        $origen = $this->validar->texto($origen, 10, 950);
        $codigo = mb_strtoupper(trim($codigo));
        if (preg_match('/^[A-Z0-9_-]{2,30}$/', $codigo) !== 1 || ! in_array($tipo, ['banco', 'caja_chica', 'boveda'], true) || ! is_finite($saldo) || ! is_finite($limite) || $saldo < 0 || $limite < 0 || ($tipo === 'banco' && (empty($banco) || empty($identificacion)))) {
            throw new DomainException('Revisa el código, tipo, banco, cuenta y fondo autorizado.');
        }
        if ($saldo > 0) {
            $this->validar->importe($saldo);
        }if ($limite > 0) {
            $this->validar->importe($limite);
        }
        $this->operacion->ejecutar($actor, 'crear_fondo', $clave, [$codigo, $nombre, $tipo, $moneda, $responsable, $saldo, $origen, $limite, $banco, $identificacion], function (OperacionTesoreria $o) use ($actor, $codigo, $nombre, $tipo, $moneda, $responsable, $saldo, $origen, $limite, $banco, $identificacion): array {
            if ($this->repo->codigoFondoExiste($codigo)) {
                throw new DomainException('El código del fondo ya existe.');
            }
            $f = $this->repo->crearFondo(['codigo' => $codigo, 'nombre' => $nombre, 'tipo' => $tipo, 'moneda_id' => $moneda, 'responsable_id' => $responsable, 'limite_vale' => $limite, 'activo' => true, 'banco' => $banco, 'identificacion' => $identificacion]);
            if ($saldo > 0) {
                $this->anotar($f, $o, $actor, $saldo, 'Saldo inicial documentado: '.$origen, 'APERTURA');
            }

            return ['fondo_id' => $f->id];
        });
    }

    public function transferir(int $actor, int $origen, int $destino, float $importe, string $concepto, string $clave): void
    {
        $this->autorizar->ejecutar($actor, 'Transferir:Fondos');
        $this->validar->importe($importe);
        $concepto = $this->validar->texto($concepto);
        if ($origen === $destino) {
            throw new DomainException('Selecciona fondos distintos.');
        }
        $this->operacion->ejecutar($actor, 'transferencia', $clave, [$origen, $destino, $importe, $concepto], function (OperacionTesoreria $o) use ($actor, $origen, $destino, $importe, $concepto): array {
            $fondos = $this->repo->bloquearFondos([$origen, $destino]);
            $a = $fondos->firstWhere('id', $origen);
            $b = $fondos->firstWhere('id', $destino);
            if ($a === null || $b === null || $a->moneda_id !== $b->moneda_id) {
                throw new DomainException('La transferencia requiere dos fondos en la misma moneda.');
            }
            $this->anotar($a, $o, $actor, -$importe, $concepto, 'TRASLADO-'.$o->id);
            $this->anotar($b, $o, $actor, $importe, $concepto, 'TRASLADO-'.$o->id);

            return ['operacion_id' => $o->id];
        });
    }

    public function movimiento(int $actor, int $fondo, float $importe, string $concepto, string $referencia, string $clave): void
    {
        $this->autorizar->ejecutar($actor, 'Administrar:Fondos');
        $this->validar->importe(abs($importe));
        $concepto = $this->validar->texto($concepto);
        $referencia = $this->validar->referencia($referencia);
        $this->operacion->ejecutar($actor, 'movimiento_banco', $clave, [$fondo, $importe, $concepto, $referencia], function (OperacionTesoreria $o) use ($actor, $fondo, $importe, $concepto, $referencia): array {
            $f = $this->repo->fondo($fondo);
            if ($f->tipo !== 'banco') {
                throw new DomainException('Para caja chica usa vales y reposiciones; para bóveda usa traslados.');
            }
            $m = $this->anotar($f, $o, $actor, $importe, $concepto, $referencia);

            return ['movimiento_id' => $m];
        });
    }

    public function anotar(FondoTesoreria $f, OperacionTesoreria $o, int $actor, float $importe, string $concepto, string $referencia): int
    {
        // Caller holds the fund row lock inside the operation transaction.
        if (! $f->activo) {
            throw new DomainException('El fondo está inactivo.');
        }
        $centavos = $this->validar->importe(abs($importe));
        $saldo = $this->repo->saldoCentavos($f->id);
        if ($importe < 0 && $saldo < $centavos) {
            throw new DomainException('El fondo no tiene saldo suficiente.');
        }

        return $this->repo->crearMovimiento(['fondo_id' => $f->id, 'operacion_id' => $o->id, 'usuario_id' => $actor, 'importe' => $importe, 'concepto' => mb_substr($concepto, 0, 1000), 'referencia' => $referencia])->id;
    }
}
