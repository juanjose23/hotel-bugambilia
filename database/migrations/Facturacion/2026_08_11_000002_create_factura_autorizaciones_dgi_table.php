<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factura_autorizaciones_dgi', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('factura_serie_id')->constrained('factura_series')->restrictOnDelete();
            $table->string('numero_autorizacion', 80)->unique();
            $table->string('ruc_emisor', 30);
            $table->string('razon_social_emisor', 180);
            $table->string('nombre_comercial_emisor', 180)->nullable();
            $table->string('direccion_emisor', 255)->nullable();
            $table->date('fecha_autorizacion');
            $table->date('vence_at')->nullable()->index();
            $table->unsignedBigInteger('rango_desde');
            $table->unsignedBigInteger('rango_hasta');
            $table->string('pie_imprenta_fiscal', 255)->nullable();
            $table->boolean('activa')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['factura_serie_id', 'activa']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_autorizaciones_dgi');
    }
};
