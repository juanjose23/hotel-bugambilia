<?php

declare(strict_types=1);

namespace Database\Seeders\Limpieza\Lavanderia;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Catalogos\Ubicacion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Químicos de lavandería como stock inicial del área.
 *
 * Ubica detergentes, cloro, desengrasante y quitamanchas en la ubicación
 * tipo `lavanderia` para que los interactors de lavandería
 * (RegistrarConsumoJornadaLavanderia, RegistrarConsumoMermaLavanderia,
 * RegistrarEntradaInsumosLavanderia, ReponerDesdeLavanderia) dispongan de
 * insumos coherentes para operar el ciclo de blanco y lencería.
 */
final class LimpiezaLavanderiaSeeder extends Seeder
{
    /**
     * @var array<int, array{codigo: string, cantidad: float, costo_unitario: float}>
     */
    private array $insumos = [
        ['codigo' => 'DET-20L', 'cantidad' => 12.0, 'costo_unitario' => 48.00],
        ['codigo' => 'CLO-20L', 'cantidad' => 8.0, 'costo_unitario' => 35.00],
        ['codigo' => 'DG-5L', 'cantidad' => 6.0, 'costo_unitario' => 32.00],
        ['codigo' => 'QM-500ML', 'cantidad' => 10.0, 'costo_unitario' => 21.00],
    ];

    public function run(): void
    {
        $lavanderia = Ubicacion::where('tipo', 'lavanderia')->first();

        if (! $lavanderia) {
            $this->command->warn('No se encontró la ubicación Lavandería. Ejecuta UbicacionSeeder primero.');

            return;
        }

        foreach ($this->insumos as $item) {
            $variant = DB::table('producto_variantes')
                ->where('codigo', $item['codigo'])
                ->first();

            if (! $variant) {
                continue;
            }

            $exists = DB::table('inv_stock')
                ->where('producto_id', $variant->producto_id)
                ->where('producto_variante_id', $variant->id)
                ->where('ubicacion_id', $lavanderia->id)
                ->exists();

            if ($exists) {
                continue;
            }

            $costoTotal = $item['costo_unitario'] * $item['cantidad'];
            $loteId = DB::table('inv_lotes')->insertGetId([
                'codigo_lote' => 'LOTE-LAVANDERIA-'.$lavanderia->id.'-'.$variant->id,
                'producto_id' => $variant->producto_id,
                'producto_variante_id' => $variant->id,
                'estado' => EstadoLote::Disponible->value,
                'cantidad_disponible' => $item['cantidad'],
                'cantidad_inicial' => $item['cantidad'],
                'costo_unitario' => $item['costo_unitario'],
                'costo_total' => $costoTotal,
                'ubicacion_id' => $lavanderia->id,
                'fecha_vencimiento' => now()->addMonths(12),
                'fecha_recepcion' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('inv_stock')->insert([
                'producto_id' => $variant->producto_id,
                'producto_variante_id' => $variant->id,
                'lote_id' => $loteId,
                'ubicacion_id' => $lavanderia->id,
                'cantidad' => $item['cantidad'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
