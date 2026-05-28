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
        Schema::create('precios_espacio', function (Blueprint $table) {
            $table->comment('Precios de los espacios físicos (mesas, salones, gym, etc.)');
            $table->id()->comment('Identificador único del precio');

            $table->foreignId('espacio_id')
                ->comment('Referencia al espacio físico')
                ->constrained('espacios');

            $table->foreignId('moneda_id')
                ->comment('Moneda del precio')
                ->constrained('monedas');

            $table->decimal('precio', 10, 2)->comment('Precio de reserva/alquiler en la moneda seleccionada');
            $table->date('fecha_inicio')->comment('Fecha de inicio de vigencia del precio');
            $table->date('fecha_fin')->nullable()->comment('Fecha de fin de vigencia del precio');
            $table->integer('estado')->default(1)->comment('Estado: 1=Vigente, 2=No Vigente');
            $table->boolean('es_oferta')->default(false)->comment('Indica si es una oferta especial');

            $table->string('tipo_precio', 50)->default('base')->comment('Tipo de precio: base (reserva completa) o por_hora (tarifa horaria)');
            $table->timestamps();
            $table->softDeletes();

            $table->index('espacio_id');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE precios_espacio ADD CONSTRAINT spe_estado_check CHECK (estado IN (1, 2))');
            DB::statement('ALTER TABLE precios_espacio ADD CONSTRAINT spe_chk_fecha CHECK (fecha_fin IS NULL OR fecha_fin >= fecha_inicio)');
            DB::statement('ALTER TABLE precios_espacio ADD CONSTRAINT spe_chk_precio CHECK (precio >= 0)');
            DB::statement('ALTER TABLE precios_espacio ADD CONSTRAINT spe_chk_precio_vigente CHECK (estado != 1 OR precio > 0)');
            DB::statement('ALTER TABLE precios_espacio ADD CONSTRAINT spe_chk_tipo_precio CHECK (tipo_precio IN (\'base\', \'por_hora\'))');
            DB::statement('CREATE UNIQUE INDEX spe_unique_precio_vigente ON precios_espacio(espacio_id, moneda_id, tipo_precio) WHERE estado = 1 AND es_oferta = false AND deleted_at IS NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('precios_espacio');
    }
};
