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
        Schema::create('precios_habitacion', function (Blueprint $table) {
            $table->comment('Precios de las habitaciones');
            $table->id()->comment('Identificador único del precio');
            $table->foreignId('habitacion_id')
                ->comment('Referencia a la habitación')
                ->constrained('habitaciones');
            $table->foreignId('moneda_id')
                ->comment('Moneda del precio')
                ->constrained('monedas');
            $table->decimal('precio', 10, 2)->comment('Precio del servicio en la moneda seleccionada');
            $table->date('fecha_inicio')->comment('Fecha de inicio de vigencia');
            $table->date('fecha_fin')->nullable()->comment('Fecha de fin de vigencia');
            $table->integer('estado')->default(1)->comment('Estado: 1=Vigente, 2=No Vigente');
            $table->boolean('es_oferta')->default(false)->comment('Indica si es una oferta especial');
            $table->timestamps();
            $table->softDeletes();

            $table->index('habitacion_id');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE precios_habitacion ADD CONSTRAINT shp_estado_check CHECK (estado IN (1, 2))');
            DB::statement('ALTER TABLE precios_habitacion ADD CONSTRAINT shp_chk_fecha CHECK (fecha_fin IS NULL OR fecha_fin >= fecha_inicio)');
            DB::statement('ALTER TABLE precios_habitacion ADD CONSTRAINT shp_chk_precio CHECK (precio >= 0)');
            DB::statement('ALTER TABLE precios_habitacion ADD CONSTRAINT shp_chk_precio_vigente CHECK (estado != 1 OR precio > 0)');
            DB::statement('CREATE UNIQUE INDEX shp_unique_precio_vigente ON precios_habitacion(habitacion_id, moneda_id) WHERE estado = 1 AND es_oferta = false AND deleted_at IS NULL');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('precios_habitacion');
    }
};
