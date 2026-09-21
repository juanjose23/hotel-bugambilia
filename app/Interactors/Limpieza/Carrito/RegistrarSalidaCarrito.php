<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Carrito;

use App\BusinessLogic\Inventario\Servicios\ServicioConsumos;
use App\Repository\Persistencia\Limpieza\LimpiezaRepositorioInterface;
use Illuminate\Support\Facades\DB;

final readonly class RegistrarSalidaCarrito
{
    public function __construct(
        private ServicioConsumos $servicioConsumos,
        private LimpiezaRepositorioInterface $limpiezaRepositorio,
    ) {}

    public function execute(
        int $stockId,
        float $cantidad,
        int $carritoId,
        string $tipoSalida,
        ?int $ejecucionId,
        ?int $creadoPorId,
        ?string $notas,
    ): void {
        $this->ejecutar(
            $stockId,
            $cantidad,
            $carritoId,
            $tipoSalida,
            $ejecucionId,
            $creadoPorId,
            $notas,
        );
    }

    public function ejecutar(
        int $stockId,
        float $cantidad,
        int $carritoId,
        string $tipoSalida,
        ?int $ejecucionId,
        ?int $creadoPorId,
        ?string $notas,
    ): void {
        if ($cantidad <= 0.0) {
            throw new \InvalidArgumentException('La cantidad debe ser mayor a cero.');
        }

        $tipoMovimiento = match ($tipoSalida) {
            'uso' => 'CONSUMO_LIMPIEZA',
            'merma' => 'MERMA_CARRITO',
            default => throw new \InvalidArgumentException("Tipo de salida inválido: {$tipoSalida}"),
        };

        DB::transaction(function () use ($stockId, $cantidad, $carritoId, $tipoSalida, $tipoMovimiento, $ejecucionId, $creadoPorId, $notas): void {
            $stock = $this->limpiezaRepositorio->obtenerStockCarritoPorIdConLock($stockId, $carritoId);

            if ((float) $stock->cantidad < $cantidad) {
                throw new \RuntimeException(sprintf(
                    'Stock insuficiente en el carrito. Disponible: %f, requerido: %f',
                    (float) $stock->cantidad,
                    $cantidad,
                ));
            }

            $referencia = match (true) {
                $tipoSalida === 'uso' && $ejecucionId !== null => "Uso de insumo en limpieza #{$ejecucionId}",
                $tipoSalida === 'uso' => "Uso de insumo desde carrito #{$carritoId}",
                default => "Merma registrada desde carrito #{$carritoId}",
            };

            $this->servicioConsumos->ejecutarConsumoDeStock(
                stock: $stock,
                cantidad: $cantidad,
                tipoMovimiento: $tipoMovimiento,
                documentoId: $ejecucionId,
                documentoTipo: $ejecucionId !== null ? 'limpieza_ejecucion' : 'carrito_limpieza',
                creadoPorId: $creadoPorId,
                referencia: $referencia,
                notas: $notas,
            );

            if ($tipoSalida === 'uso' && $ejecucionId !== null && $stock->producto_variante_id !== null) {
                $this->limpiezaRepositorio->actualizarConsumoEnEjecucion($ejecucionId, (int) $stock->producto_variante_id, $cantidad);
            }
        });
    }
}
