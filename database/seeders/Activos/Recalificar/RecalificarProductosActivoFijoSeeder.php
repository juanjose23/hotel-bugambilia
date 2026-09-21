<?php

declare(strict_types=1);

namespace Database\Seeders\Activos\Recalificar;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Recalifica como tipo=3 (Activo Fijo Individualizable) todos los productos
 * cuya categoría corresponde a activos fijos.
 *
 * Idempotente: puede ejecutarse múltiples veces sin efectos secundarios.
 *
 * Regla de negocio:
 *   tipo=1 → Perecedero
 *   tipo=2 → No perecedero / consumible
 *   tipo=3 → Activo Fijo (requiere individualización y código de inventario)
 *
 * Categorías de activos:
 *   CAT_PRO_MOB     → Mobiliario (camas, mesas, sofás, sillas, etc.)
 *   CAT_PRO_ELECTRO → Electrónicos (TVs, ACs, cafeteras, cámaras, etc.)
 *
 * NOTA: CAT_PRO_MANT incluye tanto herramientas consumibles como equipos
 * mayores. Para una distinción más fina, crea categorías separadas.
 */
class RecalificarProductosActivoFijoSeeder extends Seeder
{
    /**
     * Códigos de catálogo que corresponden a Activos Fijos (tipo=3).
     * Solo se incluyen los que deben individualizarse como activos.
     *
     * @var array<string>
     */
    private const CATEGORIAS_ACTIVO_FIJO = [
        'CAT_PRO_MOB',      // Mobiliario general y eventos
        'CAT_PRO_ELECTRO',  // Equipos electrónicos
    ];

    public function run(): void
    {
        $categoriaIds = DB::table('catalogos')
            ->whereIn('codigo', self::CATEGORIAS_ACTIVO_FIJO)
            ->pluck('id');

        if ($categoriaIds->isEmpty()) {
            $this->command->warn('No se encontraron categorías de activo fijo en catalogos. Verifique que CatalogoSeeder se ejecutó primero.');

            return;
        }

        $antes = DB::table('productos')
            ->whereIn('categoria_id', $categoriaIds)
            ->where('tipo', '!=', 3)
            ->count();

        $actualizados = DB::table('productos')
            ->whereIn('categoria_id', $categoriaIds)
            ->where('tipo', '!=', 3)
            ->update([
                'tipo' => 3,
                'updated_at' => now(),
            ]);

        // Reporte por categoría
        $this->command->info('RecalificarProductosActivoFijoSeeder completado:');

        foreach (self::CATEGORIAS_ACTIVO_FIJO as $codigo) {
            $catId = DB::table('catalogos')->where('codigo', $codigo)->value('id');

            if (! $catId) {
                continue;
            }

            $total = DB::table('productos')
                ->where('categoria_id', $catId)
                ->count();

            $tipo3 = DB::table('productos')
                ->where('categoria_id', $catId)
                ->where('tipo', 3)
                ->count();

            $this->command->info("   [{$codigo}] → {$tipo3}/{$total} productos marcados como Activo Fijo (tipo=3)");
        }

        $this->command->info("   Total recalificados ahora: {$actualizados} (tenían tipo incorrecto: {$antes})");

        // Verificación: confirmar que hay productos tipo=3 disponibles para el ActivoFijoSeeder
        $totalActivos = DB::table('productos')->where('tipo', 3)->count();
        $this->command->info("   Total global tipo=3: {$totalActivos} productos listos para individualización.");
    }
}
