<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Lavanderia;

use App\Repository\Persistencia\Catalogos\ProductoRepositorioInterface;
use App\Repository\Persistencia\Catalogos\UbicacionRepositorioInterface;
use App\Repository\Persistencia\Limpieza\LavanderiaRepositorioInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final readonly class RegistrarEntradaInsumosLavanderia
{
    public function __construct(
        private LavanderiaRepositorioInterface $lavanderiaRepositorio,
        private ProductoRepositorioInterface $productoRepositorio,
        private UbicacionRepositorioInterface $ubicacionRepositorio,
    ) {}

    /**
     * @param  list<array{producto_variante_id: int, cantidad: float, lote_id?: int|null, codigo_lote?: string|null, costo_unitario?: float|null, fecha_vencimiento?: string|null, notas?: string|null}>  $items
     * @param  int|array<int>  $ubicacionLavanderiaId
     * @return array{total_items: int, total_cantidad: float}
     */
    public function execute(
        string $tipoOrigen,
        array $items,
        int|array $ubicacionLavanderiaId,
        ?int $bodegaOrigenId = null,
        ?int $creadoPorId = null,
        ?string $documentoReferencia = null,
        ?string $notasGenerales = null,
    ): array {
        return $this->ejecutar(
            $tipoOrigen,
            $items,
            $ubicacionLavanderiaId,
            $bodegaOrigenId,
            $creadoPorId,
            $documentoReferencia,
            $notasGenerales,
        );
    }

    /**
     * @param  list<array{producto_variante_id: int, cantidad: float, lote_id?: int|null, codigo_lote?: string|null, costo_unitario?: float|null, fecha_vencimiento?: string|null, notas?: string|null}>  $items
     * @param  int|array<int>  $ubicacionLavanderiaId
     * @return array{total_items: int, total_cantidad: float}
     */
    public function ejecutar(
        string $tipoOrigen,
        array $items,
        int|array $ubicacionLavanderiaId,
        ?int $bodegaOrigenId = null,
        ?int $creadoPorId = null,
        ?string $documentoReferencia = null,
        ?string $notasGenerales = null,
    ): array {
        $lavanderiaId = is_array($ubicacionLavanderiaId)
            ? ($ubicacionLavanderiaId[0] ?? $this->ubicacionRepositorio->buscarActivaPorTipo('lavanderia')?->id)
            : $ubicacionLavanderiaId;

        if ($lavanderiaId === null) {
            throw new InvalidArgumentException('Ubicación de lavandería no válida.');
        }

        $itemsValidos = array_filter($items, fn (array $item): bool => $item['producto_variante_id'] > 0 && $item['cantidad'] > 0.0);

        if (empty($itemsValidos)) {
            throw new InvalidArgumentException('Debe ingresar al menos un insumo con cantidad mayor a cero.');
        }

        if ($tipoOrigen === 'bodega' && ($bodegaOrigenId === null || $bodegaOrigenId <= 0)) {
            throw new InvalidArgumentException('Debe seleccionar el almacén / bodega de origen.');
        }

        return DB::transaction(function () use (
            $tipoOrigen,
            $itemsValidos,
            $lavanderiaId,
            $bodegaOrigenId,
            $creadoPorId,
            $documentoReferencia,
            $notasGenerales
        ): array {
            $totalItems = 0;
            $totalCantidad = 0.0;

            foreach ($itemsValidos as $item) {
                $varianteId = (int) $item['producto_variante_id'];
                $cantidad = (float) $item['cantidad'];
                $variante = $this->productoRepositorio->buscarVariantePorId($varianteId);
                if (! $variante) {
                    throw new InvalidArgumentException("Variante no encontrada: {$varianteId}");
                }
                $productoId = (int) $variante->producto_id;
                $notasItem = isset($item['notas']) && trim((string) $item['notas']) !== ''
                    ? trim((string) $item['notas'])
                    : $notasGenerales;

                if ($tipoOrigen === 'bodega') {
                    $loteId = isset($item['lote_id']) && (int) $item['lote_id'] > 0 ? (int) $item['lote_id'] : null;

                    $this->lavanderiaRepositorio->entradaInsumosDesdeBodega(
                        bodegaOrigenId: (int) $bodegaOrigenId,
                        lavanderiaId: (int) $lavanderiaId,
                        varianteId: $varianteId,
                        productoId: $productoId,
                        cantidad: $cantidad,
                        loteId: $loteId,
                        creadoPorId: $creadoPorId,
                        referencia: $documentoReferencia,
                        notas: $notasItem,
                    );
                } else {
                    $codigoLote = isset($item['codigo_lote']) && trim((string) $item['codigo_lote']) !== ''
                        ? trim((string) $item['codigo_lote'])
                        : null;
                    $costoUnitario = isset($item['costo_unitario']) ? (float) $item['costo_unitario'] : null;
                    $fechaVencimiento = isset($item['fecha_vencimiento']) && trim((string) $item['fecha_vencimiento']) !== '' ? trim((string) $item['fecha_vencimiento']) : null;

                    $this->lavanderiaRepositorio->entradaDirectaInsumos(
                        lavanderiaId: (int) $lavanderiaId,
                        productoId: $productoId,
                        varianteId: $varianteId,
                        cantidad: $cantidad,
                        codigoLote: $codigoLote,
                        costoUnitario: $costoUnitario,
                        fechaVencimiento: $fechaVencimiento,
                        creadoPorId: $creadoPorId,
                        referencia: $documentoReferencia,
                        notas: $notasItem,
                    );
                }

                $totalItems++;
                $totalCantidad += $cantidad;
            }

            return [
                'total_items' => $totalItems,
                'total_cantidad' => $totalCantidad,
            ];
        });
    }
}
