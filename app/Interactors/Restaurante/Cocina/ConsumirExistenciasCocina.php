<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Shared\Stock;
use App\Repository\Persistencia\Restaurante\ExistenciasCocinaRepositorio;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class ConsumirExistenciasCocina
{
    public function __construct(private ExistenciasCocinaRepositorio $existencias) {}

    /** @param Collection<int, Stock> $stocks
     * @param  array<string, mixed>  $contexto
     * @return list<array{stock_id: int, lote_id: int|null, cantidad: float, costo_total: float, vencimiento: string|null}>
     */
    public function ejecutar(Collection $stocks, float $cantidad, array $contexto = [], bool $permitirNoDisponibles = false): array
    {
        if ($cantidad < 0.0001 || ! is_finite($cantidad)) {
            throw new DomainException('La cantidad a consumir debe ser mayor que cero.');
        }

        return DB::transaction(function () use ($stocks, $cantidad, $contexto, $permitirNoDisponibles): array {
            $stocks = $this->existencias->bloquearSeleccion($stocks);
            if ($this->existencias->cantidad($stocks) + 0.00001 < $cantidad) {
                throw new DomainException('Stock insuficiente para completar la operación de cocina.');
            }
            $restante = $cantidad;
            $detalles = [];
            foreach ($stocks as $stock) {
                if ($restante <= 0.00001) {
                    break;
                }
                $lote = $this->existencias->loteBloqueado($stock->lote_id);
                if ($stock->lote_id !== null && $lote === null) {
                    throw new DomainException('No se encuentra el lote del insumo.');
                }
                if ($lote !== null && ! $permitirNoDisponibles && ($lote->estado !== EstadoLote::Disponible || ($lote->fecha_vencimiento !== null && $lote->fecha_vencimiento->toDateString() < now()->toDateString()))) {
                    throw new DomainException('El lote está vencido o no está disponible.');
                }
                $consumido = min($restante, (float) $stock->cantidad_actual);
                if ($lote !== null && (float) $lote->cantidad_disponible + 0.00001 < $consumido) {
                    throw new DomainException('El saldo del lote no coincide con sus existencias; concilie el inventario antes de continuar.');
                }
                $stock->cantidad_actual = round((float) $stock->cantidad_actual - $consumido, 4);
                $stock->ultima_verificacion = now();
                $this->existencias->guardar($stock);
                if ($lote !== null) {
                    $lote->cantidad_disponible = round((float) $lote->cantidad_disponible - $consumido, 4);
                    if ($lote->cantidad_disponible <= 0) {
                        $lote->estado = EstadoLote::Agotado;
                    }
                    $this->existencias->guardarLote($lote);
                }
                $costo = round($consumido * (float) ($lote->costo_unitario ?? 0), 6);
                $this->existencias->movimiento(array_merge($contexto, [
                    'tipo' => $contexto['tipo'] ?? 'CONSUMO', 'producto_id' => $stock->producto_id ?? $stock->variante?->producto_id,
                    'lote_id' => $stock->lote_id, 'cantidad' => -$consumido, 'costo_unitario' => $lote?->costo_unitario,
                    'costo_total' => $costo, 'ubicacion_origen_id' => $stock->stockable_type === Ubicacion::class ? $stock->stockable_id : null,
                ]));
                $detalles[] = ['stock_id' => $stock->id, 'lote_id' => $stock->lote_id, 'cantidad' => $consumido, 'costo_total' => $costo, 'vencimiento' => $lote?->fecha_vencimiento?->toDateString()];
                $restante = round($restante - $consumido, 4);
            }

            return $detalles;
        });
    }
}
