<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reserva_detalles', function (Blueprint $table) {
            $table->foreignId('estancia_id')
                ->nullable()
                ->comment('FK a la estancia asociada')
                ->after('parent_id')
                ->constrained('estancias')
                ->nullOnDelete();

            $table->unsignedTinyInteger('origen')
                ->default(1)
                ->after('estancia_id')
                ->comment('1=ReservaInicial, 2=Recepcion, 3=Huesped, 4=Restaurante, 5=Housekeeping, 6=Spa, 7=Sistema');

            $table->index(['estancia_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::table('reserva_detalles', function (Blueprint $table) {
            $table->dropForeign(['estancia_id']);
            $table->dropColumn(['estancia_id', 'origen']);
        });
    }
};
