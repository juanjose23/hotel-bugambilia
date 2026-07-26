<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla auditoria_restaurante
        if (! Schema::hasTable('auditoria_restaurante')) {
            Schema::create('auditoria_restaurante', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('mesa_id')->nullable()->constrained('espacios')->nullOnDelete();
                $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->nullOnDelete();
                $table->string('accion');
                $table->json('detalles')->nullable();
                $table->string('ip', 45)->nullable();
                $table->timestamps();
            });
        }

        // 2. Columna area_cocina en platos
        if (Schema::hasTable('platos') && ! Schema::hasColumn('platos', 'area_cocina')) {
            Schema::table('platos', function (Blueprint $table): void {
                $table->string('area_cocina')->default('cocina')->after('categoria_id');
            });
        }

        // 3. Columnas de POS/cuentas en pedidos
        if (Schema::hasTable('pedidos')) {
            Schema::table('pedidos', function (Blueprint $table): void {
                if (! Schema::hasColumn('pedidos', 'propina_monto')) {
                    $table->decimal('propina_monto', 10, 2)->default(0)->after('total');
                }
                if (! Schema::hasColumn('pedidos', 'propina_porcentaje')) {
                    $table->decimal('propina_porcentaje', 5, 2)->default(0)->after('propina_monto');
                }
                if (! Schema::hasColumn('pedidos', 'impuesto_monto')) {
                    $table->decimal('impuesto_monto', 10, 2)->default(0)->after('propina_porcentaje');
                }
                if (! Schema::hasColumn('pedidos', 'impuesto_porcentaje')) {
                    $table->decimal('impuesto_porcentaje', 5, 2)->default(0)->after('impuesto_monto');
                }
                if (! Schema::hasColumn('pedidos', 'descuento_monto')) {
                    $table->decimal('descuento_monto', 10, 2)->default(0)->after('impuesto_porcentaje');
                }
                if (! Schema::hasColumn('pedidos', 'descuento_porcentaje')) {
                    $table->decimal('descuento_porcentaje', 5, 2)->default(0)->after('descuento_monto');
                }
                if (! Schema::hasColumn('pedidos', 'motivo_descuento')) {
                    $table->string('motivo_descuento')->nullable()->after('descuento_porcentaje');
                }
                if (! Schema::hasColumn('pedidos', 'consecutivo_comanda')) {
                    $table->integer('consecutivo_comanda')->default(1)->after('motivo_descuento');
                }
                if (! Schema::hasColumn('pedidos', 'padre_pedido_id')) {
                    $table->foreignId('padre_pedido_id')->nullable()->after('consecutivo_comanda')->constrained('pedidos')->nullOnDelete();
                }
            });
        }

        // 4. Columnas en pedido_items
        if (Schema::hasTable('pedido_items')) {
            Schema::table('pedido_items', function (Blueprint $table): void {
                if (! Schema::hasColumn('pedido_items', 'area_cocina')) {
                    $table->string('area_cocina')->default('cocina')->after('plato_id');
                }
                if (! Schema::hasColumn('pedido_items', 'observaciones')) {
                    $table->text('observaciones')->nullable()->after('notas');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_restaurante');
    }
};
