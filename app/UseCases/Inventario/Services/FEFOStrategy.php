<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Services;

use App\Models\Inventario\Lote;
use Illuminate\Database\Eloquent\Collection;

class FEFOStrategy
{
    /**
     * @param  Collection<int, Lote>  $lotes
     * @return array<int, array{lote: Lote, cantidad: float}>
     */
    public function seleccionarLotes(Collection $lotes, float $cantidadRequerida): array
    {
        $ordenados = $lotes->sortBy(function ($lote) {
            return $lote->fecha_vencimiento ?? '9999-12-31';
        })->values();

        $seleccion = [];
        $restante = $cantidadRequerida;

        foreach ($ordenados as $lote) {
            if ($restante <= 0.0) {
                break;
            }

            $aTomar = min($lote->cantidad_disponible, $restante);
            $seleccion[] = [
                'lote' => $lote,
                'cantidad' => $aTomar,
            ];
            $restante -= $aTomar;
        }

        return $seleccion;
    }
}
