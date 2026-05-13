<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compra_historial', function (Blueprint $table) {
            $table->id();
            $table->string('model_type')->comment('Solicitud, OrdenCompra, etc');
            $table->unsignedBigInteger('model_id');
            $table->string('estado_anterior')->nullable();
            $table->string('estado_nuevo');
            $table->foreignId('usuario_id')->nullable()->constrained('users');
            $table->text('comentario')->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compra_historial');
    }
};
