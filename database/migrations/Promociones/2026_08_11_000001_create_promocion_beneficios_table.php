<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promocion_beneficios', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('promocion_id')->nullable()->constrained('promociones')->nullOnDelete();
            $table->foreignId('segmento_cliente_id')->nullable()->constrained('catalogos')->nullOnDelete();
            $table->string('codigo', 40)->unique();
            $table->string('nombre', 150);
            $table->string('tipo', 40)->index();
            $table->decimal('valor', 12, 2)->nullable();
            $table->boolean('es_porcentaje')->default(true);
            $table->boolean('combinable')->default(false);
            $table->unsignedSmallInteger('limite_usos_por_cliente')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true)->index();
            $table->text('descripcion')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['segmento_cliente_id', 'activo']);
            $table->index(['fecha_inicio', 'fecha_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promocion_beneficios');
    }
};
