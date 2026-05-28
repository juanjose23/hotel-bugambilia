<?php

declare(strict_types=1);

namespace App\UseCases\Compras\Solicitudes\Mutations;

use App\Enums\Compras\EstadoSolicitud;
use App\Models\Compras\Solicitud;
use Illuminate\Support\Facades\DB;

class CancelarSolicitud
{
    /**
     * Cancela una solicitud, actualizando las cantidades aprobadas de sus items y registrando notas de compras.
     *
     * @param  array<array-key, mixed>  $itemsCancelacion
     */
    public function execute(
        Solicitud $solicitud,
        array $itemsCancelacion = [],
        string $notaCompras = 'Cancelado desde listado de solicitudes'
    ): void {
        DB::transaction(function () use ($solicitud, $itemsCancelacion, $notaCompras) {
            $items = $solicitud->items()->get();

            foreach ($itemsCancelacion as $i => $itemData) {
                if (isset($items[$i])) {
                    $items[$i]->update([
                        'cantidad_aprobada' => $itemData['cantidad_aprobada'],
                    ]);
                }
            }

            $nota = '['.now()->format('d/m/Y H:i').'] CANCELADO: '.$notaCompras;
            $notas = $solicitud->notas ? $solicitud->notas."\n\n".$nota : $nota;

            $solicitud->update([
                'notas' => $notas,
                'estado' => EstadoSolicitud::Cancelada,
            ]);
        });
    }
}
