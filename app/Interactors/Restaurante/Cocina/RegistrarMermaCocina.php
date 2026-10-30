<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Repository\Persistencia\Restaurante\ExistenciasCocinaRepositorio;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class RegistrarMermaCocina
{
    public function __construct(private ExistenciasCocinaRepositorio $existencias, private ConsumirExistenciasCocina $consumir) {}

    /** @param array<int, array{producto_id: int, cantidad: float, motivo?: string|null}> $items
     * @return array{total_items: int, total_perdida: float, detalle: list<array{producto_id: int, cantidad: float, costo_total: float, motivo: string}>}
     */
    public function ejecutar(string $fecha, int $ubicacionId, array $items, ?int $usuarioId = null): array
    {
        return DB::transaction(function () use ($fecha, $ubicacionId, $items, $usuarioId): array {
            $detalle = [];
            $total = 0.0;
            if ($items === []) {
                throw new DomainException('Debe registrar al menos una pérdida.');
            }
            foreach ($items as $item) {
                $motivo = trim($item['motivo'] ?? '');
                if ($motivo === '') {
                    throw new DomainException('Indique el motivo de la pérdida.');
                }
                $consumos = $this->consumir->ejecutar($this->existencias->productoEnUbicacion($item['producto_id'], $ubicacionId, true, true), $item['cantidad'], ['tipo' => 'MERMA_COCINA', 'documento_tipo' => 'merma_cocina', 'referencia' => 'Merma '.$fecha.': '.$motivo, 'creado_por_id' => $usuarioId], true);
                $costo = round(array_sum(array_column($consumos, 'costo_total')), 2);
                $total += $costo;
                $detalle[] = ['producto_id' => $item['producto_id'], 'cantidad' => $item['cantidad'], 'costo_total' => $costo, 'motivo' => $motivo];
            }

            return ['total_items' => count($detalle), 'total_perdida' => round($total, 2), 'detalle' => $detalle];
        });
    }
}
