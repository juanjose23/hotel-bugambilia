<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('politica_penalizaciones', function (Blueprint $table) {
            $table->comment('Rangos ejecutables de penalizacion por cancelacion para politicas de negocio');
            $table->id();
            $table->foreignId('politica_id')
                ->constrained('politicas')
                ->onDelete('cascade');
            $table->integer('min_unidades')->nullable()->comment('Anticipacion minima (dias u horas)');
            $table->integer('max_unidades')->nullable()->comment('Anticipacion maxima (dias u horas)');
            $table->unsignedSmallInteger('unidad')->default(1)->comment('Unidad: 1=Dias, 2=Horas');
            $table->decimal('porcentaje', 5, 2)->default(100.00)->comment('Porcentaje del total de la reserva');
            $table->boolean('aplica_no_show')->default(false)->comment('Aplica cuando es no-show o despues de fecha');
            $table->integer('orden')->default(0);
            $table->timestamps();

            $table->index(['politica_id', 'aplica_no_show'], 'idx_politica_penalizacion_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('politica_penalizaciones');
    }
};
