<?php

declare(strict_types=1);

namespace App\BusinessLogic\Habitaciones;

use App\Interactors\Inventario\ConsumirStock\ConsumirStock;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Models\Shared\Stock as SharedStock;
use App\Repository\Models\User;
use App\Repository\Queries\Shared\ObtenerNombrePersona;
use Illuminate\Support\Collection;

class ServicioAsignacionPacks
{
    public function __construct(
        private readonly ConsumirStock $consumirStock,
        private readonly ObtenerNombrePersona $obtenerNombrePersona,
    ) {}

    /**
     * @return array<int, array{variante_id: int, cantidad_asignada: float, cantidad_requerida: float, stock_id: int|null, lote_id: int|null}>
     */
    public function asignar(
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

        $destinoUbicacionId = null;
        if ($stockableType === Habitacion::class) {
            $habitacion = Habitacion::findOrFail($destinoId);
            $destinoUbicacionId = $habitacion->ubicacion_id;
        } elseif ($stockableType === Espacio::class) {
            $espacio = Espacio::findOrFail($destinoId);
            $destinoUbicacionId = $espacio->ubicacion_id;
        } elseif ($stockableType === Ubicacion::class) {
            $ubicacion = Ubicacion::findOrFail($destinoId);
            $destinoUbicacionId = $ubicacion->id;
        }

        $registradorName = 'Sistema';
        if ($creadoPorId) {
            $user = User::with('persona')->find($creadoPorId);
            if ($user) {
                $registradorName = $user->persona
                    ? $this->obtenerNombrePersona->ejecutar($user->persona)
                    : ($user->name ?: 'Usuario');
            }
        }

        if ($colaboradorId === null && $creadoPorId) {
            $user = User::with('persona.colaborador')->find($creadoPorId);
            $colaboradorId = $user?->persona?->colaborador?->id;
        }

        $colaboradorName = 'Sistema';
        if ($colaboradorId) {
            $colaborador = Colaborador::with('persona')->find($colaboradorId);
            if ($colaborador && $colaborador->persona) {
                $colaboradorName = $this->obtenerNombrePersona->ejecutar($colaborador->persona);
            }
        }

        $items = ProductoKit::with(['variante.producto'])
            ->where('producto_padre_id', $productoPackId)
            ->get();

        $varianteIds = $items->pluck('producto_variante_id')->filter()->unique()->all();

        $availableStocks = Stock::where('ubicacion_id', $bodegaOrigenId)
            ->whereIn('producto_variante_id', $varianteIds)
            ->where('cantidad', '>', 0)
            ->get()
            ->groupBy('producto_variante_id');

        $destinoStocks = collect();
        if ($destinoTipo !== 'ubicacion') {
            $destinoStocks = SharedStock::withTrashed()
                ->where('stockable_type', $stockableType)
                ->where('stockable_id', $destinoId)
                ->whereIn('producto_variante_id', $varianteIds)
                ->get()
                ->keyBy('producto_variante_id');
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
                    $stockFisico = Stock::firstOrNew([
                        'ubicacion_id' => $destinoId,
                        'producto_id' => $producto->id,
                        'producto_variante_id' => $variante->id,
                        'lote_id' => $loteConsumidoId,
                    ]);
                    $stockFisico->cantidad = ($stockFisico->cantidad ?? 0.0) + $cantidadASurtir;
                    $stockFisico->save();
                }
            } else {
                $stockDestino = $destinoStocks->get($variante->id);

                if ($stockDestino) {
                    if ($stockDestino->trashed()) {
                        $stockDestino->restore();
                    }
                    $stockDestino->cantidad_actual = (float) $stockDestino->cantidad_actual + $cantidadASurtir;
                    $stockDestino->cantidad_ideal = (float) $stockDestino->cantidad_ideal + $cantidadTotal;
                    if ($loteConsumidoId) {
                        $stockDestino->lote_id = abs((int) $loteConsumidoId);
                    }
                    $stockDestino->save();
                } else {
                    SharedStock::create([
                        'stockable_type' => $stockableType,
                        'stockable_id' => $destinoId,
                        'producto_variante_id' => $variante->id,
                        'lote_id' => $loteConsumidoId,
                        'cantidad_ideal' => $cantidadTotal,
                        'cantidad_actual' => $cantidadASurtir,
                    ]);
                }
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
