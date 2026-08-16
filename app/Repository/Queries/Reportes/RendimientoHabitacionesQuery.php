<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reportes;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Shared\Precio;
use Illuminate\Database\Eloquent\Builder;

final class RendimientoHabitacionesQuery
{
    /**
     * @return array<int, array{
     *   nombre: string,
     *   capacidad_total: int,
     *   habitaciones_activas: int,
     *   precio_base_noche: float,
     * }>
     */
    public function categorias(): array
    {
        return Catalogo::query()
            ->whereHas('catalogoTipo', fn (Builder $q) => $q->where('codigo', CatalogoTipo::CATEGORIA_HABITACION->value))
            ->with(['habitaciones.detalle', 'habitaciones.precios.moneda'])
            ->orderBy('nombre')
            ->get()
            ->map(fn (Catalogo $categoria): array => [
                'nombre' => $categoria->nombre,
                'capacidad_total' => $categoria->habitaciones->sum(
                    fn (Habitacion $habitacion) => (int) ($habitacion->detalle?->capacidad_adultos) + (int) ($habitacion->detalle?->capacidad_ninos),
                ),
                'habitaciones_activas' => $categoria->habitaciones->count(),
                'precio_base_noche' => $this->precioBaseUsd($categoria),
            ])
            ->values()
            ->all();
    }

    private function precioBaseUsd(Catalogo $categoria): float
    {
        $precioMinimo = $categoria->habitaciones
            ->flatMap(fn (Habitacion $habitacion) => $habitacion->precios)
            ->filter(fn (Precio $precio) => $precio->moneda?->codigo === 'USD' && $precio->estado === EstadoGeneral::Activo)
            ->min('precio');

        return is_numeric($precioMinimo) ? (float) $precioMinimo : 0.0;
    }
}
