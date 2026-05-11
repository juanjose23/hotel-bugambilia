<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla imagenes para manejo polimórfico de imágenes.
     * Permite asociar imágenes a cualquier modelo del sistema
     * (productos, colaboradores, clientes, etc.) mediante
     * relaciones polimórficas (imagenable_type + imagenable_id).
     */
    public function up(): void
    {
        Schema::create('imagenes', function (Blueprint $table) {
            $table->id();
            $table->string('url')->comment('URL o ruta de almacenamiento de la imagen');
            $table->string('public_id')->nullable()->comment('ID público en Cloudinary o proveedor cloud (nullable si es almacenamiento local)');
            $table->morphs('imagenable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imagenes');
    }
};
