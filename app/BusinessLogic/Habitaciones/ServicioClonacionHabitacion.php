<?php

declare(strict_types=1);

namespace App\BusinessLogic\Habitaciones;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Interactors\Habitaciones\GenerarCodigoHabitacion;
use App\Interactors\Habitaciones\GenerarSlugHabitacion;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Shared\Stock as SharedStock;

class ServicioClonacionHabitacion
{
    public function __construct(
        private readonly GenerarSlugHabitacion $generarSlug,
        private readonly GenerarCodigoHabitacion $generarCodigo,
    ) {}

    public function clonar(
        Habitacion $origen,
        int $nuevoNumero,
        ?string $nuevoNombre = null,
        ?string $nuevoSlug = null,
        ?string $nuevoCodigo = null,
    ): Habitacion {
        $nombre = $nuevoNombre ?? (string) preg_replace(
            '/\d+/',
            (string) $nuevoNumero,
            $origen->nombre ?? '',
            1
        );

        $slug = $nuevoSlug ?? $this->generarSlug->ejecutar($nombre);
        $codigo = $nuevoCodigo ?? $this->generarCodigo->ejecutar();

        $nueva = Habitacion::create([
            'codigo' => $codigo,
            'numero' => $nuevoNumero,
            'slug' => $slug,
            'nombre' => $nombre,
            'descripcion' => $origen->descripcion,
            'categoria_id' => $origen->categoria_id,
            'ubicacion_id' => $origen->ubicacion_id,
            'estado' => EstadoEspacio::Mantenimiento,
        ]);

        if ($origen->detalle) {
            $nueva->detalle()->create($origen->detalle->replicate(['id', 'habitacion_id', 'created_at', 'updated_at'])->toArray());
        }

        foreach ($origen->servicioAsignaciones as $servicio) {
            $nueva->servicioAsignaciones()->create([
                'servicio_id' => $servicio->servicio_id,
                'incluido' => $servicio->incluido,
            ]);
        }

        foreach ($origen->precios as $precio) {
            $nueva->precios()->create($precio->replicate(['id', 'priceable_type', 'priceable_id', 'created_at', 'updated_at'])->toArray());
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

        return $nueva;
    }
}
