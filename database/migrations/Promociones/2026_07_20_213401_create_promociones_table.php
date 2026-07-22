<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promociones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->foreignId('tipo_promocion_id')
                ->nullable()
                ->constrained('catalogos')
                ->nullOnDelete();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->decimal('descuento_porcentaje', 5, 2)->nullable();
            $table->decimal('descuento_monto', 10, 2)->nullable();
            $table->decimal('precio_paquete', 10, 2)->nullable()->comment('Precio del paquete/promoción');
            $table->integer('estado')->default(1);
            $table->boolean('web')->default(false);
            $table->text('condiciones')->nullable();
            $table->integer('orden')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('tipo_promocion_id');
            $table->index('estado');
            $table->index('fecha_inicio');
            $table->index('fecha_fin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promociones');
    }
};
