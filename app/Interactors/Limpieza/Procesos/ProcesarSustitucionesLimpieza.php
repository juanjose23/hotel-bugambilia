<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Procesos;

use App\Interactors\Inventario\ConsumirStock;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Persistencia\Catalogos\ProductoRepositorioInterface;
use App\Repository\Persistencia\Limpieza\LimpiezaRepositorioInterface;
use RuntimeException;

final readonly class ProcesarSustitucionesLimpieza
{
    public function __construct(
        private ConsumirStock $consumirStock,
        private LimpiezaRepositorioInterface $limpiezaRepositorio,
        private ProductoRepositorioInterface $productoRepositorio,
    ) {}

    /** @param array<string, mixed> $data */
    public function execute(LimpiezaEjecucion $ejecucion, array $data, ?int $carritoId, ?int $usuarioId): void
    {
        $this->ejecutar($ejecucion, $data, $carritoId, $usuarioId);
    }

    /** @param array<string, mixed> $data */
    public function ejecutar(LimpiezaEjecucion $ejecucion, array $data, ?int $carritoId, ?int $usuarioId): void
    {
        /** @var list<array{ producto_variante_id: int|string, sustituto_variante_id: int|string, cantidad: int|float|string }> $sustituciones */
        $sustituciones = $data['sustituciones'] ?? [];
        foreach ($sustituciones as $sub) {
            $originalVarId = (int) $sub['producto_variante_id'];
            $sustitutoVarId = (int) $sub['sustituto_variante_id'];
            $qty = (float) $sub['cantidad'];

            if (! $originalVarId || ! $sustitutoVarId || $qty <= 0) {
                continue;
            }

            $originalVar = $this->productoRepositorio->buscarVariantePorId($originalVarId);
            $sustitutoVar = $this->productoRepositorio->buscarVariantePorId($sustitutoVarId);

            if (! $originalVar || ! $sustitutoVar) {
                continue;
            }

            $this->limpiezaRepositorio->registrarSustitucionStock(
                ejecucionId: (int) $ejecucion->id,
                productoId: (int) $originalVar->producto_id,
                sustitutoProductoId: (int) $sustitutoVar->producto_id,
                varianteId: $originalVarId,
                sustitutoVarianteId: $sustitutoVarId,
                cantidad: $qty,
            );

            if ($carritoId) {
                $available = $this->limpiezaRepositorio->obtenerStockDisponibleEnCarrito($carritoId, $sustitutoVarId);

                if ($available < $qty) {
                    throw new RuntimeException(sprintf(
                        'El carrito no cuenta con stock suficiente del producto sustituto "%s". Requerido para sustitución: %f, Disponible en Carro: %f. No se puede realizar la sustitución.',
                        $sustitutoVar->producto->nombre ?? 'Sustituto',
                        $qty,
                        $available
                    ));
                }

                $this->consumirStock->execute(
                    productoId: (int) $sustitutoVar->producto_id,
                    cantidadRequerida: $qty,
                    ubicacionId: $carritoId,
                    tipoMovimiento: 'TRASLADO',
                    productoVarianteId: $sustitutoVarId,
                    documentoId: (int) $ejecucion->id,
                    documentoTipo: 'limp_ejecuciones',
                    creadoPorId: $usuarioId,
                    referencia: "Sustitución en ejecución #{$ejecucion->id}",
                    notas: "Sustituye variante #{$originalVarId} por variante #{$sustitutoVarId}"
                );

                $this->limpiezaRepositorio->incrementarSharedStock(
                    stockableType: (string) $ejecucion->limpiable_type,
                    stockableId: (int) $ejecucion->limpiable_id,
                    varianteId: $originalVarId,
                    cantidad: $qty,
                );
            }
        }
    }
}
