<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\BusinessLogic\Restaurante\Cocina\CalcularCantidadIngredienteReceta;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Persistencia\Restaurante\ExistenciasCocinaRepositorio;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerIngredientesPedidoQuery;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerItemPedidoQuery;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class ConsumirIngredientesPedido
{
    public function __construct(private ObtenerIngredientesPedidoQuery $ingredientesQuery, private CalcularCantidadIngredienteReceta $calcularCantidadIngrediente, private RestauranteRepositorioInterface $repositorio, private ExistenciasCocinaRepositorio $existencias, private ConsumirExistenciasCocina $consumir, private ObtenerItemPedidoQuery $items) {}

    /** @return array<int, float> Cantidad consumida por existencia. */
    public function ejecutar(PedidoItem $item, ?int $usuarioId = null): array
    {
        return DB::transaction(function () use ($item, $usuarioId): array {
            $item = $this->items->bloquear($item->id) ?? throw new DomainException('Partida no encontrada.');
            if ($this->existencias->tieneConsumo($item->id)) {
                return [];
            }
            $retiros = [];
            foreach ($this->necesidades($item) as $necesidad) {
                $cantidad = $necesidad['cantidad'];
                $nombre = $necesidad['nombre'];
                $stocks = $this->existencias->porVariante($necesidad['variante_id'], $necesidad['ubicacion_id'], true);
                if ($this->existencias->cantidad($stocks) + 0.00001 < $cantidad) {
                    throw new DomainException('Stock insuficiente para '.$nombre);
                }
                $consumos = $this->consumir->ejecutar($stocks, $cantidad, ['creado_por_id' => $usuarioId, 'documento_tipo' => 'pedido_item', 'documento_id' => $item->id, 'referencia' => 'Consumo de '.$nombre.' para pedido #'.$item->pedido_id]);
                foreach ($consumos as $consumo) {
                    $stockId = $consumo['stock_id'];
                    $retiros[$stockId] = ($retiros[$stockId] ?? 0.0) + $consumo['cantidad'];
                }
            }

            return $retiros;
        });
    }

    /** @return list<array{variante_id: int, ubicacion_id: int, cantidad: float, nombre: string}> */
    public function necesidades(PedidoItem $item): array
    {
        $consumo = $this->ingredientesQuery->ejecutar($item);
        if ($consumo === null) {
            return [];
        }

        $necesidades = [];
        foreach ($consumo['ingredientes'] as $ingrediente) {
            $varianteId = (int) $ingrediente->producto_variante_id;
            $sustitucion = $this->repositorio->obtenerSustitucionActiva($item->id, $varianteId);
            $varianteConsumoId = $sustitucion !== null ? (int) $sustitucion->variante_sustituta_id : $varianteId;
            $cantidad = $sustitucion !== null ? (float) $sustitucion->cantidad_usada : $this->calcularCantidadIngrediente->ejecutar($ingrediente, $item);
            $variante = $this->repositorio->obtenerVarianteConProducto($varianteConsumoId);
            $necesidades[] = [
                'variante_id' => $varianteConsumoId,
                'ubicacion_id' => (int) $consumo['ubicacion_id'],
                'cantidad' => $cantidad,
                'nombre' => trim(($variante->producto->nombre ?? 'Ingrediente').' - '.($variante->nombre_variante ?? '')),
            ];
        }

        return $necesidades;
    }
}
