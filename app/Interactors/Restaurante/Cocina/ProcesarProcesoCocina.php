<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\BusinessLogic\Restaurante\Cocina\CalcularCostoProcesoCocina;
use App\Enums\Restaurante\UbicacionCocina;
use App\Repository\Models\Restaurante\ProcesoCocina;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class ProcesarProcesoCocina
{
    public function __construct(
        private CalcularCostoProcesoCocina $calcularCosto,
        private RestauranteRepositorioInterface $repositorio,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function guardar(?ProcesoCocina $proceso, array $data, ?int $usuarioId = null): ProcesoCocina
    {
        return DB::transaction(function () use ($proceso, $data, $usuarioId): ProcesoCocina {
            if ($proceso !== null && ($proceso->getAttribute('confirmado_at') !== null || $proceso->getAttribute('pedido_item_id') !== null || $proceso->getAttribute('tipo_registro') !== 'borrador')) {
                throw new \DomainException('Los consumos de pedidos y producciones confirmadas no se pueden modificar.');
            }
            $platoId = is_numeric($data['plato_id'] ?? null) ? (int) $data['plato_id'] : null;
            $cantPlatos = is_numeric($data['cantidad_platos'] ?? null) ? (int) $data['cantidad_platos'] : 1;
            $productoOrigenId = is_numeric($data['producto_origen_id'] ?? null) ? (int) $data['producto_origen_id'] : null;
            $varianteOrigenId = null;
            $cantidadProcesada = 1.0;
            $obs = is_string($data['observaciones'] ?? null) ? $data['observaciones'] : null;

            $plato = $platoId !== null ? $this->repositorio->obtenerPlatoConReceta($platoId) : null;
            if ($plato === null || $plato->estado !== 1 || $plato->producto_receta_id === null || $cantPlatos < 1 || ! is_numeric($data['cantidad_platos'] ?? null) || (float) $data['cantidad_platos'] !== (float) $cantPlatos) {
                throw new \DomainException('Seleccione un platillo activo con receta y una cantidad entera de porciones.');
            }
            if ($productoOrigenId !== null && $productoOrigenId !== $plato->producto_receta_id) {
                throw new \DomainException('La receta debe corresponder al platillo seleccionado.');
            }
            $productoOrigenId = $plato->producto_receta_id;
            $rendimiento = (float) ($plato->receta->rendimiento_porciones ?? 1);
            if ($rendimiento <= 0) {
                throw new \DomainException('Configure el rendimiento de la receta antes de planificar.');
            }
            $itemsData = [];
            foreach ($this->repositorio->obtenerIngredientesReceta($productoOrigenId) as $ingrediente) {
                $variante = $ingrediente->variante;
                if ($variante === null) {
                    throw new \DomainException('Complete las presentaciones de los ingredientes de la receta.');
                }
                $itemsData[] = ['producto_destino_id' => $variante->producto_id, 'variante_destino_id' => $variante->id, 'cantidad' => round((float) $ingrediente->cantidad * $cantPlatos / $rendimiento, 4), 'costo_asignado' => 0.0, 'es_merma' => false];
            }
            if ($itemsData === []) {
                throw new \DomainException('La receta no tiene ingredientes configurados.');
            }
            $resulCostos = $this->calcularCosto->calcularDesdeArray($cantPlatos, $itemsData);

            if ($proceso === null) {
                $codigo = 'PRC-'.Str::upper(Str::random(16));
                $proceso = $this->repositorio->crearProcesoCocina([
                    'codigo' => $codigo,
                    'plato_id' => $platoId,
                    'cantidad_platos' => $cantPlatos,
                    'producto_origen_id' => $productoOrigenId,
                    'variante_origen_id' => $varianteOrigenId,
                    'cantidad_procesada' => $cantidadProcesada,
                    'costo_total' => $resulCostos['costo_total'],
                    'observaciones' => $obs,
                    'realizado_por' => $usuarioId,
                ]);
            } else {
                $this->repositorio->actualizarProcesoCocina($proceso, [
                    'plato_id' => $platoId,
                    'cantidad_platos' => $cantPlatos,
                    'producto_origen_id' => $productoOrigenId,
                    'variante_origen_id' => $varianteOrigenId,
                    'cantidad_procesada' => $cantidadProcesada,
                    'costo_total' => $resulCostos['costo_total'],
                    'observaciones' => $obs,
                ]);

                $this->repositorio->eliminarItemsDeProcesoCocina($proceso);
            }

            $cocina = $this->repositorio->obtenerUbicacionPorNombre(UbicacionCocina::RESTAURANTE->value);
            foreach ($itemsData as $itemData) {
                $stock = $cocina !== null ? $this->repositorio->obtenerStockConLote($cocina->id, $itemData['variante_destino_id']) : null;
                $costo = round($itemData['cantidad'] * (float) ($stock?->lote->costo_unitario ?? 0.0), 2);
                $this->repositorio->guardarProcesoItem($proceso, [
                    'producto_destino_id' => $itemData['producto_destino_id'],
                    'variante_destino_id' => $itemData['variante_destino_id'],
                    'cantidad' => $itemData['cantidad'],
                    'es_merma' => false,
                    'costo_asignado' => $costo,
                    'ubicacion_destino_id' => $cocina?->id,
                ]);
            }

            return $this->repositorio->recalcularCostoTotalProceso($proceso);
        });
    }
}
