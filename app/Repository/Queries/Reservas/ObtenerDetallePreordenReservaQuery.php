<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas;

use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Restaurante\Plato;

final class ObtenerDetallePreordenReservaQuery
{
    /**
     * @return array<int, array{nombre: string, cantidad: int, precio_unitario: float, subtotal: float, observaciones: string|null}>
     */
    public function ejecutar(Reserva $reserva): array
    {
        $datos = $reserva->ultimaEntradaBitacora('preorden');
        $items = is_array($datos['items'] ?? null) ? $datos['items'] : [];
        $platoIds = collect($items)
            ->filter(fn (mixed $item): bool => is_array($item) && is_numeric($item['plato_id'] ?? null))
            ->map(fn (array $item): int => (int) $item['plato_id'])
            ->unique()
            ->values()
            ->all();
        $platos = Plato::query()->whereKey($platoIds)->pluck('nombre', 'id');
        $detalle = [];

        foreach ($items as $item) {
            if (! is_array($item) || ! is_numeric($item['plato_id'] ?? null)) {
                continue;
            }

            $platoId = (int) $item['plato_id'];
            $cantidad = max(1, is_numeric($item['cantidad'] ?? null) ? (int) $item['cantidad'] : 1);
            $precio = is_numeric($item['precio_unitario'] ?? null)
                ? (float) $item['precio_unitario']
                : (is_numeric($item['precio'] ?? null) ? (float) $item['precio'] : 0.0);
            $nombre = $platos->get($platoId);
            $observaciones = is_string($item['observaciones'] ?? null) && trim($item['observaciones']) !== ''
                ? trim($item['observaciones'])
                : null;

            $detalle[] = [
                'nombre' => is_string($nombre) ? $nombre : "Platillo #{$platoId}",
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
                'subtotal' => round($precio * $cantidad, 2),
                'observaciones' => $observaciones,
            ];
        }

        return $detalle;
    }
}
