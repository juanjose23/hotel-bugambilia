<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\BusinessLogic\Restaurante\Cocina\MedidasCocina;
use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Restaurante\TransformacionMateriaPrima;
use App\Repository\Persistencia\Restaurante\ExistenciasCocinaRepositorio;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class TransformarMateriaPrimaCocina
{
    public function __construct(private RestauranteRepositorioInterface $repositorio, private ExistenciasCocinaRepositorio $existencias, private ConsumirExistenciasCocina $consumir, private MedidasCocina $medidas) {}

    /** @param array<string, mixed> $data */
    public function ejecutar(array $data, ?int $usuarioId = null): TransformacionMateriaPrima
    {
        return DB::transaction(function () use ($data, $usuarioId): TransformacionMateriaPrima {
            $productoId = $this->entero($data['producto_origen_id'] ?? 0);
            $varianteId = $this->entero($data['variante_origen_id'] ?? 0);
            $ubicacionId = $this->entero($data['ubicacion_origen_id'] ?? 0);
            $cantidad = is_numeric($data['cantidad_procesada'] ?? null) ? (float) $data['cantidad_procesada'] : 0.0;
            $variante = $this->repositorio->obtenerVarianteConProducto($varianteId);
            if ($variante === null || $variante->producto_id !== $productoId || $this->repositorio->obtenerUbicacionPorId($ubicacionId) === null || $cantidad <= 0 || ! is_finite($cantidad)) {
                throw new DomainException('Seleccione producto, variante, ubicación y cantidad origen válidos.');
            }
            $origen = $this->unidad($variante);
            $entradaBase = $cantidad * $origen['factor'];
            $itemsRaw = $data['items'] ?? [];
            if (! is_array($itemsRaw) || $itemsRaw === []) {
                throw new DomainException('Debe registrar al menos un resultado o merma.');
            }
            $items = [];
            $salidaBase = 0.0;
            foreach ($itemsRaw as $raw) {
                if (! is_array($raw)) {
                    throw new DomainException('Resultado de transformación inválido.');
                }
                $producto = $this->repositorio->obtenerProductoPorId($this->entero($raw['producto_destino_id'] ?? 0));
                $destino = $this->repositorio->obtenerVarianteConProducto($this->entero($raw['variante_destino_id'] ?? 0));
                $esMerma = (bool) ($raw['es_merma'] ?? false);
                $cantidadSalida = is_numeric($raw['cantidad'] ?? null) ? (float) $raw['cantidad'] : 0.0;
                $ubicacionSalida = $this->entero($raw['ubicacion_destino_id'] ?? $ubicacionId);
                if (! $producto instanceof Producto || $cantidadSalida <= 0 || ! is_finite($cantidadSalida) || (! $esMerma && ($destino === null || $destino->producto_id !== $producto->id)) || (! $esMerma && $this->repositorio->obtenerUbicacionPorId($ubicacionSalida) === null)) {
                    throw new DomainException('Cada resultado debe tener producto, variante, ubicación y cantidad válidos.');
                }
                $unidad = $destino !== null ? $this->unidad($destino) : $this->unidadProducto($producto);
                if ($unidad['dimension'] !== $origen['dimension']) {
                    throw new DomainException('Configure unidades compatibles antes de transformar.');
                }
                $base = $cantidadSalida * $unidad['factor'];
                $salidaBase += $base;
                $items[] = ['producto_destino_id' => $producto->id, 'variante_destino_id' => $destino?->id, 'ubicacion_destino_id' => $ubicacionSalida, 'cantidad' => $cantidadSalida, 'es_merma' => $esMerma, 'base' => $base, 'observaciones' => is_string($raw['observaciones'] ?? null) ? $raw['observaciones'] : null];
            }
            if ($salidaBase > $entradaBase + 0.0001) {
                throw new DomainException('El rendimiento supera la materia prima disponible; revise unidades y cantidades.');
            }
            if ($entradaBase - $salidaBase > 0.0001) {
                $items[] = ['producto_destino_id' => $productoId, 'variante_destino_id' => null, 'ubicacion_destino_id' => $ubicacionId, 'cantidad' => round(($entradaBase - $salidaBase) / $origen['factor'], 4), 'es_merma' => true, 'base' => $entradaBase - $salidaBase, 'observaciones' => 'Pérdida de rendimiento de la transformación.'];
            }
            $transformacion = $this->repositorio->crearTransformacionMateriaPrima([
                'codigo' => is_string($data['codigo'] ?? null) ? $data['codigo'] : 'TMP-'.Str::upper(Str::random(16)),
                'producto_origen_id' => $productoId, 'variante_origen_id' => $varianteId, 'ubicacion_origen_id' => $ubicacionId,
                'cantidad_procesada' => $cantidad, 'costo_total' => 0, 'realizado_por' => $usuarioId,
                'observaciones' => is_string($data['observaciones'] ?? null) ? $data['observaciones'] : null,
            ]);
            $consumos = $this->consumir->ejecutar($this->existencias->porVariante($varianteId, $ubicacionId, true), $cantidad, [
                'tipo' => 'TRANSFORMACION_SALIDA', 'documento_tipo' => 'transformacion_materia_prima', 'documento_id' => $transformacion->id,
                'referencia' => 'Salida por transformación '.$transformacion->codigo, 'creado_por_id' => $usuarioId,
            ]);
            $costo = round(array_sum(array_column($consumos, 'costo_total')), 2);
            if ($costo < 0) {
                throw new DomainException('El costo de origen debe conciliarse antes de transformar.');
            }
            $fechas = array_values(array_filter(array_column($consumos, 'vencimiento')));
            sort($fechas);
            $vencimiento = $fechas[0] ?? null;
            if (is_string($data['fecha_vencimiento'] ?? null)) {
                $fechaSolicitada = Carbon::parse($data['fecha_vencimiento'])->toDateString();
                if ($fechaSolicitada < now()->toDateString() || ($vencimiento !== null && $fechaSolicitada > $vencimiento)) {
                    throw new DomainException('La vigencia de salida no puede ampliar la del origen ni estar vencida.');
                }
                $vencimiento = $fechaSolicitada;
            }
            $util = array_sum(array_map(fn (array $i): float => $i['es_merma'] ? 0.0 : $i['base'], $items));
            $indices = array_keys(array_filter($items, fn (array $i): bool => $util > 0 ? ! $i['es_merma'] : true));
            $ultimo = end($indices);
            $asignado = 0.0;
            foreach ($items as $index => $item) {
                $baseAsignacion = $util > 0 ? $util : $entradaBase;
                $importe = $util > 0 && $item['es_merma'] ? 0.0 : min(round($costo * $item['base'] / $baseAsignacion, 2), round($costo - $asignado, 2));
                if ($index === $ultimo) {
                    $importe = round($costo - $asignado, 2);
                }
                $asignado += $importe;
                unset($item['base']);
                $item['costo_asignado'] = $importe;
                $this->repositorio->crearTransformacionItem($transformacion, $item);
                if ($item['es_merma']) {
                    $this->repositorio->registrarMovimiento(['tipo' => 'MERMA_COCINA', 'producto_id' => $item['producto_destino_id'], 'cantidad' => -$item['cantidad'], 'costo_total' => $importe, 'ubicacion_origen_id' => $ubicacionId, 'documento_tipo' => 'transformacion_materia_prima', 'documento_id' => $transformacion->id, 'referencia' => 'Pérdida incluida en transformación '.$transformacion->codigo, 'creado_por_id' => $usuarioId]);

                    continue;
                }
                $lote = $this->repositorio->crearLote(['producto_id' => $item['producto_destino_id'], 'producto_variante_id' => $item['variante_destino_id'], 'ubicacion_id' => $item['ubicacion_destino_id'], 'codigo_lote' => 'TMP-'.Str::upper(Str::random(16)), 'cantidad_inicial' => $item['cantidad'], 'cantidad_disponible' => $item['cantidad'], 'costo_unitario' => round($importe / $item['cantidad'], 6), 'costo_total' => $importe, 'estado' => EstadoLote::Disponible, 'fecha_recepcion' => now()->toDateString(), 'fecha_vencimiento' => $vencimiento]);
                $this->repositorio->crearStock(['stockable_type' => Ubicacion::class, 'stockable_id' => $item['ubicacion_destino_id'], 'producto_id' => $item['producto_destino_id'], 'producto_variante_id' => $item['variante_destino_id'], 'lote_id' => $lote->id, 'cantidad_ideal' => 0, 'cantidad_actual' => $item['cantidad']]);
                $this->repositorio->registrarMovimiento(['tipo' => 'TRANSFORMACION_ENTRADA', 'lote_id' => $lote->id, 'producto_id' => $item['producto_destino_id'], 'cantidad' => $item['cantidad'], 'costo_unitario' => $lote->costo_unitario, 'costo_total' => $importe, 'ubicacion_destino_id' => $item['ubicacion_destino_id'], 'documento_tipo' => 'transformacion_materia_prima', 'documento_id' => $transformacion->id, 'referencia' => 'Resultado de '.$transformacion->codigo, 'creado_por_id' => $usuarioId]);
            }
            $this->repositorio->actualizarTransformacionMateriaPrima($transformacion, ['costo_total' => $costo]);

            return $transformacion->refresh();
        });
    }

    private function entero(mixed $valor): int
    {
        return is_numeric($valor) ? (int) $valor : 0;
    }

    /** @return array{dimension: string, factor: float} */
    private function unidad(ProductoVariante $variante): array
    {
        $unidad = $variante->unidadMedida ?? $variante->producto?->unidadMedida;

        return $this->medidas->resolver((int) ($unidad->id ?? 0), (string) ($unidad->nombre ?? ''), (string) ($unidad->codigo ?? ''), (float) ($variante->volumen ?? 0), (float) ($variante->peso ?? 0), is_numeric($variante->getAttribute('unidades_por_empaque')) ? (float) $variante->getAttribute('unidades_por_empaque') : 1.0);
    }

    /** @return array{dimension: string, factor: float} */
    private function unidadProducto(Producto $producto): array
    {
        $unidad = $producto->unidadMedida;

        return $this->medidas->resolver((int) ($unidad->id ?? 0), (string) ($unidad->nombre ?? ''), (string) ($unidad->codigo ?? ''));
    }
}
