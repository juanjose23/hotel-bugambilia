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

    public function ejecutar(PedidoItem $item): void
    {
        DB::transaction(function () use ($item): void {
            $item = $this->items->bloquear($item->id) ?? throw new DomainException('Partida no encontrada.');
            if ($this->existencias->tieneConsumo($item->id)) {
                return;
            }
            $consumo = $this->ingredientesQuery->ejecutar($item);
            if ($consumo === null) {
                return;
            }
            foreach ($consumo['ingredientes'] as $ingrediente) {
                $varianteId = (int) $ingrediente->producto_variante_id;
                $sustitucion = $this->repositorio->obtenerSustitucionActiva($item->id, $varianteId);
                $varianteConsumoId = $sustitucion !== null ? (int) $sustitucion->variante_sustituta_id : $varianteId;
                $cantidad = $sustitucion !== null ? (float) $sustitucion->cantidad_usada : $this->calcularCantidadIngrediente->ejecutar($ingrediente, $item);
                $stocks = $this->existencias->porVariante($varianteConsumoId, (int) $consumo['ubicacion_id'], true);
                $variante = $this->repositorio->obtenerVarianteConProducto($varianteConsumoId);
                $nombre = trim(($variante->producto->nombre ?? 'Ingrediente').' - '.($variante->nombre_variante ?? ''));
                if ($this->existencias->cantidad($stocks) + 0.00001 < $cantidad) {
                    throw new DomainException('Stock insuficiente para '.$nombre);
                }
                $this->consumir->ejecutar($stocks, $cantidad, ['documento_tipo' => 'pedido_item', 'documento_id' => $item->id, 'referencia' => 'Consumo de '.$nombre.' para pedido #'.$item->pedido_id]);
            }
        });
    }
}
