<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Lavanderia;

use App\Actions\Limpieza\Lavanderia\FinalizarProcesosLavanderia;
use App\BusinessLogic\Inventario\Servicios\ServicioConsumos;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Persistencia\Limpieza\LavanderiaRepositorioInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

final readonly class ReponerDesdeLavanderia
{
    public function __construct(
        private ServicioConsumos $servicioConsumos,
        private FinalizarProcesosLavanderia $finalizarProcesos,
        private LavanderiaRepositorioInterface $lavanderiaRepositorio,
    ) {}

    /**
     * @param  int|array<int>  $ubicacionLavanderiaId
     */
    public function execute(
        int $stockId,
        float $cantidad,
        int|array $ubicacionLavanderiaId,
        string $tipoDestino,
        int $destinoId,
        ?int $creadoPorId,
    ): void {
        $this->ejecutar($stockId, $cantidad, $ubicacionLavanderiaId, $tipoDestino, $destinoId, $creadoPorId);
    }

    /**
     * @param  int|array<int>  $ubicacionLavanderiaId
     */
    public function ejecutar(
        int $stockId,
        float $cantidad,
        int|array $ubicacionLavanderiaId,
        string $tipoDestino,
        int $destinoId,
        ?int $creadoPorId,
    ): void {
        if ($cantidad <= 0.0) {
            throw new InvalidArgumentException('La cantidad a reponer debe ser mayor a cero.');
        }

        $stockableType = match ($tipoDestino) {
            'habitacion' => Habitacion::class,
            'espacio' => Espacio::class,
            'ubicacion' => Ubicacion::class,
            default => throw new InvalidArgumentException("Tipo de destino inválido: {$tipoDestino}"),
        };

        DB::transaction(function () use ($stockId, $cantidad, $ubicacionLavanderiaId, $stockableType, $destinoId, $creadoPorId): void {
            $stock = $this->lavanderiaRepositorio->buscarStockPorIdConLock($stockId, $ubicacionLavanderiaId);

            if ((float) $stock->cantidad < $cantidad) {
                throw new RuntimeException(sprintf(
                    'Stock insuficiente en lavandería. Disponible: %f, requerido: %f',
                    (float) $stock->cantidad,
                    $cantidad,
                ));
            }

            $ubicacionDestinoId = $this->lavanderiaRepositorio->resolverUbicacionDestino($stockableType, $destinoId);

            $detalle = $this->servicioConsumos->ejecutarConsumoDeStock(
                stock: $stock,
                cantidad: $cantidad,
                tipoMovimiento: 'TRASLADO',
                ubicacionDestinoId: $ubicacionDestinoId,
                creadoPorId: $creadoPorId,
                referencia: "Salida de lavandería hacia {$stockableType} #{$destinoId}",
                notas: 'Reposición de blancos/insumos desde lavandería.',
            );

            $cantidadConsumida = (float) $detalle['cantidad'];

            $this->lavanderiaRepositorio->reponerDestinoStock(
                stockableType: $stockableType,
                destinoId: $destinoId,
                varianteId: $stock->producto_variante_id !== null ? (int) $stock->producto_variante_id : null,
                cantidad: $cantidadConsumida,
                loteId: $stock->lote_id !== null ? (int) $stock->lote_id : null,
            );

            $this->finalizarProcesos->execute(
                productoId: (int) $stock->producto_id,
                productoVarianteId: $stock->producto_variante_id !== null ? (int) $stock->producto_variante_id : null,
                loteId: $stock->lote_id !== null ? (int) $stock->lote_id : null,
                cantidad: $cantidadConsumida,
            );
        });
    }
}
