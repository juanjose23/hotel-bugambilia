<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Recepciones\Mutations;

use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Ubicacion;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\Stock;
use App\UseCases\Inventario\Services\PutawayPolicy;

class RegistrarEntradaRecepcion
{
    /**
     * @param  array<int, array{id: int, producto_id: int, producto_variante_id: int|null, cantidad_recibida: float, cantidad_rechazada: float, lote_proveedor: string|null, fecha_vencimiento: string|null}>  $items
     * @param  array<int, array{disponible: float, cuarentena: float}>  $decisionesDiscrepancia
     */
    public function execute(
        string $nuevoEstado,
        array $items,
        ?int $proveedorId = null,
        ?int $creadoPorId = null,
        array $decisionesDiscrepancia = [],
    ): void {
        foreach ($items as $item) {
            $cantidad = (float) $item['cantidad_recibida'];
            if ($cantidad <= 0) {
                continue;
            }

            $productoId = (int) $item['producto_id'];
            $varianteId = isset($item['producto_variante_id']) ? (int) $item['producto_variante_id'] : null;
            $fechaVenc = $item['fecha_vencimiento'] ? new \DateTimeImmutable($item['fecha_vencimiento']) : null;
            $fechaHoy = now()->toDateString();
            $codigoBase = $item['lote_proveedor'] ?: sprintf('LOTE-%d-%s', $productoId, now()->format('Ymd'));

            if ($nuevoEstado === 'ConDiscrepancia' && isset($decisionesDiscrepancia[$item['id']])) {
                $d = $decisionesDiscrepancia[$item['id']];
                if ($d['disponible'] > 0) {
                    $this->crearLote($productoId, $varianteId, $codigoBase.'-DISP', EstadoLote::Disponible, $d['disponible'], $item, $fechaVenc, $proveedorId, $fechaHoy, $creadoPorId);
                }
                if ($d['cuarentena'] > 0) {
                    $this->crearLote($productoId, $varianteId, $codigoBase.'-CUAR', EstadoLote::Cuarentena, $d['cuarentena'], $item, $fechaVenc, $proveedorId, $fechaHoy, $creadoPorId);
                }
            } elseif ($nuevoEstado === 'EnCuarentena') {
                $this->crearLote($productoId, $varianteId, $codigoBase, EstadoLote::Cuarentena, $cantidad, $item, $fechaVenc, $proveedorId, $fechaHoy, $creadoPorId);
            } else {
                $estado = in_array($nuevoEstado, ['Completa', 'Parcial']) ? EstadoLote::Disponible : EstadoLote::Cuarentena;
                $this->crearLote($productoId, $varianteId, $codigoBase, $estado, $cantidad, $item, $fechaVenc, $proveedorId, $fechaHoy, $creadoPorId);
            }
        }
    }

    /**
     * @param  array{id: int, producto_id: int, producto_variante_id: int|null, cantidad_recibida: float, cantidad_rechazada: float, lote_proveedor: string|null, fecha_vencimiento: string|null, ubicacion_id?: int|null}  $item
     */
    private function crearLote(
        int $productoId,
        ?int $varianteId,
        string $codigoLote,
        EstadoLote $estado,
        float $cantidad,
        array $item,
        ?\DateTimeImmutable $fechaVenc,
        ?int $proveedorId,
        string $fechaHoy,
        ?int $creadoPorId,
    ): void {
        $ubicacion = null;
        if (! empty($item['ubicacion_id'])) {
            $ubicacion = Ubicacion::where('estado', 1)->find($item['ubicacion_id']);
        }
        if (! $ubicacion) {
            $ubicacion = PutawayPolicy::sugerirUbicacion();
        }

        $lote = Lote::create([
            'codigo_lote' => $codigoLote,
            'producto_id' => $productoId,
            'producto_variante_id' => $varianteId,
            'estado' => $estado,
            'cantidad_disponible' => $cantidad,
            'cantidad_inicial' => $cantidad,
            'ubicacion_id' => $ubicacion->id,
            'fecha_vencimiento' => $fechaVenc?->format('Y-m-d'),
            'lote_proveedor' => $item['lote_proveedor'],
            'proveedor_id' => $proveedorId,
            'fecha_recepcion' => $fechaHoy,
            'recepcion_item_id' => (int) $item['id'],
        ]);

        // Registrar en inv_stock (Distribución física inicial en bodega)
        Stock::create([
            'producto_id' => $productoId,
            'producto_variante_id' => $varianteId,
            'lote_id' => $lote->id,
            'ubicacion_id' => $ubicacion->id,
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
            'referencia' => "Lote {$codigoLote} — ".$estado->label(),
            'creado_por_id' => $creadoPorId,
        ]);
    }
}
