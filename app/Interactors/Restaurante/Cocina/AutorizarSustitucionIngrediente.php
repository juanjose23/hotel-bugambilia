<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Enums\Restaurante\UbicacionCocina;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Restaurante\SustitucionIngrediente;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class AutorizarSustitucionIngrediente
{
    public function __construct(
        private RestauranteRepositorioInterface $repositorio,
    ) {}

    public function ejecutar(
        PedidoItem $item,
        int $varianteOriginalId,
        int $varianteSustitutaId,
        float $cantidadRequerida,
        ?float $cantidadUsada = null,
        ?int $usuarioId = null,
        ?string $motivo = null,
    ): SustitucionIngrediente {
        if ($varianteOriginalId === $varianteSustitutaId) {
            throw new DomainException('La variante sustituta debe ser diferente al ingrediente original.');
        }

        $varianteOriginal = ProductoVariante::query()->with('producto')->findOrFail($varianteOriginalId);
        $varianteSustituta = ProductoVariante::query()->with('producto')->findOrFail($varianteSustitutaId);
        $cantidad = round($cantidadUsada ?? $cantidadRequerida, 4);

        if ($cantidad <= 0) {
            throw new DomainException('La cantidad a sustituir debe ser mayor que cero.');
        }

        return DB::transaction(function () use (
            $item,
            $varianteOriginal,
            $varianteSustituta,
            $cantidadRequerida,
            $cantidad,
            $usuarioId,
            $motivo,
        ): SustitucionIngrediente {
            $cocina = $this->repositorio->obtenerUbicacionPorNombre(UbicacionCocina::RESTAURANTE->value)
                ?? $this->repositorio->obtenerUbicacionPorNombre('Cocina');
            $stock = $cocina !== null
                ? $this->repositorio->obtenerStockPorVariante((int) $cocina->id, (int) $varianteSustituta->id)
                : null;
            $disponible = $stock !== null ? (float) $stock->cantidad_actual : 0.0;

            if ($disponible < $cantidad) {
                throw new DomainException(sprintf(
                    'Stock insuficiente para sustituto %s. Disponible: %s; requerido: %s.',
                    $this->nombreVariante($varianteSustituta),
                    $disponible,
                    $cantidad,
                ));
            }

            SustitucionIngrediente::query()
                ->where('pedido_item_id', $item->id)
                ->where('variante_original_id', $varianteOriginal->id)
                ->where('estado', 1)
                ->update(['estado' => 0]);

            /** @var SustitucionIngrediente $sustitucion */
            $sustitucion = SustitucionIngrediente::query()->create([
                'pedido_item_id' => $item->id,
                'plato_id' => $item->plato_id,
                'producto_original_id' => $varianteOriginal->producto_id,
                'variante_original_id' => $varianteOriginal->id,
                'producto_sustituto_id' => $varianteSustituta->producto_id,
                'variante_sustituta_id' => $varianteSustituta->id,
                'cantidad_requerida' => $cantidadRequerida,
                'cantidad_usada' => $cantidad,
                'motivo' => $motivo,
                'autorizado_por' => $usuarioId,
                'estado' => 1,
            ]);

            return $sustitucion;
        });
    }

    private function nombreVariante(ProductoVariante $variante): string
    {
        $producto = $variante->producto?->nombre;
        $nombreVariante = $variante->nombre_variante;

        return trim(($producto !== null ? "{$producto} - " : '').$nombreVariante);
    }
}
