<?php

declare(strict_types=1);

namespace App\UseCases\Habitaciones\Mutations;

use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Models\Habitaciones\Habitacion;
use App\Models\Shared\Stock as SharedStock;
use Illuminate\Support\Facades\DB;

class ClonarHabitacion
{
    public function execute(
        Habitacion $origen,
        int $nuevoNumero,
        ?string $nuevoNombre = null,
        ?string $nuevoSlug = null,
        ?string $nuevoCodigo = null,
    ): Habitacion {
        if ($nuevoNumero < 1) {
            throw new \InvalidArgumentException('El número de habitación debe ser mayor a cero.');
        }

        $numeroExiste = Habitacion::withTrashed()
            ->where('numero', $nuevoNumero)
            ->where('id', '!=', $origen->id)
            ->exists();

        if ($numeroExiste) {
            throw new \InvalidArgumentException("El número {$nuevoNumero} ya está en uso.");
        }

        return DB::transaction(function () use ($origen, $nuevoNumero, $nuevoNombre, $nuevoSlug, $nuevoCodigo) {
            $nombre = $nuevoNombre ?? preg_replace(
                '/\d+/',
                (string) $nuevoNumero,
                $origen->nombre,
                1
            );

            $slug = $nuevoSlug ?? app(GenerarSlugHabitacion::class)->execute($nombre);
            $codigo = $nuevoCodigo ?? app(GenerarCodigoHabitacion::class)->execute();

            $nueva = Habitacion::create([
                'codigo' => $codigo,
                'numero' => $nuevoNumero,
                'slug' => $slug,
                'nombre' => $nombre,
                'descripcion' => $origen->descripcion,
                'categoria_id' => $origen->categoria_id,
                'ubicacion_id' => $origen->ubicacion_id,
                'estado' => EstadoHabitacion::Mantenimiento,
            ]);

            if ($origen->detalle) {
                $nueva->detalle()->create($origen->detalle->replicate(['id', 'habitacion_id', 'created_at', 'updated_at'])->toArray());
            }

            foreach ($origen->serviciosHabitacion as $servicio) {
                $nueva->serviciosHabitacion()->create([
                    'servicio_id' => $servicio->servicio_id,
                    'incluido' => $servicio->incluido,
                ]);
            }

            foreach ($origen->precioshabitacion as $precio) {
                $nueva->precioshabitacion()->create($precio->replicate(['id', 'priceable_type', 'priceable_id', 'created_at', 'updated_at'])->toArray());
            }

            if ($origen->politicas->isNotEmpty()) {
                $nueva->politicas()->sync(
                    $origen->politicas->pluck('id')->all()
                );
            }

            $origen->loadMissing('stocks');

            foreach ($origen->stocks as $stock) {
                SharedStock::create([
                    'stockable_type' => Habitacion::class,
                    'stockable_id' => $nueva->id,
                    'producto_variante_id' => $stock->producto_variante_id,
                    'lote_id' => null,
                    'cantidad_ideal' => $stock->cantidad_ideal,
                    'cantidad_actual' => '0.0000',
                ]);
            }

            return $nueva->fresh([
                'detalle',
                'categoria',
                'ubicacion',
                'serviciosHabitacion',
                'precioshabitacion',
                'politicas',
                'habitacionStocks',
            ]) ?? $nueva;
        });
    }
}
