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
        // 1. Asegurar catálogo TIPO_CAJA en catalogo_tipos
        $tipoId = DB::table('catalogo_tipos')->where('codigo', 'TIPO_CAJA')->value('id');
        if (! $tipoId) {
            $tipoId = DB::table('catalogo_tipos')->insertGetId([
                'codigo' => 'TIPO_CAJA',
                'nombre' => 'Tipo de caja operativa',
                'estado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Sembrar tipos de caja iniciales en catalogos
        $tiposCaja = [
            ['codigo' => 'CAJA_FRONT_DESK', 'nombre' => 'Recepción / Front-Desk', 'descripcion' => 'Cobros de reservas, check-in, check-out y huéspedes.'],
            ['codigo' => 'CAJA_POS_RESTAURANTE', 'nombre' => 'Restaurante y Bar', 'descripcion' => 'Punto de venta y comandas de alimentos y bebidas.'],
            ['codigo' => 'CAJA_BAR_PISCINA', 'nombre' => 'Bar / Piscina', 'descripcion' => 'Punto de venta directo en área de piscina y terraza.'],
            ['codigo' => 'CAJA_EVENTOS', 'nombre' => 'Banquete y Eventos', 'descripcion' => 'Liquidaciones de eventos, salones y banquetes especiales.'],
            ['codigo' => 'CAJA_CHICA_OPERATIVA', 'nombre' => 'Caja Chica Operativa', 'descripcion' => 'Gastos menores de caja chica y compras de urgencia.'],
            ['codigo' => 'CAJA_GENERAL', 'nombre' => 'Caja Principal / Tesorería', 'descripcion' => 'Bóveda central y custodia de fondos principales.'],
        ];

        foreach ($tiposCaja as $idx => $item) {
            DB::table('catalogos')->updateOrInsert(
                ['catalogo_tipo_id' => $tipoId, 'codigo' => $item['codigo']],
                [
                    'catalogo_tipo_id' => $tipoId,
                    'codigo' => $item['codigo'],
                    'nombre' => $item['nombre'],
                    'descripcion' => $item['descripcion'],
                    'orden' => $idx + 1,
                    'estado' => 1,
                    'updated_at' => now(),
                ]
            );
        }

        // 3. Modificar tes_cajas para agregar llaves foráneas a ubicaciones y catalogos
        Schema::table('tes_cajas', function (Blueprint $table): void {
            $table->string('ubicacion', 150)->nullable()->change();

            $table->foreignId('ubicacion_id')
                ->nullable()
                ->after('nombre')
                ->comment('FK a ubicaciones físicas del hotel')
                ->constrained('ubicaciones')
                ->nullOnDelete();

            $table->foreignId('tipo_caja_id')
                ->nullable()
                ->after('ubicacion_id')
                ->comment('FK a catalogos (TIPO_CAJA)')
                ->constrained('catalogos')
                ->nullOnDelete();

            $table->index('ubicacion_id');
            $table->index('tipo_caja_id');
        });
    }

    public function down(): void
    {
        Schema::table('tes_cajas', function (Blueprint $table): void {
            $table->dropForeign(['ubicacion_id']);
            $table->dropForeign(['tipo_caja_id']);
            $table->dropIndex(['ubicacion_id']);
            $table->dropIndex(['tipo_caja_id']);
            $table->dropColumn(['ubicacion_id', 'tipo_caja_id']);
            $table->string('ubicacion', 150)->nullable(false)->change();
        });
    }
};
