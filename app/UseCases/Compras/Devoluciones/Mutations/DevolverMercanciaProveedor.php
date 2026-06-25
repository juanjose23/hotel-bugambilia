<?php

declare(strict_types=1);

namespace App\UseCases\Compras\Devoluciones\Mutations;

use App\Enums\Compras\EstadoDevolucion;
use App\Enums\Compras\EstadoOrdenCompra;
use App\Enums\Inventario\EstadoLote;
use App\Models\Compras\DevolucionCompra;
use App\Models\Compras\RecepcionItem;
use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\Stock;
use App\Services\Compras\NotificadorCompras;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DevolverMercanciaProveedor
{
    public function __construct(
        protected NotificadorCompras $notificador
    ) {}

    /**
     * Confirma la devolución, egresa físicamente el stock del inventario y actualiza el PO.
     *
     * @throws RuntimeException
     */
    public function execute(DevolucionCompra $devolucion, int $creadoPorId): void
    {
        if ($devolucion->estado === EstadoDevolucion::Confirmada) {
            throw new RuntimeException("La devolución {$devolucion->codigo} ya está confirmada.");
        }

        DB::transaction(function () use ($devolucion, $creadoPorId) {
            $devolucion->load(['items.lote', 'items.recepcionItem.ordenItem.ordenCompra']);

            foreach ($devolucion->items as $item) {
                $lote = $item->lote;
                if (! $lote) {
                    throw new RuntimeException("El item de devolución con ID {$item->id} no tiene un lote de inventario asociado.");
                }

                // Cantidad a devolver
                $cantidadDevolver = (float) $item->cantidad_devolver;

                if ($lote->estado === EstadoLote::Rechazado) {
                    // Si ya estaba en la zona de merma como rechazado, no restamos stock (ya era 0 disponible)
                    // pero registramos el movimiento de salida física hacia el proveedor.
                    $costoUnitarioMov = $lote->costo_unitario;
                    $costoTotalMov = $costoUnitarioMov !== null
                        ? $costoUnitarioMov * $cantidadDevolver
                        : null;

                    MovimientoStock::create([
                        'tipo' => 'MOV_SALIDA',
                        'lote_id' => $lote->id,
                        'producto_id' => $item->producto_id,
                        'cantidad' => $cantidadDevolver,
                        'costo_unitario' => $costoUnitarioMov,
                        'costo_total' => $costoTotalMov,
                        'ubicacion_origen_id' => $lote->ubicacion_id, // Zona de Merma
                        'ubicacion_destino_id' => null, // Sale del almacén
                        'documento_tipo' => 'devolucion_item',
                        'documento_id' => $item->id,
                        'referencia' => "Devolución física {$devolucion->codigo}: ".$devolucion->motivo,
                        'creado_por_id' => $creadoPorId,
                        'notas' => 'Salida física de lote rechazado hacia proveedor.',
                    ]);
                } else {
                    // Si estaba Disponible, Cuarentena, etc., debemos verificar stock y descontarlo
                    if ($lote->cantidad_disponible < $cantidadDevolver) {
                        throw new RuntimeException(
                            "Stock insuficiente para devolver en el lote {$lote->codigo_lote}. ".
                            "Disponible: {$lote->cantidad_disponible}, Requerido: {$cantidadDevolver}"
                        );
                    }

                    $ubicacionOrigen = $lote->ubicacion_id;

                    // Restar stock
                    $lote->cantidad_disponible -= $cantidadDevolver;
                    if ($lote->cantidad_disponible <= 0) {
                        $lote->estado = EstadoLote::Agotado;
                    }
                    $lote->save();

                    // Restar stock en inv_stock
                    $stock = Stock::where([
                        'lote_id' => $lote->id,
                        'ubicacion_id' => $ubicacionOrigen,
                    ])->first();

                    if ($stock) {
                        $stock->cantidad -= $cantidadDevolver;
                        if ($stock->cantidad <= 0.0) {
                            $stock->delete();
                        } else {
                            $stock->save();
                        }
                    }

                    // Registrar movimiento de salida hacia proveedor
                    $costoUnitarioMov = $lote->costo_unitario;
                    $costoTotalMov = $costoUnitarioMov !== null
                        ? $costoUnitarioMov * $cantidadDevolver
                        : null;

                    MovimientoStock::create([
                        'tipo' => 'MOV_SALIDA',
                        'lote_id' => $lote->id,
                        'producto_id' => $item->producto_id,
                        'cantidad' => $cantidadDevolver,
                        'costo_unitario' => $costoUnitarioMov,
                        'costo_total' => $costoTotalMov,
                        'ubicacion_origen_id' => $ubicacionOrigen,
                        'ubicacion_destino_id' => null, // Sale del almacén
                        'documento_tipo' => 'devolucion_item',
                        'documento_id' => $item->id,
                        'referencia' => "Devolución {$devolucion->codigo}: ".$devolucion->motivo,
                        'creado_por_id' => $creadoPorId,
                        'notas' => 'Devolución de stock activo hacia proveedor.',
                    ]);
                }

                // Ajustar cantidad_recibida en el RecepcionItem para liberar saldo
                if ($item->recepcion_item_id) {
                    $recepcionItem = $item->recepcionItem;
                    if ($recepcionItem) {
                        $recepcionItem->cantidad_recibida = max(0.00, (float) $recepcionItem->cantidad_recibida - $cantidadDevolver);
                        $recepcionItem->save();

                        $ordenItem = $recepcionItem->ordenItem;
                        if ($ordenItem) {
                            // Registrar en historial de la OC
                            $ordenCompra = $ordenItem->ordenCompra;
                            if ($ordenCompra) {
                                // Agregar registro en historial de la Orden de Compra
                                if (method_exists($ordenCompra, 'historial')) {
                                    $ordenCompra->historial()->create([
                                        'estado' => $ordenCompra->estado,
                                        'notes' => "Devolución registrada para lote {$lote->codigo_lote}. Cantidad devuelta: {$cantidadDevolver}. Saldo de orden liberado.",
                                        'creado_por_id' => $creadoPorId,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            // Cambiar estado a Confirmada
            $devolucion->estado = EstadoDevolucion::Confirmada;
            $devolucion->save();

            if (method_exists($devolucion, 'recordStatusChange')) {
                $devolucion->recordStatusChange(EstadoDevolucion::Confirmada, 'Devolución confirmada, stock físico egresado y saldo liberado.');
            }

            // Actualizar estado de la Orden de Compra según lo devuelto
            $ordenCompra = $devolucion->ordenCompra;
            if ($ordenCompra) {
                $totalOrdenado = (float) $ordenCompra->items()->sum('cantidad');

                $totalDevuelto = (float) RecepcionItem::query()
                    ->whereHas('recepcion', fn ($q) => $q
                        ->where('orden_compra_id', $ordenCompra->id)
                    )
                    ->sum('cantidad_recibida');

                if ($totalOrdenado > 0) {
                    if ($totalDevuelto <= 0) {
                        // Todo fue devuelto, saldo en cero
                        $ordenCompra->update(['estado' => EstadoOrdenCompra::DevueltaTotalmente]);
                    } else {
                        // Queda saldo positivo pero ya hay devolución confirmada
                        $ordenCompra->update(['estado' => EstadoOrdenCompra::DevueltaParcialmente]);
                    }
                }
            }
        });

        // Notificar
        $this->notificador->devolucionConfirmada($devolucion);
    }
}
