<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tablas nativas de Laravel para el sistema de caché y bloqueos atómicos.
     * Requeridas para el funcionamiento de cache driver database y job batches.
     */
    public function up(): void
    {
        Schema::create('cache', function (Blueprint $table) {
            $table->comment('Tabla nativa de Laravel para almacenamiento en caché de datos y objetos serializados.');
            $table->string('key')->primary()->comment('Clave única de identificación del objeto en caché');
            $table->mediumText('value')->comment('Valor serializado almacenado bajo la clave');
            $table->bigInteger('expiration')->index()->comment('Timestamp UNIX de expiración del registro');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->comment('Tabla nativa de Laravel para la gestión de bloqueos atómicos y exclusión mutua en procesos simultáneos.');
            $table->string('key')->primary()->comment('Clave del bloqueo atómico');
            $table->string('owner')->comment('Identificador del proceso o worker propietario del bloqueo');
            $table->bigInteger('expiration')->index()->comment('Timestamp UNIX de expiración del bloqueo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
