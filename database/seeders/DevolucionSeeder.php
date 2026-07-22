<?php

namespace Database\Seeders;

use App\Enums\Compras\EstadoDevolucion;
use App\Interactors\Compras\Devoluciones\DevolverMercanciaProveedor;
use App\Interactors\Compras\Devoluciones\GenerarCodigoDevolucion;
use App\Repository\Models\Compras\DevolucionCompra;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\User;
use Illuminate\Database\Seeder;

class DevolucionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        if (! $admin) {
            return;
        }

        // Encontrar una recepción que tenga lotes asociados
        $recepcion = RecepcionCompra::whereHas('items.lote')->with('items.lote')->first();
        if (! $recepcion) {
            return;
        }

        $lotes = Lote::whereHas('recepcionItem', fn ($q) => $q->where('recepcion_id', $recepcion->id))
            ->where('cantidad_disponible', '>', 5)
            ->get();

        if ($lotes->isEmpty()) {
            return;
        }

        // --- 1. Devolución en estado BORRADOR ---
        $codigoBorrador = app(GenerarCodigoDevolucion::class)->ejecutar();
        $devolucionBorrador = DevolucionCompra::create([
            'codigo' => $codigoBorrador,
            'orden_compra_id' => $recepcion->orden_compra_id,
            'recepcion_compra_id' => $recepcion->id,
            'fecha_devolucion' => now()->subDays(2),
            'estado' => EstadoDevolucion::Borrador,
            'motivo' => 'Exceso de inventario detectado en conteo posterior para control de almacén.',
            'documento_externo' => 'GUIA-RET-019',
            'creado_por_id' => $admin->id,
        ]);

        $loteBorrador = $lotes->first();
        $devolucionBorrador->items()->create([
            'lote_id' => $loteBorrador->id,
            'recepcion_item_id' => $loteBorrador->recepcion_item_id,
            'producto_id' => $loteBorrador->producto_id,
            'producto_variante_id' => $loteBorrador->producto_variante_id,
            'unidad_medida_id' => ($loteBorrador->recepcionItem ? $loteBorrador->recepcionItem->unidad_medida_id : null) ?? ($loteBorrador->producto ? $loteBorrador->producto->unidad_medida_id : null),
            'cantidad_devolver' => 1.00,
        ]);

        // --- 2. Devolución en estado CONFIRMADA (Procesada) ---
        if ($lotes->count() > 1) {
            $codigoConfirmada = app(GenerarCodigoDevolucion::class)->ejecutar();
            $devolucionConfirmada = DevolucionCompra::create([
                'codigo' => $codigoConfirmada,
                'orden_compra_id' => $recepcion->orden_compra_id,
                'recepcion_compra_id' => $recepcion->id,
                'fecha_devolucion' => now()->subDays(1),
                'estado' => EstadoDevolucion::Borrador, // Se crea en Borrador para luego confirmarla por mutation
                'motivo' => 'Devolución de lote dañado tras inspección en cuarentena.',
                'documento_externo' => 'GUIA-RET-020',
                'creado_por_id' => $admin->id,
            ]);

            $loteConfirmar = $lotes->last();
            $devolucionConfirmada->items()->create([
                'lote_id' => $loteConfirmar->id,
                'recepcion_item_id' => $loteConfirmar->recepcion_item_id,
                'producto_id' => $loteConfirmar->producto_id,
                'producto_variante_id' => $loteConfirmar->producto_variante_id,
                'unidad_medida_id' => ($loteConfirmar->recepcionItem ? $loteConfirmar->recepcionItem->unidad_medida_id : null) ?? ($loteConfirmar->producto ? $loteConfirmar->producto->unidad_medida_id : null),
                'cantidad_devolver' => 2.00,
            ]);

            // Confirmar usando el Caso de Uso UC-05
            app(DevolverMercanciaProveedor::class)->ejecutar($devolucionConfirmada, $admin->id);
        }
    }
}
