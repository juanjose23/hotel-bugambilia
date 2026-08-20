<?php

declare(strict_types=1);

namespace App\Actions\Limpieza\Lavanderia;

use App\Repository\Models\Limpieza\LavanderiaProceso;
use Illuminate\Support\Facades\DB;

final class FinalizarProcesosLavanderia
{
    public function execute(int $productoId, ?int $productoVarianteId, ?int $loteId, float $cantidad): void
    {
        if ($cantidad <= 0.0) {
            return;
        }

        DB::transaction(function () use ($productoId, $productoVarianteId, $loteId, $cantidad): void {
            $restante = $cantidad;

            $procesos = LavanderiaProceso::query()
                ->where('producto_id', $productoId)
                ->where('estado', 'en_proceso')
                ->when(
                    $productoVarianteId !== null,
                    fn ($query) => $query->where('producto_variante_id', $productoVarianteId),
                    fn ($query) => $query->whereNull('producto_variante_id')
                )
                ->when(
                    $loteId !== null,
                    fn ($query) => $query->where('lote_id', $loteId),
                    fn ($query) => $query->whereNull('lote_id')
                )
                ->oldest()
                ->lockForUpdate()
                ->get();

            foreach ($procesos as $proceso) {
                if ($restante <= 0.0) {
                    break;
                }

                $cantidadProceso = (float) $proceso->cantidad;

                if ($cantidadProceso <= $restante) {
                    $proceso->estado = 'finalizado';
                    $proceso->save();
                    $restante -= $cantidadProceso;

                    continue;
                }

                $proceso->cantidad = $cantidadProceso - $restante;
                $proceso->save();

                LavanderiaProceso::query()->create([
                    'producto_id' => $proceso->producto_id,
                    'producto_variante_id' => $proceso->producto_variante_id,
                    'lote_id' => $proceso->lote_id,
                    'cantidad' => $restante,
                    'estado' => 'finalizado',
                ]);

                $restante = 0.0;
            }
        });
    }
}
