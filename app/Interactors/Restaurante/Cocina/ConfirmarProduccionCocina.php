<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\BusinessLogic\Restaurante\Cocina\MedidasCocina;
use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Restaurante\ProcesoCocina;
use App\Repository\Persistencia\Restaurante\ExistenciasCocinaRepositorio;
use App\Repository\Persistencia\Restaurante\ProduccionCocinaRepositorio;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class ConfirmarProduccionCocina
{
    public function __construct(private ProduccionCocinaRepositorio $produccion, private ExistenciasCocinaRepositorio $existencias, private ConsumirExistenciasCocina $consumir, private MedidasCocina $medidas) {}

    public function ejecutar(int $procesoId, int $varianteResultadoId, int $ubicacionId, float $cantidadObtenida, string $fechaVencimiento, ?int $usuarioId = null): ProcesoCocina
    {
        return DB::transaction(function () use ($procesoId, $varianteResultadoId, $ubicacionId, $cantidadObtenida, $fechaVencimiento, $usuarioId): ProcesoCocina {
            $proceso = $this->produccion->bloquear($procesoId);
            if ($proceso->getAttribute('confirmado_at') !== null) {
                throw new DomainException('La producción ya está confirmada.');
            }
            if ($proceso->getAttribute('tipo_registro') !== 'borrador' || $proceso->getAttribute('pedido_item_id') !== null) {
                throw new DomainException('El consumo de un pedido no puede confirmarse otra vez como producción.');
            }
            $resultado = $this->produccion->variante($varianteResultadoId);
            $vencimiento = Carbon::parse($fechaVencimiento)->toDateString();
            if ($cantidadObtenida <= 0 || ! is_finite($cantidadObtenida) || $vencimiento < now()->toDateString()) {
                throw new DomainException('Indique cantidad obtenida positiva y vigencia válida.');
            }
            $this->existencias->bloquearUbicaciones([$ubicacionId]);
            $ingredientes = $this->produccion->ingredientes((int) $proceso->producto_origen_id);
            if ($ingredientes->isEmpty()) {
                throw new DomainException('La preparación no tiene una receta configurada.');
            }
            $rendimiento = (float) ($proceso->productoOrigen->rendimiento_porciones ?? 1);
            $rendimiento = $rendimiento > 0 ? $rendimiento : 1.0;
            $unidadResultado = $this->unidad($resultado);
            $baseEntrada = 0.0;
            $costo = 0.0;
            foreach ($ingredientes as $ingrediente) {
                if ((int) $ingrediente->producto_variante_id === $varianteResultadoId) {
                    throw new DomainException('El resultado no puede ser un ingrediente de su propia producción.');
                }
                $cantidad = round((float) $ingrediente->cantidad * (float) $proceso->cantidad_platos / $rendimiento, 4);
                $varianteIngrediente = $ingrediente->variante;
                if ($varianteIngrediente === null) {
                    throw new DomainException('Ingrediente sin variante configurada.');
                }
                $unidadIngrediente = $this->unidad($varianteIngrediente);
                if ($unidadIngrediente['dimension'] !== $unidadResultado['dimension']) {
                    throw new DomainException('Configure medidas compatibles para justificar el rendimiento de producción.');
                }
                $baseEntrada += $cantidad * $unidadIngrediente['factor'];
                $consumos = $this->consumir->ejecutar($this->existencias->porVariante((int) $ingrediente->producto_variante_id, $ubicacionId, true), $cantidad, ['documento_tipo' => 'produccion_cocina', 'documento_id' => $proceso->id, 'referencia' => 'Producción '.$proceso->codigo, 'creado_por_id' => $usuarioId]);
                $costo += array_sum(array_column($consumos, 'costo_total'));
                foreach ($consumos as $consumo) {
                    if ($consumo['vencimiento'] !== null && $consumo['vencimiento'] < $vencimiento) {
                        $vencimiento = $consumo['vencimiento'];
                    }
                }
            }
            if ($cantidadObtenida * $unidadResultado['factor'] > $baseEntrada + 0.0001) {
                throw new DomainException('La cantidad obtenida supera las entradas de la receta.');
            }
            $perdida = round(($baseEntrada - $cantidadObtenida * $unidadResultado['factor']) / $unidadResultado['factor'], 4);
            if ($perdida > 0) {
                $this->existencias->movimiento(['tipo' => 'MERMA_COCINA', 'producto_id' => $resultado->producto_id, 'cantidad' => -$perdida, 'costo_total' => 0, 'ubicacion_origen_id' => $ubicacionId, 'documento_tipo' => 'produccion_cocina', 'documento_id' => $proceso->id, 'referencia' => 'Pérdida de rendimiento incluida en el costo de producción; unidad del resultado', 'creado_por_id' => $usuarioId]);
            }
            $costo = round($costo, 2);
            $lote = $this->produccion->crearLote(['producto_id' => $resultado->producto_id, 'producto_variante_id' => $resultado->id, 'ubicacion_id' => $ubicacionId, 'codigo_lote' => 'PRD-'.Str::upper(Str::random(16)), 'cantidad_inicial' => $cantidadObtenida, 'cantidad_disponible' => $cantidadObtenida, 'costo_unitario' => round($costo / $cantidadObtenida, 6), 'costo_total' => $costo, 'estado' => EstadoLote::Disponible, 'fecha_recepcion' => now()->toDateString(), 'fecha_vencimiento' => $vencimiento]);
            $this->produccion->crearStock($lote, $ubicacionId);
            $this->existencias->movimiento(['tipo' => 'PRODUCCION_ENTRADA', 'producto_id' => $resultado->producto_id, 'lote_id' => $lote->id, 'cantidad' => $cantidadObtenida, 'costo_unitario' => $lote->costo_unitario, 'costo_total' => $costo, 'ubicacion_destino_id' => $ubicacionId, 'documento_tipo' => 'produccion_cocina', 'documento_id' => $proceso->id, 'referencia' => 'Resultado de '.$proceso->codigo, 'creado_por_id' => $usuarioId]);

            return $this->produccion->confirmar($proceso, ['confirmado_at' => now(), 'lote_resultado_id' => $lote->id, 'cantidad_obtenida' => $cantidadObtenida, 'costo_total' => $costo]);
        });
    }

    /** @return array{dimension: string, factor: float} */
    private function unidad(ProductoVariante $variante): array
    {
        $producto = $variante->producto;
        if ($producto === null) {
            throw new DomainException('La variante no tiene producto asociado.');
        }
        $unidad = $variante->unidadMedida ?? $producto->unidadMedida;

        return $this->medidas->resolver((int) ($unidad->id ?? 0), (string) ($unidad->nombre ?? ''), (string) ($unidad->codigo ?? ''), (float) ($variante->volumen ?? 0), (float) ($variante->peso ?? 0), is_numeric($variante->getAttribute('unidades_por_empaque')) ? (float) $variante->getAttribute('unidades_por_empaque') : 1.0);
    }
}
