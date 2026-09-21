<?php

declare(strict_types=1);

namespace Database\Seeders\Activos\Prefijos;

use App\BusinessLogic\Activos\GeneradorPrefijo;
use App\Repository\Models\Activos\PrefijoCodigo;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Producto;
use Illuminate\Database\Seeder;

class PrefijoCodigoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Siembra los prefijos de inventario de activos utilizando la misma lógica de negocio del GeneradorPrefijo.
     */
    public function run(): void
    {
        $generadorPrefijo = app(GeneradorPrefijo::class);
        $prefijosRegistrados = [];

        // 1. Prefijos base estándar del sector hotelero
        $prefijosBase = [
            'TV',   // Televisores y Pantallas
            'AC',   // Aires Acondicionados / Climatización
            'CAM',  // Camas y Somieres
            'MUE',  // Muebles Generales
            'ELE',  // Equipos Electrónicos
            'CLI',  // Climatización
            'SAN',  // Sanitarios y Grifería
            'LAV',  // Lavandería y Planchado
            'COC',  // Equipos de Cocina
            'GYM',  // Equipos de Gimnasio y Fitness
            'SOM',  // Sombrillas y Mobiliario Exterior
            'SIL',  // Sillones y Sillas
            'MES',  // Mesas y Escritorios
            'UPS',  // Sistemas de Respaldo Eléctrico
            'AUD',  // Equipos de Audio y Sonido
            'PROY', // Proyectores y Pantallas
            'ACT',  // Activos Fijos Genéricos
        ];

        foreach ($prefijosBase as $prefijo) {
            $prefijosRegistrados[$prefijo] = true;
        }

        // 2. Extraer prefijos dinámicamente desde las categorías de catálogo usando el GeneradorPrefijo
        $categorias = Catalogo::query()
            ->whereHas('catalogoTipo', fn ($q) => $q->whereIn('codigo', ['CATEGORIA_PRODUCTO', 'TIPO_ACTIVO', 'FAMILIA_PRODUCTO']))
            ->get();

        foreach ($categorias as $categoria) {
            $prefijo = $generadorPrefijo->generarDesdeCategoria($categoria);
            if (filled($prefijo)) {
                $prefijosRegistrados[$prefijo] = true;
            }
        }

        // 3. Extraer prefijos dinámicamente desde los productos de activo fijo (tipo=3)
        $productosActivo = Producto::query()
            ->where('tipo', 3)
            ->with('categoria')
            ->get();

        foreach ($productosActivo as $producto) {
            $prefijo = $generadorPrefijo->generarDesdeProducto($producto);
            if (filled($prefijo)) {
                $prefijosRegistrados[$prefijo] = true;
            }
        }

        // 4. Persistir todos los prefijos asegurando no duplicados y conservando el último número si ya existe
        foreach (array_keys($prefijosRegistrados) as $prefijoStr) {
            PrefijoCodigo::query()->firstOrCreate(
                ['prefijo' => $prefijoStr],
                ['ultimo_numero' => 0]
            );
        }
    }
}
