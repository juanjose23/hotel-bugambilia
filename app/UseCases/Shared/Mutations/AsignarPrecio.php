<?php

declare(strict_types=1);

namespace App\UseCases\Shared\Mutations;

use App\Models\Shared\Precio;
use Illuminate\Support\Facades\DB;

class AsignarPrecio
{
    public function execute(
        string $priceableType,
        int $priceableId,
        int $monedaId,
        float $precio,
        string $fechaInicio,
        ?string $fechaFin = null,
        int $estado = 1,
        bool $esOferta = false,
        string $tipoPrecio = 'base',
    ): Precio {
        $priceableType::findOrFail($priceableId);

        if ($precio < 0) {
            throw new \InvalidArgumentException('El precio no puede ser negativo.');
        }

        return DB::transaction(function () use (
            $priceableType, $priceableId, $monedaId, $precio,
            $fechaInicio, $fechaFin, $estado, $esOferta, $tipoPrecio,
        ) {
            if ($estado === 1 && ! $esOferta) {
                Precio::where('priceable_type', $priceableType)
                    ->where('priceable_id', $priceableId)
                    ->where('moneda_id', $monedaId)
                    ->where('tipo_precio', $tipoPrecio)
                    ->where('estado', 1)
                    ->where('es_oferta', false)
                    ->update([
                        'estado' => 2,
                        'fecha_fin' => now()->subDay()->toDateString(),
                    ]);
            }

            return Precio::create([
                'priceable_type' => $priceableType,
                'priceable_id' => $priceableId,
                'moneda_id' => $monedaId,
                'precio' => $precio,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'estado' => $estado,
                'es_oferta' => $esOferta,
                'tipo_precio' => $tipoPrecio,
            ]);
        });
    }
}
