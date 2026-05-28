<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('servicios_espacio', function (Blueprint $table) {
            $table->comment('Relación muchos a muchos entre servicios y espacios físicos, con indicador de incluido');

            $table->id()->comment('Identificador único de la relación servicio-espacio');

            $table->foreignId('servicio_id')
                ->comment('Servicio ofrecido en el espacio')
                ->constrained('servicios');

            $table->foreignId('espacio_id')
                ->comment('Espacio físico que ofrece el servicio')
                ->constrained('espacios');

            $table->boolean('incluido')
                ->default(false)
                ->comment('Indica si el servicio está incluido en la tarifa base del espacio');

            $table->tinyInteger('estado')
                ->default(1)
                ->comment('Estado de la relación: 0=inactivo, 1=activo');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['servicio_id', 'espacio_id'], 'uq_servicio_espacio');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE servicios_espacio ADD CONSTRAINT chk_servicio_espacio_estado CHECK (estado IN (0, 1))');
        }

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE servicios_espacio DROP CONSTRAINT IF EXISTS uq_servicio_espacio');
            DB::statement('CREATE UNIQUE INDEX uq_servicio_espacio ON servicios_espacio(servicio_id, espacio_id) WHERE deleted_at IS NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicios_espacio');
    }
};
