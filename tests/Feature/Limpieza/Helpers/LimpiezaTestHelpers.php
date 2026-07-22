<?php

declare(strict_types=1);

namespace Tests\Feature\Limpieza\Helpers;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Inventario\Stock as InventarioStock;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Models\Limpieza\Turno;

trait LimpiezaTestHelpers
{
    protected function crearHabitacionLimpieza(int $numero = 401, ?int $categoriaId = null, ?int $ubicacionId = null, EstadoEspacio $estado = EstadoEspacio::Activa): Habitacion
    {
        return Habitacion::create([
            'codigo' => 'HAB-'.str_pad((string) $numero, 4, '0', STR_PAD_LEFT),
            'numero' => $numero,
            'slug' => "habitacion-{$numero}",
            'nombre' => "Habitación {$numero}",
            'categoria_id' => $categoriaId,
            'ubicacion_id' => $ubicacionId,
            'estado' => $estado,
        ]);
    }

    protected function crearEspacioLimpieza(int $ubicacionId): Espacio
    {
        return Espacio::create([
            'codigo' => 'MESA-LIMP-001',
            'nombre' => 'Mesa Limpieza',
            'tipo' => TipoEspacio::MESA,
            'capacidad_personas' => 4,
            'ubicacion_id' => $ubicacionId,
            'estado' => EstadoEspacio::Disponible,
        ]);
    }

    protected function crearSolicitudLimpieza(Habitacion|Espacio $limpiable, EstadoLimpieza $estado = EstadoLimpieza::Pendiente): SolicitudLimpieza
    {
        return SolicitudLimpieza::create([
            'limpiable_type' => $limpiable::class,
            'limpiable_id' => $limpiable->id,
            'prioridad' => 'normal',
            'estado' => $estado,
        ]);
    }

    protected function crearStockEnBodega(int $varianteId, int $bodegaId, float $cantidad = 50.0): void
    {
        $variant = ProductoVariante::findOrFail($varianteId);
        Producto::findOrFail($variant->producto_id);

        InventarioStock::create([
            'producto_id' => $variant->producto_id,
            'ubicacion_id' => $bodegaId,
            'producto_variante_id' => $varianteId,
            'cantidad' => $cantidad,
        ]);
    }

    protected function crearTurno(): Turno
    {
        return Turno::create([
            'nombre' => 'Turno Test',
            'lider_id' => Colaborador::factory()->create()->id,
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
        ]);
    }

    protected function crearEjecucion(Habitacion|Espacio $limpiable, Turno $turno, EstadoLimpieza $estado = EstadoLimpieza::Pendiente): LimpiezaEjecucion
    {
        return LimpiezaEjecucion::create([
            'limpiable_type' => $limpiable::class,
            'limpiable_id' => $limpiable->id,
            'turno_id' => $turno->id,
            'fecha' => now()->toDateString(),
            'estado' => $estado,
        ]);
    }
}
