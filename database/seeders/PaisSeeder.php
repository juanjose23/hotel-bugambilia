<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PaisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $path = database_path('data/countries.json');

        if (! File::exists($path)) {
            $this->command->error("No se encontró el archivo JSON en: $path");

            return;
        }

        $json = File::get($path);
        /** @var array<int, array<string, mixed>> $paises */
        $paises = (array) json_decode($json, true);

        foreach ($paises as $pais) {
            $idVal = $pais['id'] ?? 0;
            $id = is_numeric($idVal) ? (int) $idVal : 0;

            $alpha2Val = $pais['alpha2'] ?? '';
            $alpha2 = is_string($alpha2Val) ? $alpha2Val : '';

            $alpha3Val = $pais['alpha3'] ?? '';
            $alpha3 = is_string($alpha3Val) ? $alpha3Val : '';

            $nameVal = $pais['name'] ?? '';
            $name = is_string($nameVal) ? $nameVal : '';

            DB::table('paises')->updateOrInsert(
                ['id' => $id],
                [
                    'codigo_iso2' => strtoupper($alpha2),
                    'codigo_iso3' => strtoupper($alpha3),
                    'nombre' => $name,
                    'codigo_telefono' => null,
                    'estado' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('¡Tabla de países cargada con éxito!');
    }
}
