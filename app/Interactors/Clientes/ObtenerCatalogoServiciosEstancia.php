<?php

declare(strict_types=1);

namespace App\Interactors\Clientes;

use App\Repository\Models\Servicios\Servicio;

final class ObtenerCatalogoServiciosEstancia
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function ejecutar(): array
    {
        $servicios = Servicio::with(['categoria', 'precios.moneda', 'imagenes'])
            ->activos()
            ->get();

        return $servicios->map(function (Servicio $s): array {
            $precioObj = $s->precios->first();
            $precio = $precioObj !== null ? (float) $precioObj->precio : 0.0;
            $monedaSimbolo = $precioObj !== null && $precioObj->moneda !== null ? (string) $precioObj->moneda->simbolo : '$';
            $imagen = $s->imagenes->first() !== null ? (string) $s->imagenes->first()->url : null;

            return [
                'id' => (int) $s->id,
                'nombre' => (string) $s->nombre,
                'codigo' => (string) $s->codigo,
                'descripcion' => $s->descripcion,
                'categoria' => $s->categoria !== null ? (string) $s->categoria->nombre : 'General',
                'categoria_id' => $s->categoria_id,
                'precio' => $precio,
                'moneda_simbolo' => $monedaSimbolo,
                'imagen' => $imagen,
                'requiere_reserva' => (bool) $s->reservable_id,
            ];
        })->all();
    }
}
