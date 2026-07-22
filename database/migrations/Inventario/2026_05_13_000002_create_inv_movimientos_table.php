<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MÓDULO: MOVIMIENTOS DE INVENTARIO (inv_movimientos)
     *
     * Bitácora histórica e INMUTABLE de todas las transacciones de inventario.
     * Cada cambio de stock (entrada, salida, traslado, ajuste) DEBE registrar
     * un movimiento aquí. Nunca se modifican ni se borran estos registros.
     *
     * TIPOS DE MOVIMIENTO (campo `tipo`):
     *   MOV_ENTRADA       - Ingreso por recepción de compra
     *   TRASLADO          - Transferencia entre bodegas físicas
     *   SALIDA_DOTACION   - Consumo por PrepararEspacio (plantilla)
     *   REPOSICION_DIARIA - Consumo por ReponerEspacio (diario)
     *   DEVOLUCION_BODEGA - Regreso de consumibles a bodega de piso
     *   MOV_TRANSFERENCIA - Traslado de cuarentena a almacén al liberar lote
     *   MOV_AJUSTE        - Ajuste por auditoría física
     *   AJUSTE_ENTRADA    - Excedente detectado en toma física
     *   AJUSTE_SALIDA     - Faltante detectado en toma física
     *   BAJA_CADUCIDAD    - Lote vencido enviado a merma automáticamente
     *   BAJA_CALIDAD      - Lote rechazado por inspección de calidad
     *   DEVOLUCION_PROVEEDOR - Devolución al proveedor
     *   CONSUMO           - Consumo general sin categoría específica
     */
    public function up(): void
    {
        Schema::create('inv_movimientos', function (Blueprint $table) {
            $table->comment('Bitácora inmutable de todas las transacciones de inventario (entradas, salidas, traslados, ajustes). Nunca se modifica ni borra.');
            $table->id()->comment('Identificador único autoincremental del movimiento de inventario');
            $table->string('tipo', 40)
                ->comment('Tipo de movimiento. Ver lista completa de tipos en FUNCIONALIDADES.md y en el docblock de esta migración.');
            $table->foreignId('lote_id')
                ->nullable()
                ->comment('Lote de inventario afectado por el movimiento. NULL para movimientos sin lote específico. FK → inv_lotes, nullOnDelete.')
                ->constrained('inv_lotes')
                ->nullOnDelete();
            $table->foreignId('producto_id')
                ->comment('Producto afectado por el movimiento (FK → productos, cascadeOnDelete)')
                ->constrained('productos');
            $table->decimal('cantidad', 14, 4)
                ->comment('Cantidad transaccionada. Negativa para salidas, positiva para entradas.');
            $table->decimal('costo_unitario', 14, 6)->nullable()->after('cantidad')->comment('Costo unitario del producto al momento del movimiento');
            $table->decimal('costo_total', 14, 2)->nullable()->after('costo_unitario')->comment('Costo total del movimiento (cantidad * costo_unitario)');
            $table->foreignId('ubicacion_origen_id')
                ->nullable()
                ->comment('Bodega de origen del movimiento. NULL si es ingreso externo (recepción de compra). FK → ubicaciones, nullOnDelete.')
                ->constrained('ubicaciones')
                ->nullOnDelete();
            $table->foreignId('ubicacion_destino_id')
                ->nullable()
                ->comment('Bodega de destino del movimiento. NULL si es salida o consumo final. FK → ubicaciones, nullOnDelete.')
                ->constrained('ubicaciones')
                ->nullOnDelete();
            $table->string('documento_tipo', 50)
                ->nullable()
                ->comment('Tipo de documento soporte (ej: recepcion_item, reposicion, devolucion, inventario_fisico)');
            $table->unsignedBigInteger('documento_id')
                ->nullable()
                ->comment('ID del documento soporte de este movimiento');
            $table->string('referencia', 255)
                ->nullable()
                ->comment('Glosa descriptiva del movimiento (ej: "Lote LOTE-5-20260520 — Disponible")');
            $table->foreignId('creado_por_id')
                ->nullable()
                ->comment('Usuario responsable de registrar el movimiento. FK → users, nullOnDelete.')
                ->constrained('users')
                ->nullOnDelete();
            $table->text('notas')
                ->nullable()
                ->comment('Observaciones adicionales, motivo del ajuste o notas de auditoría');
            $table->timestamp('created_at')
                ->useCurrent()
                ->comment('Marca de tiempo inmutable del movimiento. No tiene updated_at (el modelo usa timestamps = false).');

            $table->index(['producto_id', 'created_at'], 'inv_movimientos_producto_fecha_index');
            $table->index('lote_id');
            $table->index('ubicacion_origen_id');
            $table->index('ubicacion_destino_id');
            $table->index('creado_por_id');
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::table('inv_movimientos', function (Blueprint $t) {
            $t->dropIndex(['lote_id']);
            $t->dropIndex(['ubicacion_origen_id']);
            $t->dropIndex(['ubicacion_destino_id']);
            $t->dropIndex(['creado_por_id']);
            $t->dropIndex(['tipo']);
            $t->dropColumn(['costo_unitario', 'costo_total']);
        });
        Schema::dropIfExists('inv_movimientos');
    }
};
