<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Compras;

use App\Enums\Compras\EstadoSolicitud;
use App\Events\Compras\SolicitudCreada;
use App\Repository\Models\Compras\Solicitud;
use Illuminate\Support\Facades\DB;

final class SolicitudRepositorio implements SolicitudRepositorioInterface
{
    /**
     * @param  array<string, mixed>  $datos
     */
    public function crear(array $datos): Solicitud
    {
        $solicitud = Solicitud::create($datos);

        return $solicitud;
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<int, array<string, mixed>>  $items
     */
    public function crearConItems(array $datos, array $items): Solicitud
    {
        $solicitud = $this->crear($datos);

        if (! empty($items)) {
            $solicitud->items()->createMany($items);
        }

        SolicitudCreada::dispatch($solicitud);

        return $solicitud;
    }

    public function actualizarEstado(Solicitud $solicitud, EstadoSolicitud $estado): void
    {
        $solicitud->update(['estado' => $estado]);
    }

    /**
     * @param  array<int, array{id: int, cantidad_aprobada: float|int|string}>  $items
     */
    public function actualizarCantidadesAprobadas(Solicitud $solicitud, array $items): void
    {
        foreach ($items as $itemData) {
            $solicitud->items()
                ->where('id', $itemData['id'])
                ->update(['cantidad_aprobada' => (float) $itemData['cantidad_aprobada']]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $itemsCancelacion
     */
    public function cancelar(Solicitud $solicitud, array $itemsCancelacion, string $nota): void
    {
        DB::transaction(function () use ($solicitud, $itemsCancelacion, $nota): void {
            $items = $solicitud->items()->get();

            foreach ($itemsCancelacion as $i => $itemData) {
                if (isset($items[$i])) {
                    $cantAprobada = is_numeric($itemData['cantidad_aprobada'] ?? null)
                        ? (int) $itemData['cantidad_aprobada']
                        : 0;

                    $items[$i]->update(['cantidad_aprobada' => $cantAprobada]);
                }
            }

            $notaFormateada = '['.now()->format('d/m/Y H:i').'] CANCELADO: '.$nota;
            $notas = $solicitud->notas
                ? $solicitud->notas."\n\n".$notaFormateada
                : $notaFormateada;

            $solicitud->update([
                'notas' => $notas,
                'estado' => EstadoSolicitud::Cancelada,
            ]);
        });
    }
}
