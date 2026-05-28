<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MÓDULO: STOCK DE BODEGAS (inv_stock)
     *
     * Registra la existencia física de consumibles por producto, lote y bodega real.
     * Es la "foto actual" del inventario distribuido entre bodegas del hotel.
     *
     * PRINCIPIO FUNDAMENTAL:
     *   Un registro en inv_stock existe solo mientras cantidad > 0.
     *   Cuando un consumo o traslado deja la cantidad en cero, el registro
     *   se ELIMINA (DELETE) para mantener la tabla compacta y eficiente.
     *
     * DIFERENCIA CON inv_lotes:
     *   inv_lotes.cantidad_disponible = stock TOTAL del lote en todo el hotel
     *   inv_stock.cantidad = stock del lote EN UNA BODEGA ESPECÍFICA
     *
     * SOLO BODEGAS REALES:
     *   ubicacion_id apunta SOLO a ubicaciones de tipo='almacen' o tipo='zona'.
     *   Nunca a habitaciones ni áreas (esas se gestionan con inv_activo_asignaciones → ActivoAsignacion).
     */
    public function up(): void
    {
        Schema::create('inv_stock', function (Blueprint $table) {
            $table->comment('Stock actual de consumibles por producto, lote y bodega física. Reemplaza inv_stock_ubicacion del v1. Solo bodegas reales (ubicaciones tipo almacen).');
            $table->id()->comment('Identificador único autoincremental del registro de stock');
            $table->foreignId('producto_id')
                ->comment('Producto del que se registra el stock (FK → productos, cascadeOnDelete)')
                ->constrained('productos')
                ->cascadeOnDelete();
            $table->foreignId('producto_variante_id')
                ->nullable()
                ->comment('Variante específica del producto. NULL si aplica a cualquier variante. FK → producto_variantes, nullOnDelete.')
                ->constrained('producto_variantes')
                ->nullOnDelete();
            $table->foreignId('lote_id')
                ->nullable()
                ->comment('Lote al que pertenece esta existencia. NULL para stock sin lote específico. FK → inv_lotes, nullOnDelete.')
                ->constrained('inv_lotes')
                ->nullOnDelete();
            $table->foreignId('ubicacion_id')
                ->comment('Bodega física real donde está este stock (tipo=almacen). FK → ubicaciones, cascadeOnDelete.')
                ->constrained('ubicaciones')
                ->cascadeOnDelete();
            $table->decimal('cantidad', 14, 4)
                ->default(0)
                ->comment('Cantidad disponible en esta bodega. El registro se ELIMINA cuando llega a cero (no se conservan filas vacías).');
            $table->timestamps();

            $table->unique(
                ['producto_id', 'producto_variante_id', 'lote_id', 'ubicacion_id'],
                'inv_stock_unique'
            );
            $table->index(
                ['producto_id', 'ubicacion_id'],
                'inv_stock_producto_bodega_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_stock');
    }
};
