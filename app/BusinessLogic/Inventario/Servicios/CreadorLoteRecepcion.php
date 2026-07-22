<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Servicios;

use App\BusinessLogic\Inventario\Estrategias\PutawayPolicy;
use App\BusinessLogic\Inventario\Validacion\ReglasLotesRecepcion;
use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Compras\RecepcionItem;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Persistencia\Inventario\LoteRepositorioInterface;

readonly class CreadorLoteRecepcion
{
    public function __construct(
        private LoteRepositorioInterface $loteRepositorio,
        private ReglasLotesRecepcion $reglas
    ) {}

    /**
     * @param  array{id: int, lote_proveedor?: string|null, ubicacion_id?: int|null, ubicacion_detalle_id?: int|null, producto_id?: int, producto_variante_id?: int|null, cantidad_recibida?: float, fecha_vencimiento?: string|null}  $item
     */
    public function execute(
        int $productoId,
        ?int $varianteId,
        string $codigoLote,
        EstadoLote $estado,
        float $cantidad,
        array $item,
        ?\DateTimeImmutable $fechaVenc,
        ?int $proveedorId,
        string $fechaHoy,
        ?int $creadoPorId
    ): void {
        $ubicacion = null;
        if (! empty($item['ubicacion_id'])) {
            $found = Ubicacion::where('estado', 1)->find($item['ubicacion_id']);
            if ($found instanceof Ubicacion) {
                $ubicacion = $found;
            }
        }
        if (! $ubicacion) {
            $ubicacion = PutawayPolicy::sugerirUbicacion();
        }

        $ubicacionDetalle = null;
        if (! empty($item['ubicacion_detalle_id'])) {
            $foundDetalle = Ubicacion::where('estado', 1)->find($item['ubicacion_detalle_id']);
            if ($foundDetalle instanceof Ubicacion) {
                $ubicacionDetalle = $foundDetalle;
            }
        }
        if (! $ubicacionDetalle) {
            $ubicacionDetalle = PutawayPolicy::sugerirSubUbicacion($ubicacion);
        }

        $costoUnitario = null;
        $costoTotal = null;

        $recepcionItem = RecepcionItem::with(['ordenItem.ordenCompra', 'variante'])->find((int) $item['id']);
        if ($recepcionItem && $recepcionItem->ordenItem) {
            $precioUnitario = (float) $recepcionItem->ordenItem->precio_unitario;
            $tasaCambio = (float) ($recepcionItem->ordenItem->ordenCompra->tasa_cambio ?? 1.0);

            $unidadesPorEmpaque = 1.0;
            if ($varianteId) {
                $variante = $recepcionItem->variante ?? ProductoVariante::find($varianteId);
                $unidadesPorEmpaque = (float) ($variante->unidades_por_empaque ?? 1.0);
            }

            $costos = $this->reglas->calcularCostos($precioUnitario, $tasaCambio, $unidadesPorEmpaque, $cantidad);
            $costoUnitario = $costos['costo_unitario'];
            $costoTotal = $costos['costo_total'];
        }

        /** @var array<string, mixed> $datos */
        $datos = [
            'codigo_lote' => $codigoLote,
            'producto_id' => $productoId,
            'producto_variante_id' => $varianteId,
            'estado' => $estado,
            'cantidad_disponible' => $cantidad,
            'cantidad_inicial' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'costo_total' => $costoTotal,
            'ubicacion_id' => $ubicacion->id,
            'ubicacion_detalle_id' => $ubicacionDetalle?->id,
            'fecha_vencimiento' => $fechaVenc?->format('Y-m-d'),
            'lote_proveedor' => $item['lote_proveedor'] ?? null,
            'proveedor_id' => $proveedorId,
            'fecha_recepcion' => $fechaHoy,
            'recepcion_item_id' => (int) $item['id'],
        ];
        $lote = $this->loteRepositorio->crear($datos);

        Stock::create([
            'producto_id' => $productoId,
            'producto_variante_id' => $varianteId,
            'lote_id' => $lote->id,
            'ubicacion_id' => $ubicacion->id,
            'ubicacion_detalle_id' => $ubicacionDetalle?->id,
            'cantidad' => $cantidad,
        ]);

        MovimientoStock::create([
            'tipo' => 'MOV_ENTRADA',
            'lote_id' => $lote->id,
            'producto_id' => $productoId,
            'cantidad' => $cantidad,
            'ubicacion_origen_id' => null,
            'ubicacion_destino_id' => $ubicacion->id,
            'documento_tipo' => 'recepcion_item',
            'documento_id' => (int) $item['id'],
            'referencia' => "Lote $codigoLote — ".$estado->label(),
            'creado_por_id' => $creadoPorId,
        ]);
    }
}
