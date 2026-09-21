<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Servicios;

use App\BusinessLogic\Inventario\Estrategias\PutawayPolicy;
use App\BusinessLogic\Inventario\Validacion\ReglasLotesRecepcion;
use App\Enums\Inventario\EstadoLote;
use App\Repository\Persistencia\Catalogos\ProductoRepositorioInterface;
use App\Repository\Persistencia\Catalogos\UbicacionRepositorioInterface;
use App\Repository\Persistencia\Compras\RecepcionRepositorioInterface;
use App\Repository\Persistencia\Inventario\LoteRepositorioInterface;
use App\Repository\Persistencia\Inventario\MovimientoStockRepositorioInterface;
use App\Repository\Persistencia\Inventario\StockRepositorioInterface;

final readonly class CreadorLoteRecepcion
{
    public function __construct(
        private LoteRepositorioInterface $loteRepositorio,
        private StockRepositorioInterface $stockRepositorio,
        private MovimientoStockRepositorioInterface $movimientoStockRepositorio,
        private UbicacionRepositorioInterface $ubicacionRepositorio,
        private RecepcionRepositorioInterface $recepcionRepositorio,
        private ProductoRepositorioInterface $productoRepositorio,
        private PutawayPolicy $putawayPolicy,
        private ReglasLotesRecepcion $reglas,
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
            $ubicacion = $this->ubicacionRepositorio->buscarPorId((int) $item['ubicacion_id']);
        }
        if (! $ubicacion) {
            $ubicacion = $this->putawayPolicy->sugerirUbicacion();
        }

        $ubicacionDetalle = null;
        if (! empty($item['ubicacion_detalle_id'])) {
            $ubicacionDetalle = $this->ubicacionRepositorio->buscarPorId((int) $item['ubicacion_detalle_id']);
        }
        if (! $ubicacionDetalle) {
            $ubicacionDetalle = $this->putawayPolicy->sugerirSubUbicacion($ubicacion);
        }

        $costoUnitario = null;
        $costoTotal = null;

        $recepcionItem = $this->recepcionRepositorio->buscarItemPorId((int) $item['id']);
        if ($recepcionItem && $recepcionItem->ordenItem) {
            $precioUnitario = (float) $recepcionItem->ordenItem->precio_unitario;
            $tasaCambio = (float) ($recepcionItem->ordenItem->ordenCompra->tasa_cambio ?? 1.0);

            $unidadesPorEmpaque = 1.0;
            if ($varianteId) {
                $variante = $recepcionItem->variante ?? $this->productoRepositorio->buscarVariantePorId($varianteId);
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

        $this->stockRepositorio->crear([
            'producto_id' => $productoId,
            'producto_variante_id' => $varianteId,
            'lote_id' => $lote->id,
            'ubicacion_id' => $ubicacion->id,
            'ubicacion_detalle_id' => $ubicacionDetalle?->id,
            'cantidad' => $cantidad,
        ]);

        $this->movimientoStockRepositorio->registrar([
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
