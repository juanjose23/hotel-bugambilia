<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique()->comment('Folio correlativo único de comanda en restaurante');
            $table->foreignId('mesa_id')->nullable()->comment('FK a la mesa (espacio) asignada, nullable para room service')->constrained('espacios')->nullOnDelete();
            $table->foreignId('mesero_id')->nullable()->comment('FK al mesero que atiende la comanda')->constrained('colaboradores')->nullOnDelete();
            $table->foreignId('cliente_id')->nullable()->comment('FK al cliente o huésped asignado')->constrained('personas')->nullOnDelete();
            $table->unsignedBigInteger('cuenta_id')->nullable()->comment('FK lógica a la cuenta de cargo (sin FK constraint por orden de migración)');
            $table->unsignedSmallInteger('estado')->default(1)->comment('EstadoPedido: 1=Abierto, 2=EnPreparacion, 3=Listo, 4=Servido, 5=Pagado, 6=CargadoAHabitacion, 7=Cancelado');
            $table->decimal('subtotal', 10, 2)->default(0)->comment('Subtotal operativo de la comanda (suma de items no anulados)');
            $table->foreignId('padre_pedido_id')->nullable()->comment('FK autoref para división de cuenta')->constrained('pedidos')->nullOnDelete();
            $table->integer('consecutivo_comanda')->default(1)->comment('Consecutivo para reimpresiones de comanda');
            $table->timestamp('abierto_en')->nullable()->comment('Fecha y hora de apertura de la comanda');
            $table->timestamp('cerrado_en')->nullable()->comment('Fecha y hora de cierre operativo');
            $table->timestamp('cargado_en')->nullable()->comment('Fecha en que el pedido fue cargado a una cuenta');
            $table->text('notas')->nullable()->comment('Notas especiales del cliente o alergias');
            $table->timestamps();
            $table->softDeletes();

            $table->index('mesa_id');
            $table->index('mesero_id');
            $table->index('cliente_id');
            $table->index('cuenta_id');
            $table->index('estado');
            $table->index(['mesa_id', 'estado']);
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
