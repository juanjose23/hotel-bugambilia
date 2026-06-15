<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('precios', function (Blueprint $table) {
            $table->id();
            $table->morphs('priceable');
            $table->foreignId('moneda_id')->constrained('monedas');
            $table->decimal('precio', 10, 2);
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->integer('estado')->default(1);
            $table->boolean('es_oferta')->default(false);
            $table->string('tipo_precio', 50)->default('base');
            $table->timestamps();
            $table->softDeletes();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('precios');
    }
};
