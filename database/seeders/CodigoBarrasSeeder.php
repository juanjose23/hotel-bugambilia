<?php

namespace Database\Seeders;

use App\Models\Catalogos\Producto;
use App\UseCases\Catalogos\Queries\GenerarCodigoBarrasUseCase;
use Illuminate\Database\Seeder;

class CodigoBarrasSeeder extends Seeder
{
    public function run(): void
    {
        $useCase = new GenerarCodigoBarrasUseCase;
        $productos = Producto::with('variantes')->get();

        foreach ($productos as $producto) {
            $useCase->generarLote($producto);
        }

        $this->command->info('✓ Códigos de barras generados exitosamente para '.$productos->count().' productos.');
    }
}
