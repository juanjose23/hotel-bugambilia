<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // 1. Asegurar el tipo BANCO en catalogo_tipos
        $tipoId = DB::table('catalogo_tipos')->where('codigo', 'BANCO')->value('id');

        if (! $tipoId) {
            $tipoId = DB::table('catalogo_tipos')->insertGetId([
                'codigo' => 'BANCO',
                'nombre' => 'Entidad Bancaria',
                'estado' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 2. Bancos principales de la plaza
        $bancos = [
            [
                'catalogo_tipo_id' => $tipoId,
                'codigo' => 'BANCO_BAC',
                'nombre' => 'BAC Credomatic',
                'prefijo' => 'BAC',
                'descripcion' => 'Banco de América Central',
                'orden' => 1,
                'estado' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'catalogo_tipo_id' => $tipoId,
                'codigo' => 'BANCO_BANPRO',
                'nombre' => 'Banco de la Producción (Banpro)',
                'prefijo' => 'BPR',
                'descripcion' => 'Grupo Promerica',
                'orden' => 2,
                'estado' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'catalogo_tipo_id' => $tipoId,
                'codigo' => 'BANCO_LAFISE',
                'nombre' => 'Banco Lafise Bancentro',
                'prefijo' => 'LAF',
                'descripcion' => 'Grupo LAFISE',
                'orden' => 3,
                'estado' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'catalogo_tipo_id' => $tipoId,
                'codigo' => 'BANCO_BDF',
                'nombre' => 'Banco de Finanzas (BDF)',
                'prefijo' => 'BDF',
                'descripcion' => 'Banco de Finanzas',
                'orden' => 4,
                'estado' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'catalogo_tipo_id' => $tipoId,
                'codigo' => 'BANCO_FICOHSA',
                'nombre' => 'Banco Ficohsa',
                'prefijo' => 'FIC',
                'descripcion' => 'Grupo Financiero Ficohsa',
                'orden' => 5,
                'estado' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'catalogo_tipo_id' => $tipoId,
                'codigo' => 'BANCO_AVANZ',
                'nombre' => 'Banco Avanz',
                'prefijo' => 'AVZ',
                'descripcion' => 'Grupo Pellas',
                'orden' => 6,
                'estado' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'catalogo_tipo_id' => $tipoId,
                'codigo' => 'BANCO_OTRO',
                'nombre' => 'Otro Banco / Extranjero',
                'prefijo' => 'EXT',
                'descripcion' => 'Cuentas en bancos no listados o internacionales',
                'orden' => 7,
                'estado' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('catalogos')->upsert($bancos, ['catalogo_tipo_id', 'codigo'], ['nombre', 'prefijo', 'descripcion', 'orden', 'estado', 'updated_at']);
    }

    public function down(): void
    {
        $tipoId = DB::table('catalogo_tipos')->where('codigo', 'BANCO')->value('id');
        if ($tipoId) {
            DB::table('catalogos')->where('catalogo_tipo_id', $tipoId)->delete();
            DB::table('catalogo_tipos')->where('id', $tipoId)->delete();
        }
    }
};
