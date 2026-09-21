<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Support;

use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Restaurante\Plato;
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
            $entradas[] = [
                'tipo' => 'preorden',
                'datos' => [
                    'items' => $platos,
                    'total_items' => array_sum(array_column($platos, 'cantidad')),
                    'total_preorden' => round((float) array_sum(array_column($platos, 'subtotal')), 2),
                ],
            ];
        }

        if ($resumenRestaurante !== null && $resumenRestaurante !== []) {
            $entradas[] = [
                'tipo' => 'resumen_restaurante',
                'datos' => $resumenRestaurante,
            ];
        }

        return $entradas;
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array{tipo: string, datos: array<string, mixed>}
     */
    public function entradaPreorden(array $datos): array
    {
        $platos = $this->platosPreordenados($datos);

        return ['tipo' => 'preorden', 'datos' => ['items' => $platos]];
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array{tipo: string, datos: array<string, mixed>}
     */
    public function paraActualizacion(Reserva $reserva, array $datos): array
    {
        return $this->entradaPreorden($datos);
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<int, array{plato_id: int, nombre: string, cantidad: int, precio_unitario: float, subtotal: float, observaciones: string|null}>
     */
    private function platosPreordenados(array $datos): array
    {
        $rawPreorden = $this->leerDato->arreglo($datos, 'items_preorden');

        if ($rawPreorden === []) {
            return [];
        }

        /** @var list<int> $platoIds */
        $platoIds = [];
        foreach ($rawPreorden as $item) {
            if (is_array($item)) {
                $id = $this->leerDato->enteroOpcional($item, 'plato_id', 0);
                if ($id > 0) {
                    $platoIds[] = $id;
                }
            }
        }

        if ($platoIds === []) {
            return [];
        }

        $platos = $this->datosPedidoForm->platosParaPreorden($platoIds);

        $items = [];

        foreach ($rawPreorden as $item) {
            if (! is_array($item)) {
                continue;
            }

            $platoId = $this->leerDato->enteroOpcional($item, 'plato_id', 0);
            $cantidad = max(1, $this->leerDato->enteroOpcional($item, 'cantidad', 1));

            $plato = $platos->get($platoId);
            if (! $plato instanceof Plato) {
                continue;
            }

            $precio = is_numeric($item['precio_unitario'] ?? null)
                ? (float) $item['precio_unitario']
                : (is_numeric($item['precio'] ?? null)
                    ? (float) $item['precio']
                    : ($this->datosPedidoForm->precioActualDePlato($platoId) ?? (is_numeric($plato->precio_base ?? null) ? (float) $plato->precio_base : 0.0)));

            $observaciones = isset($item['observaciones']) && is_string($item['observaciones']) && trim($item['observaciones']) !== ''
                ? trim($item['observaciones'])
                : null;

            $items[] = [
                'plato_id' => $platoId,
                'nombre' => $plato->nombre,
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
                'subtotal' => round($precio * $cantidad, 2),
                'observaciones' => $observaciones,
            ];
        }

        return $items;
    }
}
