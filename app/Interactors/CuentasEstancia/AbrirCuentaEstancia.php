<?php

declare(strict_types=1);

namespace App\Interactors\CuentasEstancia;

use App\Enums\Estancias\EstadoCuentaEstancia;
use App\Enums\Estancias\TipoTitular;
use App\Events\Estancias\CuentaEstanciaAbierta;
use App\Repository\Models\Estancias\CuentaEstancia;
use App\Repository\Models\Estancias\Estancia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class AbrirCuentaEstancia
{
    public function ejecutar(
        Estancia $estancia,
        ?int $usuarioId = null,
        ?float $limite = null,
        ?TipoTitular $tipoTitular = null,
        ?Model $cuentaable = null,
    ): CuentaEstancia {
        return DB::transaction(function () use ($estancia, $usuarioId, $limite, $tipoTitular, $cuentaable): CuentaEstancia {
            $tipoTitular = $tipoTitular ?? TipoTitular::HABITACION;

            $cuentaExistente = $estancia->cuenta
                ?? CuentaEstancia::query()
                    ->where('reserva_id', $estancia->reserva_id)
                    ->where('tipo_titular', $tipoTitular)
                    ->first();

            if ($cuentaExistente !== null) {
                $cuentaExistente->update([
                    'estancia_id' => $estancia->id,
                    'estado' => EstadoCuentaEstancia::ABIERTA,
                    'limite_autorizado' => $limite ?? $cuentaExistente->limite_autorizado,
                    'abierta_at' => now(),
                    'abierta_por' => $usuarioId,
                ]);

                $cuenta = $cuentaExistente->refresh();
            } else {
                $numeroCuenta = sprintf('CUENTA-%s-%06d', now()->format('Y'), $estancia->id);

                $cuenta = CuentaEstancia::query()->create([
                    'estancia_id' => $estancia->id,
                    'reserva_id' => $estancia->reserva_id,
                    'cuentaable_type' => $cuentaable?->getMorphClass(),
                    'cuentaable_id' => $cuentaable?->getKey(),
                    'tipo_titular' => $tipoTitular,
                    'numero_folio' => sprintf('CTA-%s-%06d', now()->format('Y'), $estancia->id),
                    'numero_cuenta' => $numeroCuenta,
                    'estado' => EstadoCuentaEstancia::ABIERTA,
                    'limite_autorizado' => $limite,
                    'abierta_at' => now(),
                    'abierta_por' => $usuarioId,
                ]);
            }

            CuentaEstanciaAbierta::dispatch($cuenta);

            return $cuenta;
        });
    }
}
