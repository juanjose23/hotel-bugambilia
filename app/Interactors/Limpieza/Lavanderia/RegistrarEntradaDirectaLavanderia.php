<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Lavanderia;

use App\Repository\Persistencia\Catalogos\ProductoRepositorioInterface;
use App\Repository\Persistencia\Limpieza\LavanderiaRepositorioInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final readonly class RegistrarEntradaDirectaLavanderia
{
    public function __construct(
        private LavanderiaRepositorioInterface $lavanderiaRepositorio,
        private ProductoRepositorioInterface $productoRepositorio,
    ) {}

    public function execute(
        int $productoVarianteId,
        float $cantidad,
        int $ubicacionLavanderiaId,
        ?int $creadoPorId,
        ?string $notas = null,
        ?string $tipoOrigen = null,
        ?int $origenId = null,
        ?int $loteId = null,
    ): void {
        $this->ejecutar(
            $productoVarianteId,
            $cantidad,
            $ubicacionLavanderiaId,
            $creadoPorId,
            $notas,
            $tipoOrigen,
            $origenId,
            $loteId,
        );
    }

    public function ejecutar(
        int $productoVarianteId,
        float $cantidad,
        int $ubicacionLavanderiaId,
        ?int $creadoPorId,
        ?string $notas = null,
        ?string $tipoOrigen = null,
        ?int $origenId = null,
        ?int $loteId = null,
    ): void {
        if ($cantidad <= 0.0) {
            throw new InvalidArgumentException('La cantidad a ingresar debe ser mayor a cero.');
        }

        DB::transaction(function () use ($productoVarianteId, $cantidad, $ubicacionLavanderiaId, $creadoPorId, $notas, $tipoOrigen, $origenId, $loteId): void {
            $this->procesarItem(
                productoVarianteId: $productoVarianteId,
                cantidad: $cantidad,
                ubicacionLavanderiaId: $ubicacionLavanderiaId,
                creadoPorId: $creadoPorId,
                notas: $notas,
                tipoOrigen: $tipoOrigen,
                origenId: $origenId,
                loteId: $loteId,
            );
        });
    }

    /**
     * @param  list<array{producto_variante_id: int, lote_id?: int|null, cantidad: float, notas?: string|null}>  $items
     * @return array{total_items: int, total_piezas: float}
     */
    public function ejecutarLote(
        array $items,
        int $ubicacionLavanderiaId,
        ?int $creadoPorId,
        ?string $notasGenerales = null,
        ?string $tipoOrigen = null,
        ?int $origenId = null,
    ): array {
        $itemsValidos = array_filter($items, fn (array $item): bool => $item['cantidad'] > 0.0);

        if (empty($itemsValidos)) {
            throw new InvalidArgumentException('Debe ingresar al menos un producto con cantidad mayor a cero.');
        }

        return DB::transaction(function () use ($itemsValidos, $ubicacionLavanderiaId, $creadoPorId, $notasGenerales, $tipoOrigen, $origenId): array {
            $totalPiezas = 0.0;
            $totalItems = 0;

            foreach ($itemsValidos as $item) {
                $varianteId = $item['producto_variante_id'];
                $cantidad = $item['cantidad'];
                $itemLoteId = isset($item['lote_id']) && $item['lote_id'] > 0
                    ? $item['lote_id']
                    : null;
                $notasRaw = $item['notas'] ?? null;
                $itemNotas = $notasRaw !== null && trim($notasRaw) !== ''
                    ? trim($notasRaw)
                    : $notasGenerales;

                $this->procesarItem(
                    productoVarianteId: $varianteId,
                    cantidad: $cantidad,
                    ubicacionLavanderiaId: $ubicacionLavanderiaId,
                    creadoPorId: $creadoPorId,
                    notas: $itemNotas,
                    tipoOrigen: $tipoOrigen,
                    origenId: $origenId,
                    loteId: $itemLoteId,
                );

                $totalPiezas += $cantidad;
                $totalItems++;
            }

            return [
                'total_items' => $totalItems,
                'total_piezas' => $totalPiezas,
            ];
        });
    }

    private function procesarItem(
        int $productoVarianteId,
        float $cantidad,
        int $ubicacionLavanderiaId,
        ?int $creadoPorId,
        ?string $notas = null,
        ?string $tipoOrigen = null,
        ?int $origenId = null,
        ?int $loteId = null,
    ): void {
        $variante = $this->productoRepositorio->buscarVariantePorId($productoVarianteId);
        if (! $variante) {
            throw new InvalidArgumentException("Variante no encontrada: {$productoVarianteId}");
        }

        $referencia = $this->construirReferencia($tipoOrigen, $origenId);

        $this->lavanderiaRepositorio->entradaDirectaPrenda(
            lavanderiaId: $ubicacionLavanderiaId,
            productoId: (int) $variante->producto_id,
            varianteId: $productoVarianteId,
            cantidad: $cantidad,
            loteId: $loteId,
            tipoOrigen: $tipoOrigen,
            origenId: $origenId,
            creadoPorId: $creadoPorId,
            notas: $notas,
            referencia: $referencia,
        );
    }

    private function construirReferencia(?string $tipoOrigen, ?int $origenId): string
    {
        if ($tipoOrigen === null || $origenId === null || $origenId <= 0) {
            return 'Entrada directa a lavandería';
        }

        return match ($tipoOrigen) {
            'habitacion' => "Entrada a lavandería desde Habitación #{$origenId}",
            'espacio' => "Entrada a lavandería desde Espacio #{$origenId}",
            'ubicacion' => "Entrada a lavandería desde Almacén/Bodega #{$origenId}",
            'carrito' => "Entrada a lavandería desde Carrito #{$origenId}",
            default => "Entrada a lavandería desde {$tipoOrigen} #{$origenId}",
        };
    }
}
