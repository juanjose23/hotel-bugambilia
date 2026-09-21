<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Carrito;

use App\Interactors\Inventario\TrasladarEntreBodegas;
use App\Repository\Persistencia\Limpieza\LimpiezaRepositorioInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

final readonly class TrasladarStockSeleccionadoCarrito
{
    public function __construct(
        private TrasladarEntreBodegas $trasladarEntreBodegas,
        private LimpiezaRepositorioInterface $limpiezaRepositorio,
    ) {}

    public function execute(
        int $stockId,
        float $cantidad,
        int $origenId,
        int $destinoId,
        ?int $creadoPorId,
        string $referencia,
        string $notas,
    ): void {
        $this->ejecutar($stockId, $cantidad, $origenId, $destinoId, $creadoPorId, $referencia, $notas);
    }

    public function ejecutar(
        int $stockId,
        float $cantidad,
        int $origenId,
        int $destinoId,
        ?int $creadoPorId,
        string $referencia,
        string $notas,
    ): void {
        if ($cantidad <= 0.0) {
            throw new InvalidArgumentException('La cantidad debe ser mayor a cero.');
        }

        DB::transaction(function () use ($stockId, $cantidad, $origenId, $destinoId, $creadoPorId, $referencia, $notas): void {
            $stock = $this->limpiezaRepositorio->obtenerStockCarritoPorIdConLock($stockId, $origenId);

            if ((float) $stock->cantidad < $cantidad) {
                throw new RuntimeException(sprintf(
                    'Stock insuficiente. Disponible: %f, requerido: %f',
                    (float) $stock->cantidad,
                    $cantidad,
                ));
            }

            $this->trasladarEntreBodegas->execute(
                productoId: (int) $stock->producto_id,
                loteId: (int) $stock->lote_id,
                cantidad: $cantidad,
                origenId: $origenId,
                destinoId: $destinoId,
                productoVarianteId: $stock->producto_variante_id ? (int) $stock->producto_variante_id : null,
                creadoPorId: $creadoPorId,
                referencia: $referencia,
                notas: $notas,
            );
        });
    }
}
