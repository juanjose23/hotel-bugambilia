<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MÓDULO: Servicios
     *
     * Registra los precios de los servicios adicionales que se pueden ofrecer a los huéspedes.
     * Estos servicios no son consumibles y no se registran en inv_stock.
     * Ejemplos: spa, gimnasio, transporte, etc.
     *
     * REGLA DE NEGOCIO: Los servicios NUNCA deben tener dos precios vigentes al mismo tiempo.
     * La aplicación debe garantizar que solo un precio por servicio pueda estar activo (estado=1) en un momento dado.
     * Además, la fecha_fin de un precio vigente no puede ser anterior a su fecha_inicio.
     * Esto se valida tanto a nivel de aplicación como mediante una restricción CHECK en la base de datos.
     */
    public function up(): void
    {
        Schema::create('servicios_precios', function (Blueprint $table) {
            $table->comment('Precios de los servicios adicionales que se pueden ofrecer a los huéspedes.');
            $table->id()->comment('Identificador único autoincremental del registro');
            $table->foreignId('servicio_id')->comment('Referencia al servicio al que pertenece el precio')->constrained('servicios');
            $table->foreignId('moneda_id')->comment('Referencia a la moneda del precio')->constrained('monedas');
            $table->decimal('precio', 10, 2)->comment('Precio del servicio en la moneda seleccionada');
            $table->date('fecha_inicio')->comment('Fecha de inicio de vigencia del precio');
            $table->date('fecha_fin')->nullable()->comment('Fecha de fin de vigencia del precio');
            $table->integer('estado')->default(1)->comment('Estado del precio: 1=Vigente, 2=No Vigente');
            $table->boolean('es_oferta')->default(false)->comment('Indica si este precio es una oferta especial');
            $table->timestamps();
            $table->softDeletes();
            $table->index('servicio_id');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE servicios_precios ADD CONSTRAINT servicios_precios_estado_check CHECK (estado IN (1, 2))');
            DB::statement('ALTER TABLE servicios_precios ADD CONSTRAINT chk_fecha CHECK (fecha_fin IS NULL OR fecha_fin >= fecha_inicio)');
            DB::statement('ALTER TABLE servicios_precios ADD CONSTRAINT chk_precio CHECK (precio >= 0)');
            DB::statement('ALTER TABLE servicios_precios ADD CONSTRAINT chk_precio_vigente CHECK (estado != 1 OR precio > 0)');
            DB::statement('CREATE UNIQUE INDEX unique_precio_vigente ON servicios_precios(servicio_id, moneda_id) WHERE estado = 1 AND es_oferta = false AND deleted_at IS NULL');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('servicios_precios');
    }
};
