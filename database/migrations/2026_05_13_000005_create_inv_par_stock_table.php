<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MÓDULO: PAR STOCK — Límites de Reabastecimiento (inv_par_stock) v2.1
     *
     * Define los límites mínimos y objetivos de stock para cada producto
     * en cada bodega real. Cuando el stock cae por debajo del mínimo,
     * GenerarReposicionesBodega genera automáticamente una orden de reposición.
     *
     * CAMBIO CLAVE v1 → v2.1:
     *   v1 usaba campo polimórfico: ambito (H/S/U/M) + ambito_id
     *   v2.1 usa directamente: ubicacion_id → una bodega física real
     *
     * FÓRMULA DE REPOSICIÓN:
     *   Si SUM(inv_stock.cantidad WHERE producto_id=X AND ubicacion_id=Y) < stock_minimo:
     *     cantidad_a_reponer = stock_objetivo - stock_actual
     */
    public function up(): void
    {
        Schema::create('inv_par_stock', function (Blueprint $table) {
            $table->comment('Límites de PAR stock por producto y bodega. Cuando el stock baja del mínimo, GenerarReposicionesBodega crea una orden de reposición automática.');
            $table->id()->comment('Identificador único autoincremental de la regla PAR');
            $table->foreignId('producto_id')
                ->comment('Producto al que aplica esta regla PAR (FK → productos, cascadeOnDelete)')
                ->constrained('productos')
                ->cascadeOnDelete();
            $table->foreignId('producto_variante_id')
                ->nullable()
                ->comment('Variante específica. NULL = aplica a todas las variantes. FK → producto_variantes, nullOnDelete.')
                ->constrained('producto_variantes')
                ->nullOnDelete();
            $table->foreignId('ubicacion_id')
                ->comment('Bodega real donde se controla el PAR Stock (tipo almacen). FK → ubicaciones, cascadeOnDelete.')
                ->constrained('ubicaciones')
                ->cascadeOnDelete();
            $table->decimal('stock_minimo', 14, 4)
                ->default(0)
                ->comment('Nivel mínimo tolerable. Al caer por debajo de este valor se genera una orden de reposición automática.');
            $table->decimal('stock_objetivo', 14, 4)
                ->default(0)
                ->comment('Nivel objetivo de reabastecimiento. La reposición lleva el stock hasta este nivel.');
            $table->timestamps();

            $table->unique(
                ['producto_id', 'producto_variante_id', 'ubicacion_id'],
                'inv_par_stock_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_par_stock');
    }
};
