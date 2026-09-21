<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Inventario;

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

final readonly class PackRepositorio implements PackRepositorioInterface
{
    public function __construct(
        private ObtenerNombrePersona $obtenerNombrePersona,
    ) {}

    public function obtenerUbicacionDestino(string $stockableType, int $destinoId): ?int
    {
        if ($stockableType === Habitacion::class) {
            /** @var Habitacion $habitacion */
            $habitacion = Habitacion::query()->findOrFail($destinoId);

            return $habitacion->ubicacion_id;
        }

        if ($stockableType === Espacio::class) {
            /** @var Espacio $espacio */
            $espacio = Espacio::query()->findOrFail($destinoId);

            return $espacio->ubicacion_id;
        }

        if ($stockableType === Ubicacion::class) {
            /** @var Ubicacion $ubicacion */
            $ubicacion = Ubicacion::query()->findOrFail($destinoId);

            return $ubicacion->id;
        }

        return null;
    }

    /**
     * @return array{registrador: string, colaborador: string, colaborador_id: int|null}
     */
    public function resolverNombresYColaborador(?int $creadoPorId, ?int $colaboradorId): array
    {
        $registradorName = 'Sistema';
        if ($creadoPorId) {
            /** @var User|null $user */
            $user = User::query()->with('persona')->find($creadoPorId);
            if ($user) {
                $registradorName = $user->persona
                    ? $this->obtenerNombrePersona->ejecutar($user->persona)
                    : ($user->name ?: 'Usuario');
            }
        }

        if ($colaboradorId === null && $creadoPorId) {
            /** @var User|null $userColab */
            $userColab = User::query()->with('persona.colaborador')->find($creadoPorId);
            $colaboradorId = $userColab?->persona?->colaborador?->id;
        }

        $colaboradorName = 'Sistema';
        if ($colaboradorId) {
            /** @var Colaborador|null $colaborador */
            $colaborador = Colaborador::query()->with('persona')->find($colaboradorId);
            if ($colaborador && $colaborador->persona) {
                $colaboradorName = $this->obtenerNombrePersona->ejecutar($colaborador->persona);
            }
        }

        return [
            'registrador' => $registradorName,
            'colaborador' => $colaboradorName,
            'colaborador_id' => $colaboradorId,
        ];
    }

    /**
     * @return Collection<int, ProductoKit>
     */
    public function obtenerItemsKit(int $productoPackId): Collection
    {
        return ProductoKit::query()
            ->with(['variante.producto'])
            ->where('producto_padre_id', $productoPackId)
            ->get();
    }

    /**
     * @param  array<int, int>  $varianteIds
     * @return Collection<(int|string), Collection<int, Stock>>
     */
    public function obtenerStocksDisponibles(int $bodegaOrigenId, array $varianteIds): Collection
    {
        /** @var Collection<(int|string), Collection<int, Stock>> $stocks */
        $stocks = Stock::query()
            ->where('ubicacion_id', $bodegaOrigenId)
            ->whereIn('producto_variante_id', $varianteIds)
            ->where('cantidad', '>', 0)
            ->get()
            ->groupBy('producto_variante_id');

        return $stocks;
    }

    /**
     * @param  array<int, int>  $varianteIds
     * @return Collection<(int|string), SharedStock>
     */
    public function obtenerStocksDestinoShared(string $stockableType, int $destinoId, array $varianteIds): Collection
    {
        /** @var Collection<(int|string), SharedStock> $stocks */
        $stocks = SharedStock::withTrashed()
            ->where('stockable_type', $stockableType)
            ->where('stockable_id', $destinoId)
            ->whereIn('producto_variante_id', $varianteIds)
            ->get()
            ->keyBy('producto_variante_id');

        return $stocks;
    }

    public function guardarStockDestinoFisico(int $ubicacionId, int $productoId, int $varianteId, ?int $loteId, float $cantidad): void
    {
        /** @var Stock $stockFisico */
        $stockFisico = Stock::query()->firstOrNew([
            'ubicacion_id' => $ubicacionId,
            'producto_id' => $productoId,
            'producto_variante_id' => $varianteId,
            'lote_id' => $loteId,
        ]);
        $stockFisico->cantidad = ($stockFisico->cantidad ?? 0.0) + $cantidad;
        $stockFisico->save();
    }

    public function guardarOActualizarStockDestinoShared(
        string $stockableType,
        int $destinoId,
        int $varianteId,
        ?int $loteId,
        float $cantidadIdeal,
        float $cantidadActual,
        mixed $stockDestinoExistente = null
    ): void {
        if ($stockDestinoExistente instanceof SharedStock) {
            if ($stockDestinoExistente->trashed()) {
                $stockDestinoExistente->restore();
            }
            $stockDestinoExistente->cantidad_actual = (float) $stockDestinoExistente->cantidad_actual + $cantidadActual;
            $stockDestinoExistente->cantidad_ideal = (float) $stockDestinoExistente->cantidad_ideal + $cantidadIdeal;
            if ($loteId !== null) {
                $stockDestinoExistente->lote_id = abs($loteId);
            }
            $stockDestinoExistente->save();
        } else {
            SharedStock::query()->create([
                'stockable_type' => $stockableType,
                'stockable_id' => $destinoId,
                'producto_variante_id' => $varianteId,
                'lote_id' => $loteId,
                'cantidad_ideal' => $cantidadIdeal,
                'cantidad_actual' => $cantidadActual,
            ]);
        }
    }
}
