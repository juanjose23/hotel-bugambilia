<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estancias', function (Blueprint $table): void {
            $table->dropUnique(['reserva_id']);
            $table->foreignId('reserva_detalle_id')->nullable()->comment('FK al detalle de reserva de la estancia')->after('reserva_id')->constrained('reserva_detalles')->nullOnDelete();
            $table->dateTime('fecha_entrada_programada')->nullable()->after('habitacion_id')->comment('Fecha y hora programada de entrada');
            $table->dateTime('fecha_salida_programada')->nullable()->after('fecha_entrada_programada')->comment('Fecha y hora programada de salida');
            $table->dateTime('fecha_check_in_real')->nullable()->after('fecha_salida_programada')->comment('Fecha y hora real de entrada');
            $table->dateTime('fecha_check_out_real')->nullable()->after('fecha_check_in_real')->comment('Fecha y hora real de salida');

            $table->index(['reserva_id', 'estado'], 'estancias_reserva_estado_idx');
            $table->index(['reserva_detalle_id', 'estado'], 'estancias_detalle_estado_idx');
            $table->index(['habitacion_id', 'estado'], 'estancias_habitacion_estado_idx');
        });
    }

    public function down(): void
    {
        Schema::table('estancias', function (Blueprint $table): void {
            $table->dropIndex('estancias_reserva_estado_idx');
            $table->dropIndex('estancias_detalle_estado_idx');
            $table->dropIndex('estancias_habitacion_estado_idx');
            $table->dropConstrainedForeignId('reserva_detalle_id');
            $table->dropColumn([
                'fecha_entrada_programada',
                'fecha_salida_programada',
                'fecha_check_in_real',
                'fecha_check_out_real',
            ]);
            $table->unique('reserva_id');
        });
    }
};
