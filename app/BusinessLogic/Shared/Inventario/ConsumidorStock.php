<?php

declare(strict_types=1);

namespace App\BusinessLogic\Shared\Inventario;

use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Shared\Stock;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ConsumidorStock
{
    public function __construct(
        private readonly Stock $stockModel,
        private readonly MovimientoStock $movimientoModel,
    ) {}

    public function consumir(
        int $stockId,
        float $cantidad,
        string $motivo = 'consumo',
        ?int $creadoPorId = null,
        ?string $referencia = null,
    ): void {
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad a consumir debe ser mayor a cero.');
        }

        DB::transaction(function () use ($stockId, $cantidad, $motivo, $creadoPorId, $referencia) {
            $stock = $this->loadStockWithLock($stockId);
            $variante = $this->loadVariante($stock);
            $entidad = $this->loadEntidad($stock);
            $producto = $this->loadProducto($variante);

            if ((float) $stock->cantidad_actual < $cantidad) {
                throw new \RuntimeException(sprintf(
                    'Stock insuficiente. Actual: %s, Requerido: %s',
                    $stock->cantidad_actual,
                    $cantidad
                ));
            }

            $stock->cantidad_actual -= $cantidad;
            $stock->ultima_verificacion = CarbonImmutable::now();
            $stock->save();

            $costoUnitario = $stock->lote?->costo_unitario;
            $costoTotal = $costoUnitario !== null ? $costoUnitario * $cantidad : null;

            $this->movimientoModel->create([
                'tipo' => 'CONSUMO',
                'lote_id' => $stock->lote_id,
                'producto_id' => $producto->id,
                'cantidad' => -$cantidad,
                'costo_unitario' => $costoUnitario,
                'costo_total' => $costoTotal,
                'ubicacion_origen_id' => null,
                'ubicacion_destino_id' => null,
                'documento_tipo' => 'consumo_stock',
                'documento_id' => $stock->id,
                'referencia' => $referencia ?: sprintf(
                    'Consumo en %s: %s',
                    $entidad::class,
                    $motivo
                ),
                'creado_por_id' => $creadoPorId,
                'notas' => $motivo,
            ]);
        });
    }

    private function loadStockWithLock(int $id): Stock
    {
        return $this->stockModel->query()
            ->with(['stockable', 'variante.producto', 'lote'])
            ->lockForUpdate()
            ->findOrFail($id);
    }

    private function loadVariante(Stock $stock): ProductoVariante
    {
        $variante = $stock->variante;

        if ($variante === null) {
            throw new \RuntimeException('Registro de stock incompleto: falta variante.');
        }

        return $variante;
    }

    private function loadEntidad(Stock $stock): Model
    {
        $entidad = $stock->stockable;

        if ($entidad === null) {
            throw new \RuntimeException('Registro de stock incompleto: falta entidad asociada.');
        }

        return $entidad;
    }

    private function loadProducto(ProductoVariante $variante): Producto
    {
        $producto = $variante->producto;

        if ($producto === null) {
            throw new \RuntimeException("Variante ID {$variante->id} no tiene producto asociado.");
        }

        return $producto;
    }
}
