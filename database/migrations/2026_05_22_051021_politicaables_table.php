<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('politicaables', function (Blueprint $table) {
            $table->comment('Relación polimórfica entre políticas y entidades del sistema');
            $table->id();
            $table->foreignId('politica_id')
                ->comment('Política asociada')
                ->constrained('politicas')
                ->cascadeOnDelete();
            $table->morphs('politicaable');
            $table->timestamps();
            $table->softDeletes();
            $table->unique([
                'politica_id',
                'politicaable_type',
                'politicaable_id',
            ], 'politicaables_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('politicaables');
    }
};
