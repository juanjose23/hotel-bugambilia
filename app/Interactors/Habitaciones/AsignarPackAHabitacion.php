<?php

declare(strict_types=1);

namespace App\Interactors\Habitaciones;

use App\Interactors\Inventario\ConsumirStock;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Persistencia\Inventario\PackRepositorioInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class AsignarPackAHabitacion
{
    public function __construct(
        private ConsumirStock $consumirStock,
        private PackRepositorioInterface $packRepositorio,
    ) {}

    /**
     * Asigna un pack/kit a un destino genérico (habitación, espacio común o ubicación/carro).
     * Si falta stock de algún componente, surte únicamente la cantidad disponible, dejando
     * la reposición como pendiente (actual < ideal).
     *
     * @return array<int, array<string, mixed>>
     */
    public function execute(
        int $destinoId,
        int $productoPackId,
        int $bodegaOrigenId,
        float $cantidadPacks = 1.0,
        ?int $creadoPorId = null,
        ?string $referencia = null,
        string $destinoTipo = 'habitacion',
        ?int $colaboradorId = null,
    ): array {
        if ($cantidadPacks <= 0) {
            throw new \InvalidArgumentException('La cantidad de packs debe ser mayor a cero.');
        }

        if (! in_array($destinoTipo, ['habitacion', 'espacio', 'ubicacion'], true)) {
            throw new \InvalidArgumentException("Tipo de destino inválido: {$destinoTipo}");
        }

        return DB::transaction(fn () => $this->asignar(
            $destinoId,
            $productoPackId,
            $bodegaOrigenId,
            $cantidadPacks,
            $creadoPorId,
            $referencia,
            $destinoTipo,
            $colaboradorId
        ));
    }

    /**
     * @return array<int, array{variante_id: int, cantidad_asignada: float, cantidad_requerida: float, stock_id: int|null, lote_id: int|null}>
     */
    private function asignar(
        int $destinoId,
        int $productoPackId,
        int $bodegaOrigenId,
        float $cantidadPacks = 1.0,
        ?int $creadoPorId = null,
        ?string $referencia = null,
        string $destinoTipo = 'habitacion',
        ?int $colaboradorId = null,
    ): array {
        $stockableType = match ($destinoTipo) {
            'habitacion' => Habitacion::class,
            'espacio' => Espacio::class,
            'ubicacion' => Ubicacion::class,
            default => Habitacion::class,
        };

        $destinoUbicacionId = $this->packRepositorio->obtenerUbicacionDestino($stockableType, $destinoId);

        $nombres = $this->packRepositorio->resolverNombresYColaborador($creadoPorId, $colaboradorId);
        $registradorName = $nombres['registrador'];
        $colaboradorName = $nombres['colaborador'];

        $items = $this->packRepositorio->obtenerItemsKit($productoPackId);
        /** @var array<int, int> $varianteIds */
        $varianteIds = $items->pluck('producto_variante_id')
            ->filter()
            ->unique()
            ->values()
            ->map(fn (mixed $id): int => is_numeric($id) ? (int) $id : 0)
            ->all();

        $availableStocks = $this->packRepositorio->obtenerStocksDisponibles($bodegaOrigenId, $varianteIds);

        $destinoStocks = collect();
        if ($destinoTipo !== 'ubicacion') {
            $destinoStocks = $this->packRepositorio->obtenerStocksDestinoShared($stockableType, $destinoId, $varianteIds);
        }

        $resultado = [];

        foreach ($items as $item) {
            $cantidadTotal = (float) $item->cantidad * $cantidadPacks;
            $variante = $item->variante;

            if (! $variante) {
                throw new \RuntimeException("Item del kit ID {$item->id} no tiene variante asociada.");
            }

            $producto = $variante->producto;
            if (! $producto) {
                throw new \RuntimeException("Variante ID {$variante->id} no tiene producto asociado.");
            }

            $stockItems = $availableStocks->get($variante->id);
            $sum = $stockItems instanceof Collection ? $stockItems->sum('cantidad') : 0;
            $available = is_scalar($sum) ? floatval($sum) : 0.0;

            $cantidadASurtir = min($cantidadTotal, $available);
            $loteConsumidoId = $item->lote_id;
            $stockConsumidoId = null;

            if ($cantidadASurtir > 0) {
                $consumo = $this->consumirStock->execute(
                    productoId: $producto->id,
                    cantidadRequerida: $cantidadASurtir,
                    ubicacionId: $bodegaOrigenId,
                    tipoMovimiento: 'TRASLADO',
                    productoVarianteId: $variante->id,
                    documentoId: $destinoId,
                    documentoTipo: $destinoTipo,
                    creadoPorId: $creadoPorId,
                    referencia: $referencia ?: sprintf('Kit a %s ID %d', $destinoTipo, $destinoId),
                    notas: sprintf(
                        'Asignación de kit ID %d. Llevó: %s. Registró: %s.',
                        $productoPackId,
                        $colaboradorName,
                        $registradorName
                    ),
                    ubicacionDestinoId: $destinoUbicacionId,
                );

                $loteConsumidoId = collect($consumo)->firstWhere('lote_id', '!==', null)['lote_id'] ?? $item->lote_id;
                $stockConsumidoId = $consumo[0]['stock_id'] ?? null;
            }

            if ($destinoTipo === 'ubicacion') {
                if ($cantidadASurtir > 0) {
                    $this->packRepositorio->guardarStockDestinoFisico(
                        ubicacionId: $destinoId,
                        productoId: $producto->id,
                        varianteId: $variante->id,
                        loteId: $loteConsumidoId,
                        cantidad: $cantidadASurtir,
                    );
                }
            } else {
                $stockDestino = $destinoStocks->get($variante->id);
                $this->packRepositorio->guardarOActualizarStockDestinoShared(
                    stockableType: $stockableType,
                    destinoId: $destinoId,
                    varianteId: $variante->id,
                    loteId: $loteConsumidoId !== null ? abs((int) $loteConsumidoId) : null,
                    cantidadIdeal: $cantidadTotal,
                    cantidadActual: $cantidadASurtir,
                    stockDestinoExistente: $stockDestino,
                );
            }

            $resultado[] = [
                'variante_id' => $variante->id,
                'cantidad_asignada' => $cantidadASurtir,
                'cantidad_requerida' => $cantidadTotal,
                'stock_id' => $stockConsumidoId,
                'lote_id' => $loteConsumidoId,
            ];
        }

        return $resultado;
    }
}
