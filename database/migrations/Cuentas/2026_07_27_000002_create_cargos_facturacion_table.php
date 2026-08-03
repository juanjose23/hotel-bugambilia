<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargos_facturacion', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 20)->unique()->comment('Código único: IVA15, SERV10, PROP10');
            $table->string('nombre', 100)->comment('Nombre descriptivo del cargo');
            $table->unsignedTinyInteger('tipo')->comment('TipoCargo: 1=Impuesto, 2=Servicio, 3=Propina, 4=Descuento, 5=Recargo, 6=Otro');
            $table->unsignedTinyInteger('modo_calculo')->default(1)->comment('ModoCargo: 1=Porcentaje, 2=MontoFijo, 3=Manual');
            $table->decimal('valor', 8, 4)->default(0)->comment('Porcentaje o monto fijo del cargo');
            $table->unsignedTinyInteger('base_calculo')->default(1)->comment('BaseCalculo: 1=SubtotalBruto, 2=SubtotalNeto, 3=TotalConImpuestos, 4=BaseManual');
            $table->unsignedSmallInteger('orden')->default(0)->comment('Orden de aplicación (menor primero)');
            $table->boolean('obligatorio')->default(false)->comment('Si es obligatorio y no permite ser removido');
            $table->boolean('permite_modificacion')->default(true)->comment('Si el mesero/cajero puede modificar el valor');
            $table->jsonb('areas')->nullable()->comment('Áreas de cocina donde aplica: ["cocina","bar"] o null=todas');
            $table->date('fecha_inicio')->nullable()->comment('Fecha de inicio de vigencia');
            $table->date('fecha_fin')->nullable()->comment('Fecha de fin de vigencia');
            $table->unsignedTinyInteger('estado')->default(1)->comment('1=Activo, 0=Inactivo');
            $table->timestamps();

            $table->index('tipo');
            $table->index('estado');
            $table->index(['tipo', 'estado']);
        });

        // CHECK constraints
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE cargos_facturacion ADD CONSTRAINT chk_cargos_tipo CHECK (tipo BETWEEN 1 AND 6)');
            DB::statement('ALTER TABLE cargos_facturacion ADD CONSTRAINT chk_cargos_modo CHECK (modo_calculo BETWEEN 1 AND 3)');
            DB::statement('ALTER TABLE cargos_facturacion ADD CONSTRAINT chk_cargos_base CHECK (base_calculo BETWEEN 1 AND 4)');
            DB::statement('ALTER TABLE cargos_facturacion ADD CONSTRAINT chk_cargos_valor CHECK (valor >= 0)');
            DB::statement('ALTER TABLE cargos_facturacion ADD CONSTRAINT chk_cargos_estado CHECK (estado IN (0, 1))');
            DB::statement('ALTER TABLE cargos_facturacion ADD CONSTRAINT chk_cargos_vigencia CHECK (fecha_fin IS NULL OR fecha_inicio <= fecha_fin)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cargos_facturacion');
    }
};
