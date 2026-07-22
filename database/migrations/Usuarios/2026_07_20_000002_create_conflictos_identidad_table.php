<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conflictos_identidad', function (Blueprint $table) {
            $table->comment('Registro de conflictos de identidad detectados durante el registro de clientes.');
            $table->id();
            $table->foreignId('persona_id')
                ->nullable()
                ->constrained('personas')
                ->nullOnDelete();
            $table->string('tipo_conflicto')->comment('homonimia, datos_divergentes, identidad_dudosa');
            $table->json('datos_providos')->comment('Datos que el usuario proporcionó al registrarse');
            $table->json('datos_existentes')->comment('Datos que ya existen en el sistema');
            $table->string('estado')->default('pendiente')->comment('pendiente, resuelto, rechazado');
            $table->foreignId('creado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('resuelto_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('resuelto_en')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index('persona_id');
            $table->index('estado');
            $table->index('creado_por');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conflictos_identidad');
    }
};
