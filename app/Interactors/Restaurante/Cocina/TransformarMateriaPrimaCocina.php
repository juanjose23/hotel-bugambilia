<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Restaurante\TransformacionMateriaPrima;
use App\Repository\Models\Shared\Stock;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final class TransformarMateriaPrimaCocina
{
    public function __construct(
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function ejecutar(array $data, ?int $usuarioId = null): TransformacionMateriaPrima
    {
        return DB::transaction(function () use ($data, $usuarioId): TransformacionMateriaPrima {
            $productoOrigenId = is_numeric($data['producto_origen_id'] ?? null) ? (int) $data['producto_origen_id'] : 0;
            $varianteOrigenId = is_numeric($data['variante_origen_id'] ?? null) ? (int) $data['variante_origen_id'] : 0;
            $ubicacionOrigenId = is_numeric($data['ubicacion_origen_id'] ?? null) ? (int) $data['ubicacion_origen_id'] : 0;
            $cantidadProcesada = is_numeric($data['cantidad_procesada'] ?? null) ? (float) $data['cantidad_procesada'] : 0.0;
            $observaciones = is_string($data['observaciones'] ?? null) ? $data['observaciones'] : null;
            $items = is_array($data['items'] ?? null) ? $data['items'] : [];

            if ($productoOrigenId <= 0 || $varianteOrigenId <= 0 || $ubicacionOrigenId <= 0) {
                throw new DomainException('Debe seleccionar producto, variante y ubicación origen.');
            }

            if ($cantidadProcesada <= 0) {
                throw new DomainException('La cantidad procesada debe ser mayor que cero.');
            }

            if ($items === []) {
                throw new DomainException('Debe registrar al menos una materia prima obtenida o una merma.');
            }

            $transformacion = TransformacionMateriaPrima::query()->create([
                'codigo' => 'TMP-'.now()->format('Ymd-His'),
                'producto_origen_id' => $productoOrigenId,
                'variante_origen_id' => $varianteOrigenId,
                'ubicacion_origen_id' => $ubicacionOrigenId,
                'cantidad_procesada' => $cantidadProcesada,
                'costo_total' => 0,
                'realizado_por' => $usuarioId,
                'observaciones' => $observaciones,
            ]);

            $this->descontarOrigen($transformacion, $ubicacionOrigenId, $productoOrigenId, $varianteOrigenId, $cantidadProcesada, $usuarioId);

            $costoTotal = 0.0;
            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $productoDestinoId = is_numeric($item['producto_destino_id'] ?? null) ? (int) $item['producto_destino_id'] : 0;
                $varianteDestinoId = is_numeric($item['variante_destino_id'] ?? null) ? (int) $item['variante_destino_id'] : null;
                $ubicacionDestinoId = is_numeric($item['ubicacion_destino_id'] ?? null) ? (int) $item['ubicacion_destino_id'] : $ubicacionOrigenId;
                $cantidad = is_numeric($item['cantidad'] ?? null) ? (float) $item['cantidad'] : 0.0;
                $costoAsignado = is_numeric($item['costo_asignado'] ?? null) ? (float) $item['costo_asignado'] : 0.0;
                $esMerma = ! empty($item['es_merma']);
                $itemObservaciones = is_string($item['observaciones'] ?? null) ? $item['observaciones'] : null;

                if ($productoDestinoId <= 0 || $cantidad <= 0) {
                    continue;
                }

                if (! $esMerma && ($varianteDestinoId === null || $varianteDestinoId <= 0)) {
                    throw new DomainException('Cada materia prima obtenida debe tener variante destino.');
                }

                $transformacion->items()->create([
                    'producto_destino_id' => $productoDestinoId,
                    'variante_destino_id' => $varianteDestinoId,
                    'ubicacion_destino_id' => $ubicacionDestinoId,
                    'cantidad' => $cantidad,
                    'costo_asignado' => $costoAsignado,
                    'es_merma' => $esMerma,
                    'observaciones' => $itemObservaciones,
                ]);

                $costoTotal += $costoAsignado;

                if ($esMerma) {
                    $this->registrarMerma($transformacion, $productoDestinoId, $cantidad, $ubicacionOrigenId, $costoAsignado, $usuarioId);
                } else {
                    $this->registrarMateriaPrima($transformacion, $productoDestinoId, (int) $varianteDestinoId, $cantidad, $ubicacionDestinoId, $costoAsignado, $usuarioId);
                }
            }

            $transformacion->update(['costo_total' => round($costoTotal, 2)]);

            return $transformacion->refresh();
        });
    }

    private function descontarOrigen(TransformacionMateriaPrima $transformacion, int $ubicacionId, int $productoId, int $varianteId, float $cantidad, ?int $usuarioId): void
    {
        $stock = $this->repositorio->obtenerStockPorVariante($ubicacionId, $varianteId);
        $disponible = $stock instanceof Stock ? (float) $stock->cantidad_actual : 0.0;

        if (! $stock instanceof Stock || $disponible < $cantidad) {
            throw new DomainException("Stock insuficiente para transformar {$this->nombreVariante($varianteId)}. Disponible: {$disponible}; requerido: {$cantidad}.");
        }

        $stock->cantidad_actual -= $cantidad;
        $this->repositorio->guardarStock($stock);

        $this->repositorio->registrarMovimiento([
            'tipo' => 'TRANSFORMACION_SALIDA',
            'lote_id' => $stock->lote_id,
            'producto_id' => $productoId,
            'cantidad' => -$cantidad,
            'costo_unitario' => $stock->lote?->costo_unitario,
            'costo_total' => $stock->lote?->costo_unitario !== null ? (float) $stock->lote->costo_unitario * $cantidad : null,
            'ubicacion_origen_id' => $ubicacionId,
            'documento_tipo' => 'transformacion_materia_prima',
            'documento_id' => $transformacion->id,
            'referencia' => "Salida por transformación {$transformacion->codigo}",
            'creado_por_id' => $usuarioId,
        ]);
    }

    private function registrarMateriaPrima(TransformacionMateriaPrima $transformacion, int $productoId, int $varianteId, float $cantidad, int $ubicacionId, float $costoAsignado, ?int $usuarioId): void
    {
        $costoUnitario = $cantidad > 0 ? round($costoAsignado / $cantidad, 6) : 0.0;

        $lote = Lote::query()->create([
            'producto_id' => $productoId,
            'producto_variante_id' => $varianteId,
            'ubicacion_id' => $ubicacionId,
            'codigo_lote' => 'LOTE-TMP-'.strtoupper(substr(md5(uniqid()), 0, 8)),
            'cantidad_inicial' => $cantidad,
            'cantidad_disponible' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'costo_total' => $costoAsignado,
            'estado' => EstadoLote::Disponible,
            'fecha_recepcion' => now()->toDateString(),
        ]);

        $stock = $this->repositorio->obtenerStockPorVariante($ubicacionId, $varianteId);
        if (! $stock instanceof Stock) {
            $stock = Stock::query()->create([
                'stockable_type' => Ubicacion::class,
                'stockable_id' => $ubicacionId,
                'producto_variante_id' => $varianteId,
                'lote_id' => $lote->id,
                'cantidad_ideal' => 0,
                'cantidad_actual' => 0,
            ]);
        } else {
            $stock->lote_id = $lote->id > 0 ? $lote->id : null;
        }

        $stock->cantidad_actual += $cantidad;
        $this->repositorio->guardarStock($stock);

        $this->repositorio->registrarMovimiento([
            'tipo' => 'TRANSFORMACION_ENTRADA',
            'lote_id' => $lote->id,
            'producto_id' => $productoId,
            'cantidad' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'costo_total' => $costoAsignado > 0 ? $costoAsignado : null,
            'ubicacion_destino_id' => $ubicacionId,
            'documento_tipo' => 'transformacion_materia_prima',
            'documento_id' => $transformacion->id,
            'referencia' => "Materia prima creada por {$transformacion->codigo}",
            'creado_por_id' => $usuarioId,
        ]);
    }

    private function registrarMerma(TransformacionMateriaPrima $transformacion, int $productoId, float $cantidad, int $ubicacionId, float $costoAsignado, ?int $usuarioId): void
    {
        $this->repositorio->registrarMovimiento([
            'tipo' => 'MERMA_COCINA',
            'producto_id' => $productoId,
            'cantidad' => -$cantidad,
            'costo_unitario' => $cantidad > 0 ? round($costoAsignado / $cantidad, 6) : null,
            'costo_total' => $costoAsignado > 0 ? $costoAsignado : null,
            'ubicacion_origen_id' => $ubicacionId,
            'documento_tipo' => 'transformacion_materia_prima',
            'documento_id' => $transformacion->id,
            'referencia' => "Merma final de {$transformacion->codigo}",
            'creado_por_id' => $usuarioId,
        ]);
    }

    private function nombreVariante(int $varianteId): string
    {
        $variante = ProductoVariante::query()->with('producto')->find($varianteId);

        if (! $variante instanceof ProductoVariante) {
            return "variante {$varianteId}";
        }

        return trim(($variante->producto?->nombre ? "{$variante->producto->nombre} - " : '').($variante->nombre_variante ?: $variante->codigo));
    }
}
