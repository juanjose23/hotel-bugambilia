<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MÓDULO: ÍTEMS DE ORDEN DE REPOSICIÓN (inv_reposicion_items)
     *
     * Líneas de detalle de cada orden de reposición: qué producto se requiere,
     * cuánto se solicitó y cuánto se surtió efectivamente.
     *
     * DIFERENCIA cantidad_solicitada vs cantidad_surtida:
     *   Puede ocurrir que el Almacén General no tenga suficiente stock para
     *   satisfacer toda la cantidad solicitada. En ese caso, se surte lo que hay
     *   y se registra la diferencia para seguimiento.
     */
    public function up(): void
    {
        Schema::create('inv_reposicion_items', function (Blueprint $table) {
            $table->comment('Detalle de productos a reponer en cada orden. Registra cantidad solicitada vs cantidad surtida efectivamente.');
            $table->id()->comment('Identificador único autoincremental del ítem de reposición');
            $table->foreignId('reposicion_id')
                ->comment('Orden de reposición a la que pertenece este ítem (FK → inv_reposiciones, cascadeOnDelete)')
                ->constrained('inv_reposiciones')
                ->cascadeOnDelete();
            $table->foreignId('producto_id')
                ->comment('Producto a reponer (FK → productos, cascadeOnDelete)')
                ->constrained('productos')
                ->cascadeOnDelete();
            $table->foreignId('producto_variante_id')
                ->nullable()
                ->comment('Variante específica del producto. NULL = sin variante específica. FK → producto_variantes, nullOnDelete.')
                ->constrained('producto_variantes')
                ->nullOnDelete();
            $table->decimal('cantidad_solicitada', 14, 4)
                ->comment('Cantidad calculada por GenerarReposicionesBodega (stock_objetivo - stock_actual)');
            $table->decimal('cantidad_surtida', 14, 4)
                ->default(0)
                ->comment('Cantidad que efectivamente se transfirió al destino. Puede ser menor que la solicitada por falta de stock en origen.');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_reposicion_items');
    }
};
