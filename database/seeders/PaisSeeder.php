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
        $paises = json_decode($json, true);

        foreach ($paises as $pais) {
            DB::table('paises')->insert([
                'id' => $pais['id'],
                'codigo_iso2' => strtoupper($pais['alpha2']),
                'codigo_iso3' => strtoupper($pais['alpha3']),
                'nombre' => $pais['name'],
                'codigo_telefono' => null,
                'estado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('¡Tabla de países cargada con éxito!');
    }
}
