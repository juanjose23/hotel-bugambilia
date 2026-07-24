<?php

declare(strict_types=1);

namespace App\Interactors\CuentasEstancia;

use App\Enums\Estancias\EstadoCuentaEstancia;
use App\Enums\Estancias\TipoTitular;
use App\Events\Estancias\CuentaEstanciaSolicitada;
use App\Repository\Models\Estancias\CuentaEstancia;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\Reserva;
use DomainException;
use Illuminate\Support\Facades\DB;

final class SolicitarCuentaEstancia
{
    public function ejecutar(
        Reserva|Estancia $origen,
        ?float $limiteSolicitado = null,
        ?TipoTitular $tipoTitular = null,
    ): CuentaEstancia {
        return DB::transaction(function () use ($origen, $limiteSolicitado, $tipoTitular): CuentaEstancia {
            $estancia = $origen instanceof Estancia ? $origen : $origen->estancia;
            $reserva = $origen instanceof Reserva ? $origen : $estancia?->reserva;

            $tipoTitular = $tipoTitular ?? TipoTitular::HABITACION;

            $cuentaExistente = $estancia !== null
                ? $estancia->cuenta
                : ($reserva !== null
                    ? $reserva->cuentas()->where('tipo_titular', $tipoTitular)->first()
                    : null);

            if ($cuentaExistente !== null) {
                if ($cuentaExistente->estado !== EstadoCuentaEstancia::SOLICITADA) {
                    throw new DomainException('Ya existe un folio en proceso o activo para este titular.');
                }

                $cuentaExistente->update([
                    'limite_autorizado' => $limiteSolicitado,
                ]);

                return $cuentaExistente->refresh();
            }

            if ($origen instanceof Reserva) {
                $origen->update([
                    'solicita_cuenta' => true,
                    'limite_cuenta_solicitado' => $limiteSolicitado,
                ]);
            }

            $reservaId = ($reserva !== null ? $reserva->id : null) ?? ($origen instanceof Reserva ? $origen->id : null);
            $folio = sprintf('CTA-%s-%06d', now()->format('Y'), $reservaId ?? $origen->id);

            $cuentaData = [
                'reserva_id' => $reservaId,
                'estancia_id' => $estancia?->id,
                'tipo_titular' => $tipoTitular,
                'numero_folio' => $folio,
                'estado' => EstadoCuentaEstancia::SOLICITADA,
                'limite_autorizado' => $limiteSolicitado,
            ];

            $cuenta = CuentaEstancia::query()->create($cuentaData);

            CuentaEstanciaSolicitada::dispatch($cuenta);

            return $cuenta;
        });
    }
}
