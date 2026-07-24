<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas;

use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Servicios\Servicio;

final class ObtenerOpcionesReservaPublicaQuery
{
    /** @return array{servicios: array<int, array<string, mixed>>, espacios: array<int, array<string, mixed>>, promociones: array<int, array<string, mixed>>} */
    public function obtener(?int $espacioPrincipalId = null): array
    {
        return [
            'servicios' => Servicio::query()
                ->activos()->where('web', true)->with('precios.moneda')->orderBy('nombre')->get()
                ->map(fn (Servicio $servicio): array => $this->opcion($servicio))->values()->all(),
            'espacios' => Espacio::query()
                ->activosWeb()->where('reservable', true)->when($espacioPrincipalId, fn ($query) => $query->whereKeyNot($espacioPrincipalId))
                ->with('precios.moneda')->orderBy('nombre')->get()
                ->map(fn (Espacio $espacio): array => $this->opcion($espacio))->values()->all(),
            'promociones' => Promocion::query()
                ->vigentes()->where('web', true)->orderBy('orden')->get()
                ->map(fn (Promocion $promocion): array => [
                    'id' => $promocion->id,
                    'codigo' => $promocion->codigo,
                    'nombre' => $promocion->nombre,
                    'descripcion' => $promocion->descripcion,
                    'descuento' => $promocion->descuento_porcentaje !== null
                        ? $promocion->descuento_porcentaje.'% de descuento'
                        : ($promocion->descuento_monto !== null ? 'Descuento de '.number_format((float) $promocion->descuento_monto, 2) : 'Promoción especial'),
                ])->values()->all(),
        ];
    }

    /** @return array{id: int, nombre: string, descripcion: string|null, precio: float, moneda: string} */
    private function opcion(Servicio|Espacio $modelo): array
    {
        $precio = $modelo->precios->first();

        return [
            'id' => $modelo->id,
            'nombre' => $modelo->nombre,
            'descripcion' => $modelo->descripcion,
            'precio' => $precio !== null ? (float) $precio->precio : 0.0,
            'moneda' => ($precio !== null && $precio->moneda !== null) ? (string) $precio->moneda->simbolo : 'C$',
        ];
    }
}
