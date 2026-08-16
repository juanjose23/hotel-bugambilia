<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promocion_beneficio_reglas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('beneficio_id')->constrained('promocion_beneficios')->cascadeOnDelete();
            $table->string('tipo_regla', 50)->index();
            $table->string('operador', 20)->default('>=');
            $table->decimal('valor_numerico', 12, 2)->nullable();
            $table->string('valor_texto', 150)->nullable();
            $table->boolean('obligatoria')->default(true);
            $table->timestamps();

            $table->index(['beneficio_id', 'tipo_regla']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promocion_beneficio_reglas');
    }
};
