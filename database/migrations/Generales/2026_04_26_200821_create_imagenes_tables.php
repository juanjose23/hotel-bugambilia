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
            $table->comment('Tabla para la gestión y almacenamiento de referencias de imágenes asociadas polimórficamente a cualquier modelo del sistema.');
            $table->id()->comment('Identificador único autoincremental de la imagen');
            $table->string('url')->comment('URL o ruta de almacenamiento de la imagen');
            $table->string('public_id')->nullable()->comment('ID público en Cloudinary o proveedor cloud (nullable si es almacenamiento local)');
            $table->integer('orden')->default(0)->after('public_id')->comment('Orden de visualización');
            $table->morphs('imagenable');
            $table->timestamps();
            $table->softDeletes();
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
