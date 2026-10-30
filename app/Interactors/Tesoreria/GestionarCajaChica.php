<?php

declare(strict_types=1);

namespace App\Interactors\Tesoreria;

use App\BusinessLogic\Tesoreria\ValidarOperacionTesoreria;
use App\Repository\Models\Tesoreria\OperacionTesoreria;
use App\Repository\Persistencia\Tesoreria\TesoreriaRepositorio;
use DomainException;

final readonly class GestionarCajaChica
{
    public function __construct(private TesoreriaRepositorio $repo, private AutorizarTesoreria $autorizar, private EjecutarOperacionTesoreria $operacion, private ValidarOperacionTesoreria $validar, private GestionarFondos $fondos, private ArchivosTesoreria $archivos) {}

    public function solicitar(int $actor, int $fondo, string $beneficiario, float $importe, string $concepto, string $vence, string $clave): void
    {
        $this->autorizar->ejecutar($actor, 'Operar:CajaChica');
        $this->validar->importe($importe);
        $concepto = $this->validar->texto($concepto);
        $beneficiario = $this->validar->texto($beneficiario, 3, 150);
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $vence) || $vence < today()->toDateString() || ! checkdate((int) substr($vence, 5, 2), (int) substr($vence, 8, 2), (int) substr($vence, 0, 4))) {
            throw new DomainException('Indica una fecha válida de liquidación.');
        }
        $this->operacion->ejecutar($actor, 'solicitar_vale', $clave, [$fondo, $beneficiario, $importe, $concepto, $vence], function (OperacionTesoreria $o) use ($actor, $fondo, $beneficiario, $importe, $concepto, $vence): array {
            $f = $this->repo->fondo($fondo);
            if ($f->tipo !== 'caja_chica' || ! $f->activo || $f->responsable_id !== $actor || $importe > (float) $f->limite_vale) {
                throw new DomainException('Revisa el responsable, estado y límite del fondo de caja chica.');
            }
            $v = $this->repo->crearVale(['fondo_id' => $fondo, 'solicitante_id' => $actor, 'beneficiario' => $beneficiario, 'importe' => $importe, 'concepto' => $concepto, 'vence_at' => $vence]);

            return ['vale_id' => $v->id];
        });
    }

    public function resolver(int $actor, int $id, bool $aprobar, string $motivo, string $clave): void
    {
        $this->autorizar->ejecutar($actor, 'Aprobar:CajaChica');
        $motivo = $this->validar->texto($motivo);
        $this->operacion->ejecutar($actor, 'resolver_vale', $clave, [$id, $aprobar, $motivo], function (OperacionTesoreria $o) use ($actor, $id, $aprobar, $motivo): array {
            $v = $this->repo->vale($id);
            if ($v->solicitante_id === $actor || $v->estado !== 'solicitado') {
                throw new DomainException('Otro usuario debe revisar el vale pendiente.');
            }
            if ($aprobar) {
                $f = $this->repo->fondo($v->fondo_id);
                $this->fondos->anotar($f, $o, $actor, -(float) $v->importe, 'Vale '.$v->id.': '.$v->concepto, 'VALE-'.$v->id);
            }
            $this->repo->actualizarVale($v, ['estado' => $aprobar ? 'entregado' : 'rechazado', 'aprobado_por' => $actor, 'resolucion' => $motivo]);

            return ['vale_id' => $v->id];
        });
    }

    public function liquidar(int $actor, int $id, float $gastado, string $referencia, string $soporte, string $clave): void
    {
        $this->autorizar->ejecutar($actor, 'Aprobar:CajaChica');
        if ($gastado !== 0.0) {
            $this->validar->importe($gastado);
        }
        $referencia = $this->validar->referencia($referencia);
        $hash = $this->archivos->huella($soporte);
        $this->operacion->ejecutar($actor, 'liquidar_vale', $clave, [$id, $gastado, $referencia, $soporte, $hash], function (OperacionTesoreria $o) use ($actor, $id, $gastado, $referencia, $soporte): array {
            $v = $this->repo->vale($id);
            if ($v->estado !== 'entregado' || $actor === $v->solicitante_id || $gastado > (float) $v->importe) {
                throw new DomainException('La liquidación requiere otro revisor y no puede superar el vale entregado.');
            }
            $f = $this->repo->fondo($v->fondo_id);
            $devuelto = round((float) $v->importe - $gastado, 2);
            if ($devuelto > 0) {
                $this->fondos->anotar($f, $o, $actor, $devuelto, 'Remanente físico devuelto del vale '.$v->id, 'LIQUIDACION-'.$v->id);
            }
            $this->repo->actualizarVale($v, ['estado' => 'liquidado', 'gastado' => $gastado, 'devuelto' => $devuelto, 'referencia' => $referencia, 'soporte' => $soporte, 'liquidado_por' => $actor]);

            return ['vale_id' => $v->id];
        });
    }
}
