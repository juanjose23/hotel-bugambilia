<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Restaurante\SustitucionIngrediente;
use App\Repository\Persistencia\Restaurante\ExistenciasCocinaRepositorio;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerItemPedidoQuery;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class AutorizarSustitucionIngrediente
{
    public function __construct(
        private RestauranteRepositorioInterface $repositorio,
        private ExistenciasCocinaRepositorio $existencias,
        private ObtenerItemPedidoQuery $items,
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

        $varianteOriginal = $this->repositorio->obtenerVarianteConProducto($varianteOriginalId);
        $varianteSustituta = $this->repositorio->obtenerVarianteConProducto($varianteSustitutaId);

        if (! $varianteOriginal || ! $varianteSustituta) {
            throw new DomainException('Las variantes especificadas no existen.');
        }

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
            $item = $this->items->bloquear($item->id) ?? throw new DomainException('Partida no encontrada.');
            if (! in_array($item->estado, [EstadoItemPedido::PENDIENTE, EstadoItemPedido::BLOQUEADO_STOCK], true)) {
                throw new DomainException('No se pueden sustituir ingredientes de una partida ya preparada.');
            }
            $ubicacionId = $this->existencias->ubicacion($item->area_cocina ?? $item->plato?->area_cocina, $item->pedido?->mesa_id !== null ? (int) $item->pedido->mesa_id : null);
            $disponible = $this->existencias->cantidad($this->existencias->porVariante((int) $varianteSustituta->id, $ubicacionId, true));

            if ($disponible < $cantidad) {
                throw new DomainException(sprintf(
                    'Stock insuficiente para sustituto %s. Disponible: %s; requerido: %s.',
                    $this->nombreVariante($varianteSustituta),
                    $disponible,
                    $cantidad,
                ));
            }

            $this->repositorio->desactivarSustitucionesActivas((int) $item->id, (int) $varianteOriginal->id);

            $sustitucion = $this->repositorio->crearSustitucionIngrediente([
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
