<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas;

use App\Repository\Models\Reservas\Reserva;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerDatosPedidoFormQuery;

final readonly class ConstruirBitacoraReserva
{
    public function __construct(
        private LeerDatoReserva $leerDato,
        private ObtenerDatosPedidoFormQuery $datosPedidoForm,
    ) {}

    /**
     * Construye las entradas iniciales de bitácora para una reserva nueva.
     *
     * @param  array<string, mixed>  $datos
     * @param  array<string, mixed>|null  $resumenRestaurante
     * @return list<array{tipo: string, datos: array<string, mixed>}>
     */
    public function paraCreacion(array $datos, ?array $resumenRestaurante): array
    {
        $entradas = [];

        $platos = $this->platosPreordenados($datos);

        if ($platos !== []) {
            $entradas[] = ['tipo' => 'preorden', 'datos' => ['items' => $platos]];
        }

        if ($resumenRestaurante !== null) {
            $entradas[] = ['tipo' => 'resumen_restaurante', 'datos' => $resumenRestaurante];
        }

        return $entradas;
    }

    /**
     * Construye la entrada actualizada de preorden para una reserva existente.
     *
     * @param  array<string, mixed>  $datos
     * @return array{tipo: string, datos: array<string, mixed>}
     */
    public function paraActualizacion(Reserva $reserva, array $datos): array
    {
        $platos = $this->platosPreordenados($datos);

        return ['tipo' => 'preorden', 'datos' => ['items' => $platos]];
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<int, array{plato_id: int, nombre: string, cantidad: int, precio_unitario: float, subtotal: float, observaciones: string|null}>
     */
    private function platosPreordenados(array $datos): array
    {
        $rawPreorden = $this->leerDato->arreglo($datos, 'items_preorden');
        $platoIds = array_values(array_unique(array_filter(
            array_map(
                static fn (mixed $item): int => (is_array($item) && is_numeric($item['plato_id'] ?? null)) ? (int) $item['plato_id'] : 0,
                $rawPreorden,
            ),
            static fn (int $id): bool => $id > 0,
        )));
        $platosPorId = $this->datosPedidoForm->platosParaPreorden($platoIds);
        $platosPreordenados = [];

        foreach ($rawPreorden as $item) {
            if (! is_array($item) || ! is_numeric($item['plato_id'] ?? null)) {
                continue;
            }

            $platoId = (int) $item['plato_id'];
            $precioValor = $item['precio_unitario'] ?? null;
            $precio = is_numeric($precioValor) && (float) $precioValor > 0
                ? (float) $precioValor
                : ($this->datosPedidoForm->precioActualDePlato($platoId) ?? 0.0);
            $cantidad = max(1, is_numeric($item['cantidad'] ?? null) ? (int) $item['cantidad'] : 1);
            $plato = $platosPorId->get($platoId);
            $nombrePlato = $plato !== null ? $plato->nombre : "Platillo #{$platoId}";

            $platosPreordenados[] = [
                'plato_id' => $platoId,
                'nombre' => $nombrePlato,
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
                'subtotal' => round($precio * $cantidad, 2),
                'observaciones' => is_string($item['observaciones'] ?? null) ? trim($item['observaciones']) : null,
            ];
        }

        return $platosPreordenados;
    }
}
