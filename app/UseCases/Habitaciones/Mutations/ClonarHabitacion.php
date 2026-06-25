<?php

declare(strict_types=1);

namespace App\UseCases\Habitaciones\Mutations;

// app/UseCases/Habitaciones/Mutations/ClonarHabitacion.php

use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Models\Habitaciones\DetalleHabitacion;
use App\Models\Habitaciones\Habitacion;
use App\Models\Habitaciones\HabitacionStock;
use App\Models\Habitaciones\PrecioHabitacion;
use App\Models\Habitaciones\ServicioHabitacion;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Caso de Uso: Clonar una habitación existente hacia una nueva con número distinto.
 *
 * Qué SE clona (datos teóricos / plantillas):
 *   Atributos base: categoría, nombre base, descripción, ubicación, campo nombre
 *   DetalleHabitacion: capacidad_adultos, capacidad_ninos, medidas, vistas
 *   ServicioHabitacion: todos los servicios activos (incluido + estado)
 *   PrecioHabitacion: todos los precios vigentes como punto de partida
 *   Políticas: vínculo polimórfico (politicaables)
 *   HabitacionStock.cantidad_ideal: plantilla de consumibles (cantidad_actual = 0)
 *
 * Qué NO SE clona (datos físicos únicos):
 *   Activos fijos / ActivoAsignacion — la nueva habitación nace sin inventario fijo
 *   Imágenes — las imágenes son específicas de cada cuarto físico
 *   Historial / HabitacionHistorial
 *   codigo (se genera nuevo con GenerarCodigoHabitacion)
 *   slug (se genera nuevo con GenerarSlugHabitacion)
 *   numero (lo suministra el llamador — es único)
 *   estado (se fuerza a Mantenimiento — aún no está físicamente lista)
 *   HabitacionStock.cantidad_actual (nace en 0 — sin stock físico real)
 *   lote_id del stock (sin lote asignado hasta el abastecimiento real)
 */
class ClonarHabitacion
{
    public function __construct(
        private readonly GenerarCodigoHabitacion $generarCodigo,
        private readonly GenerarSlugHabitacion $generarSlug,
    ) {}

    /**
     * Clona la habitación origen creando una nueva con el número indicado.
     *
     * @param  Habitacion  $origen  Habitación que sirve como plantilla
     * @param  int  $numero  Número único de la nueva habitación (ej. 102)
     * @param  string|null  $nombre  Nombre descriptivo opcional; si es null, se construye automáticamente
     *
     * @throws InvalidArgumentException Si el número ya está en uso
     */
    public function execute(
        Habitacion $origen,
        int $numero,
        ?string $nombre = null,
    ): Habitacion {
        if ($numero < 1) {
            throw new InvalidArgumentException('El número de habitación debe ser mayor a cero.');
        }

        if (Habitacion::withTrashed()->where('numero', $numero)->exists()) {
            throw new InvalidArgumentException(
                "El número de habitación {$numero} ya está en uso (incluye habitaciones eliminadas)."
            );
        }

        return DB::transaction(function () use ($origen, $numero, $nombre): Habitacion {

            // ─── 1. Replicar atributos base ──────────────────────────────────
            $nueva = $origen->replicate([
                // Excluimos explícitamente campos únicos / físicos
                'codigo',
                'numero',
                'slug',
                'nombre',
                'estado',
            ]);

            $nueva->codigo = $this->generarCodigo->execute();
            $nueva->numero = $numero;
            $nueva->nombre = $nombre ?? "Habitación {$numero}";
            $nueva->slug = $this->generarSlug->execute($nueva->nombre);

            // La habitación clonada nace en Mantenimiento:
            // aún no tiene activos asignados ni stock real.
            $nueva->estado = EstadoHabitacion::Mantenimiento;

            $nueva->save();

            // ─── 2. Clonar DetalleHabitacion (capacidades, medidas, vistas) ─
            $origen->loadMissing('detalle');

            if ($origen->detalle !== null) {
                $detalleData = $origen->detalle->toArray();

                // Quitar campos que no corresponden a la nueva habitación
                unset($detalleData['id'], $detalleData['habitacion_id'], $detalleData['created_at'], $detalleData['updated_at']);

                DetalleHabitacion::create(array_merge($detalleData, [
                    'habitacion_id' => $nueva->id,
                ]));
            }

            // ─── 3. Clonar ServicioHabitacion (datos teóricos de servicios) ─
            $origen->loadMissing('serviciosHabitacion');

            foreach ($origen->serviciosHabitacion as $servicio) {
                ServicioHabitacion::create([
                    'habitacion_id' => $nueva->id,
                    'servicio_id' => $servicio->servicio_id,
                    'incluido' => $servicio->incluido,
                    'estado' => $servicio->estado,
                ]);
            }

            // ─── 4. Clonar PrecioHabitacion (tarifas vigentes como plantilla) ─
            $origen->loadMissing('precioshabitacion');

            foreach ($origen->precioshabitacion as $precio) {
                PrecioHabitacion::create([
                    'habitacion_id' => $nueva->id,
                    'moneda_id' => $precio->moneda_id,
                    'precio' => $precio->precio,
                    'fecha_inicio' => $precio->fecha_inicio,
                    'fecha_fin' => $precio->fecha_fin,
                    'estado' => $precio->estado,
                    'es_oferta' => $precio->es_oferta,
                ]);
            }

            // ─── 5. Clonar vínculos polimórficos de Políticas ───────────────
            $origen->loadMissing('politicas');

            if ($origen->politicas->isNotEmpty()) {
                $nueva->politicas()->syncWithoutDetaching(
                    $origen->politicas->pluck('id')->all()
                );
            }

            // ─── 6. Clonar plantilla de HabitacionStock (solo cantidad_ideal) ─
            // La cantidad_actual nace en 0 — la habitación está físicamente vacía
            // hasta que el personal ejecute el UseCase de abastecimiento (AsignarPackAHabitacion).
            $origen->loadMissing('habitacionStocks');

            foreach ($origen->habitacionStocks as $stock) {
                HabitacionStock::create([
                    'habitacion_id' => $nueva->id,
                    'producto_variante_id' => $stock->producto_variante_id,
                    'lote_id' => null,          // Sin lote hasta el abastecimiento real
                    'cantidad_ideal' => $stock->cantidad_ideal,
                    'cantidad_actual' => '0.0000',      // Físicamente vacía
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
