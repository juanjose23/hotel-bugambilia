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
            $table->string('codigo', 20)->unique()->comment('Folio correlativo único de comanda en restaurante');
            $table->foreignId('mesa_id')->comment('FK a la mesa (espacio) asignada')->constrained('espacios')->cascadeOnDelete();
            $table->foreignId('mesero_id')->nullable()->comment('FK al mesero que atiende la comanda')->constrained('colaboradores')->nullOnDelete();
            $table->foreignId('cliente_id')->nullable()->comment('FK al cliente o huésped asignado a la cuenta')->constrained('personas')->nullOnDelete();
            $table->string('estado', 30)->default('abierto')->comment('Estado de la comanda: abierto, en_preparacion, servido, pagado, cargado_a_habitacion, cancelado');
            $table->decimal('total', 10, 2)->default(0)->comment('Monto total acumulado de la comanda');
            $table->timestamp('abierto_en')->nullable()->comment('Fecha y hora exacta de apertura de la comanda');
            $table->timestamp('cerrado_en')->nullable()->comment('Fecha y hora exacta de cobro/cierre de la comanda');
            $table->text('notas')->nullable()->comment('Notas especiales del cliente o alergias');
            $table->timestamps();
            $table->softDeletes();

            $table->index('mesa_id');
            $table->index('mesero_id');
            $table->index('cliente_id');
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
