<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Repository\Persistencia\Restaurante\ExistenciasCocinaRepositorio;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class TrasladarExistenciasCocina
{
    public function __construct(private ExistenciasCocinaRepositorio $existencias) {}

    /** @param array<string, mixed> $contexto */
    public function ejecutar(int $varianteId, int $origenId, int $destinoId, float $cantidad, array $contexto = []): void
    {
        if ($origenId === $destinoId || $cantidad <= 0 || ! is_finite($cantidad)) {
            throw new DomainException('Indique ubicaciones distintas y cantidad positiva.');
        }
        DB::transaction(function () use ($varianteId, $origenId, $destinoId, $cantidad, $contexto): void {
            $this->existencias->bloquearUbicaciones([$origenId, $destinoId]);
            $stocks = $this->existencias->porVariante($varianteId, $origenId, true);
            if ($this->existencias->cantidad($stocks) + 0.00001 < $cantidad) {
                throw new DomainException('Stock insuficiente en bodega para el traslado a cocina.');
            }
            $restante = $cantidad;
            foreach ($stocks as $stock) {
                if ($restante <= 0.00001) {
                    break;
                }
                $asignado = min($restante, (float) $stock->cantidad_actual);
                $stock->cantidad_actual = round((float) $stock->cantidad_actual - $asignado, 4);
                $this->existencias->guardar($stock);
                $this->existencias->recibir($stock, $destinoId, $asignado);
                $costo = (float) ($stock->lote->costo_unitario ?? 0);
                $this->existencias->movimiento(array_merge($contexto, ['tipo' => 'TRASLADO', 'producto_id' => $stock->producto_id ?? $stock->variante?->producto_id, 'lote_id' => $stock->lote_id, 'cantidad' => $asignado, 'costo_unitario' => $costo, 'costo_total' => round($asignado * $costo, 6), 'ubicacion_origen_id' => $origenId, 'ubicacion_destino_id' => $destinoId]));
                $restante = round($restante - $asignado, 4);
            }
        });
    }
}
