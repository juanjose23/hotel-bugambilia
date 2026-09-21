<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Lavanderia;

use App\Actions\Limpieza\Lavanderia\FinalizarProcesosLavanderia;
use App\BusinessLogic\Inventario\Servicios\ServicioConsumos;
use App\Repository\Persistencia\Limpieza\LavanderiaRepositorioInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

final readonly class RegistrarConsumoMermaLavanderia
{
    public function __construct(
        private ServicioConsumos $servicioConsumos,
        private FinalizarProcesosLavanderia $finalizarProcesos,
        private LavanderiaRepositorioInterface $lavanderiaRepositorio,
    ) {}

    /**
     * @param  int|array<int>  $lavanderiaId
     */
    public function execute(int $stockId, float $cantidad, int|array $lavanderiaId, ?int $creadoPorId, ?string $notas): void
    {
        $this->ejecutar($stockId, $cantidad, $lavanderiaId, $creadoPorId, $notas);
    }

    /**
     * @param  int|array<int>  $lavanderiaId
     */
    public function ejecutar(int $stockId, float $cantidad, int|array $lavanderiaId, ?int $creadoPorId, ?string $notas): void
    {
        if ($cantidad <= 0.0) {
            throw new InvalidArgumentException('La cantidad a consumir debe ser mayor a cero.');
        }

        DB::transaction(function () use ($stockId, $cantidad, $lavanderiaId, $creadoPorId, $notas): void {
            $stock = $this->lavanderiaRepositorio->buscarStockPorIdConLock($stockId, $lavanderiaId);

            if ((float) $stock->cantidad < $cantidad) {
                throw new RuntimeException(sprintf(
                    'Stock insuficiente en lavandería. Disponible: %f, requerido: %f',
                    (float) $stock->cantidad,
                    $cantidad,
                ));
            }

            $detalle = $this->servicioConsumos->ejecutarConsumoDeStock(
                stock: $stock,
                cantidad: $cantidad,
                tipoMovimiento: 'CONSUMO_LAVANDERIA',
                creadoPorId: $creadoPorId,
                referencia: 'Consumo/Merma registrado en Lavandería',
                notas: $notas,
            );

            $this->finalizarProcesos->execute(
                productoId: (int) $stock->producto_id,
                productoVarianteId: $stock->producto_variante_id ? (int) $stock->producto_variante_id : null,
                loteId: isset($detalle['lote_id']) ? (int) $detalle['lote_id'] : null,
                cantidad: (float) $detalle['cantidad'],
            );
        });
    }
}
