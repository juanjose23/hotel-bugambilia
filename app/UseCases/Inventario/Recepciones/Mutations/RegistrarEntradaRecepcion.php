<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Recepciones\Mutations;

use App\Enums\Activos\EstadoIndividualizacion;
use App\Enums\Inventario\EstadoLote;
use App\Models\Activos\RegistroIndividualizacion;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use App\Models\Catalogos\Ubicacion;
use App\Models\Compras\RecepcionItem;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\Stock;
use App\UseCases\Activos\Mutations\Gestion\IndividualizarActivos;
use App\UseCases\Inventario\Services\PutawayPolicy;

class RegistrarEntradaRecepcion
{
    public function __construct(
        private readonly IndividualizarActivos $individualizarActivos,
    ) {}

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
            $producto = Producto::find($productoId);
            if ($producto && $producto->tipo === 3) {
                $registro = RegistroIndividualizacion::firstOrCreate(
                    ['recepcion_item_id' => (int) $item['id']],
                    [
                        'producto_id' => $productoId,
                        'producto_variante_id' => isset($item['producto_variante_id']) ? (int) $item['producto_variante_id'] : null,
                        'cantidad_total' => (int) $cantidad,
                        'cantidad_registrada' => 0,
                        'estado' => EstadoIndividualizacion::Pendiente,
                        'registrado_por_id' => $creadoPorId,
                    ]
                );

                if ($registro->wasRecentlyCreated || $registro->estado === EstadoIndividualizacion::Pendiente) {
                    $this->individualizarAutomaticamente($registro, (int) $cantidad, $creadoPorId);
                }

                continue;
            }

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

        $costoUnitario = null;
        $costoTotal = null;

        $recepcionItem = RecepcionItem::with(['ordenItem.ordenCompra', 'variante'])->find((int) $item['id']);
        if ($recepcionItem && $recepcionItem->ordenItem) {
            $precioUnitario = (float) $recepcionItem->ordenItem->precio_unitario;
            $tasaCambio = (float) ($recepcionItem->ordenItem->ordenCompra->tasa_cambio ?? 1.0);
            $precioConvertido = $precioUnitario * $tasaCambio;

            $unidadesPorEmpaque = 1.0;
            if ($varianteId) {
                $variante = $recepcionItem->variante ?? ProductoVariante::find($varianteId);
                $unidadesPorEmpaque = (float) ($variante->unidades_por_empaque ?? 1.0);
            }

            $costoUnitario = $precioConvertido / max($unidadesPorEmpaque, 1.0);
            $costoTotal = $costoUnitario * $cantidad;
        }

        $lote = Lote::create([
            'codigo_lote' => $codigoLote,
            'producto_id' => $productoId,
            'producto_variante_id' => $varianteId,
            'estado' => $estado,
            'cantidad_disponible' => $cantidad,
            'cantidad_inicial' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'costo_total' => $costoTotal,
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

    private function individualizarAutomaticamente(RegistroIndividualizacion $registro, int $cantidad, ?int $creadoPorId): void
    {
        if ($cantidad <= 0) {
            return;
        }

        $items = array_fill(0, $cantidad, [
            'numero_serie' => null,
            'nombre_descriptivo' => null,
            'notas' => null,
        ]);

        $this->individualizarActivos->execute(
            registroId: $registro->id,
            items: $items,
            userId: $creadoPorId ?? 1,
        );
    }
}
