<?php

declare(strict_types=1);

namespace App\UseCases\Espacios\Mutations;

use App\Models\Espacios\Espacio;
use App\Models\Espacios\PrecioEspacio;
use Illuminate\Support\Facades\DB;

/**
 * Caso de Uso: Asignar u obtener un precio para un espacio físico.
 * Controla la vigencia y evita la duplicidad de precios activos no-oferta.
 */
class AsignarPrecioAEspacio
{
    /**
     * Ejecuta la asignación de precio a un espacio.
     *
     * @param  int  $estado  (1=Vigente, 2=No Vigente)
     * @param  string  $tipoPrecio  ('base' o 'por_hora')
     */
    public function execute(
        int $espacioId,
        int $monedaId,
        float $precio,
        string $fechaInicio,
        ?string $fechaFin = null,
        int $estado = 1,
        bool $esOferta = false,
        string $tipoPrecio = 'base'
    ): PrecioEspacio {
        Espacio::findOrFail($espacioId);

        if ($precio < 0) {
            throw new \InvalidArgumentException('El precio no puede ser negativo.');
        }

        return DB::transaction(function () use (
            $espacioId,
            $monedaId,
            $precio,
            $fechaInicio,
            $fechaFin,
            $estado,
            $esOferta,
            $tipoPrecio
        ) {
            // Si el nuevo precio es vigente y no es oferta, desactivamos los otros vigentes no-oferta del mismo tipo y moneda
            if ($estado === 1 && ! $esOferta) {
                PrecioEspacio::where('espacio_id', $espacioId)
                    ->where('moneda_id', $monedaId)
                    ->where('tipo_precio', $tipoPrecio)
                    ->where('estado', 1)
                    ->where('es_oferta', false)
                    ->update([
                        'estado' => 2,
                        'fecha_fin' => now()->subDay()->toDateString(),
                    ]);
            }

            return PrecioEspacio::create([
                'espacio_id' => $espacioId,
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
