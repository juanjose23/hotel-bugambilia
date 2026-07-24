<?php

declare(strict_types=1);

namespace App\Interactors\CuentasEstancia;

use App\Enums\Estancias\EstadoCuentaEstancia;
use App\Enums\Estancias\TipoTitular;
use App\Events\Estancias\CuentaEstanciaAbierta;
use App\Repository\Models\Estancias\CuentaEstancia;
use App\Repository\Models\Reservas\Reserva;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class AbrirCuentaParaTitular
{
    public function ejecutar(
        Reserva $reserva,
        TipoTitular $tipoTitular,
        ?Model $cuentaable = null,
        ?float $limite = null,
        ?int $usuarioId = null,
    ): CuentaEstancia {
        return DB::transaction(function () use ($reserva, $tipoTitular, $cuentaable, $limite, $usuarioId): CuentaEstancia {
            $cuentaExistente = $reserva->cuentas()
                ->where('tipo_titular', $tipoTitular)
                ->when($cuentaable !== null, function ($q) use ($cuentaable): void {
                    /** @var Model $cuentaable */
                    $q->where('cuentaable_type', $cuentaable->getMorphClass())
                        ->where('cuentaable_id', $cuentaable->getKey());
                })
                ->first();

            if ($cuentaExistente !== null) {
                if ($cuentaExistente->estado !== EstadoCuentaEstancia::SOLICITADA) {
                    throw new DomainException('Ya existe una cuenta activa o en proceso para este titular.');
                }

                $cuentaExistente->update([
                    'estado' => EstadoCuentaEstancia::ABIERTA,
                    'limite_autorizado' => $limite ?? $cuentaExistente->limite_autorizado,
                    'abierta_at' => now(),
                    'abierta_por' => $usuarioId,
                ]);

                $cuenta = $cuentaExistente->refresh();
            } else {
                $estancia = $reserva->estancia;
                $numeroCuenta = sprintf('CUENTA-%s-%06d', now()->format('Y'), $reserva->id);

                $cuenta = CuentaEstancia::query()->create([
                    'reserva_id' => $reserva->id,
                    'estancia_id' => $estancia?->id,
                    'cuentaable_type' => $cuentaable?->getMorphClass(),
                    'cuentaable_id' => $cuentaable?->getKey(),
                    'tipo_titular' => $tipoTitular,
                    'numero_folio' => sprintf('CTA-%s-%06d', now()->format('Y'), $reserva->id),
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
