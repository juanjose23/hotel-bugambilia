<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Repository\Models\Restaurante\ProcesoCocina;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class RegistrarProcesoCocina
{
    public function __construct(
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}

    /**
     * Registra un proceso de cocina basado en la receta de un plato.
     * El costo se obtiene del Stock en Cocina Restaurante → Lote → costo_unitario.
     *
     * @param  array{codigo: string, plato_id: int, cantidad_platos: int, realizado_por?: int|null, observaciones?: string|null}  $data
     */
    public function ejecutar(array $data): ProcesoCocina
    {
        return DB::transaction(function () use ($data): ProcesoCocina {
            $plato = $this->repositorio->obtenerPlatoConReceta($data['plato_id']);

            if (! $plato) {
                throw new RuntimeException("El plato [{$data['plato_id']}] no existe.");
            }

            $productoReceta = $plato->receta;

            if (! $productoReceta) {
                throw new RuntimeException("El plato [{$plato->nombre}] no tiene una receta asociada.");
            }

            $cantidadPlatos = max($data['cantidad_platos'], 1);
            $ingredientes = $this->repositorio->obtenerIngredientesReceta($productoReceta->id);
            $cocina = $this->repositorio->obtenerUbicacionPorNombre('Cocina Restaurante');
            $cocinaId = $cocina?->id;

            $costoTotal = 0.0;
            $itemsData = [];

            foreach ($ingredientes as $ingrediente) {
                $variante = $ingrediente->variante;
                $productoDestino = $variante?->producto;
                $cantidadIngrediente = (float) $ingrediente->cantidad * $cantidadPlatos;

                $costoUnitario = 0.0;
                if ($cocinaId && $variante) {
                    $stock = $this->repositorio->obtenerStockConLote($cocinaId, $variante->id);

                    if ($stock && $stock->lote?->costo_unitario) {
                        $costoUnitario = (float) $stock->lote->costo_unitario;
                    }
                }

                $costoAsignado = round($costoUnitario * $cantidadIngrediente, 2);
                $costoTotal += $costoAsignado;

                $itemsData[] = [
                    'ingrediente' => $ingrediente,
                    'variante' => $variante,
                    'productoDestino' => $productoDestino,
                    'cantidadIngrediente' => $cantidadIngrediente,
                    'costoUnitario' => $costoUnitario,
                    'costoAsignado' => $costoAsignado,
                ];
            }

            $proceso = new ProcesoCocina([
                'codigo' => $data['codigo'],
                'plato_id' => $plato->id,
                'cantidad_platos' => $cantidadPlatos,
                'producto_origen_id' => $productoReceta->id,
                'cantidad_procesada' => $cantidadPlatos,
                'costo_total' => $costoTotal,
                'realizado_por' => $data['realizado_por'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            $this->repositorio->guardarProcesoCocina($proceso);

            foreach ($itemsData as $item) {
                $ingrediente = $item['ingrediente'];
                $variante = $item['variante'];
                $productoDestino = $item['productoDestino'];
                $cantidadIngrediente = $item['cantidadIngrediente'];

                $this->repositorio->guardarProcesoItem($proceso, [
                    'producto_destino_id' => $productoDestino !== null ? $productoDestino->id : $ingrediente->producto_padre_id,
                    'variante_destino_id' => $variante?->id,
                    'cantidad' => $cantidadIngrediente,
                    'peso_unitario' => $variante?->peso,
                    'peso_total' => $cantidadIngrediente,
                    'costo_asignado' => $item['costoAsignado'],
                    'es_merma' => false,
                    'ubicacion_destino_id' => null,
                ]);

                if ($cocinaId && $variante) {
                    $stockExistente = $this->repositorio->obtenerStockPorVariante($cocinaId, $variante->id);

                    if ($stockExistente) {
                        $stockExistente->cantidad_actual -= $cantidadIngrediente;
                        $this->repositorio->guardarStock($stockExistente);
                    }

                    $this->repositorio->registrarMovimiento([
                        'producto_id' => $productoDestino?->id,
                        'producto_variante_id' => $variante->id,
                        'tipo' => 'CONSUMO',
                        'cantidad' => $cantidadIngrediente,
                        'costo_unitario' => $item['costoUnitario'],
                        'fecha' => now(),
                    ]);
                }
            }

            return $proceso;
        });
    }
}
