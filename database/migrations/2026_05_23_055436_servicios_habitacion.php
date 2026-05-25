<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicios_habitacion', function (Blueprint $table) {
            $table->comment('Relación muchos a muchos entre servicios y habitaciones, con precio propio e indicador de incluido');

            $table->id()->comment('Identificador único de la relación servicio-habitación');

            $table->foreignId('servicio_id')
                ->comment('Servicio ofrecido en la habitación')
                ->constrained('servicios');

            $table->foreignId('habitacion_id')
                ->comment('Habitación que ofrece el servicio')
                ->constrained('habitaciones');

            $table->boolean('incluido')
                ->default(false)
                ->comment('Indica si el servicio está incluido en la tarifa base de la habitación');

            $table->tinyInteger('estado')
                ->default(1)
                ->comment('Estado de la relación: 0=inactivo, 1=activo');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['servicio_id', 'habitacion_id'], 'uq_servicio_habitacion');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE servicios_habitacion ADD CONSTRAINT chk_servicio_habitacion_estado CHECK (estado IN (0, 1))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('servicios_habitacion');
    }
};
