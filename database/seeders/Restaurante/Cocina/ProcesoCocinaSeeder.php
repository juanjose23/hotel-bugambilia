<?php

declare(strict_types=1);

namespace Database\Seeders\Restaurante\Cocina;

use App\Interactors\Restaurante\Cocina\RegistrarProcesoCocina;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Restaurante\ProcesoCocina;

/**
 * Procesos de cocina basados en las recetas de platos del menú; calculan su
 * costo desde el stock de la Cocina Restaurante y consumen los ingredientes.
 */
final class ProcesoCocinaSeeder extends BaseCocinaSeeder
{
    public function run(): void
    {
        $platoFettuccine = Plato::query()->where('nombre', 'Fettuccine Alfredo con Pollo')->first();
        $platoFilete = Plato::query()->where('nombre', 'Filete de res termino medio')->first();

        $this->crearSiNoExiste(
            codigo: 'PROC-COC-001',
            plato: $platoFettuccine,
            cantidadPlatos: 2,
            observaciones: 'Producción de Fettuccine Alfredo con Pollo para servicio de carta.',
        );

        $this->crearSiNoExiste(
            codigo: 'PROC-COC-002',
            plato: $platoFilete,
            cantidadPlatos: 3,
            observaciones: 'Producción de Filete de res término medio para servicio de carta.',
        );

        $this->command->info('Cocina restaurante: procesos de cocina sembrados.');
    }

    private function crearSiNoExiste(string $codigo, ?Plato $plato, int $cantidadPlatos, string $observaciones): void
    {
        if (! $plato instanceof Plato) {
            $this->command->warn("Proceso de cocina [{$codigo}] omitido: no se encontró el plato.");

            return;
        }

        $existe = ProcesoCocina::query()->where('codigo', $codigo)->exists();

        if ($existe) {
            return;
        }

        app(RegistrarProcesoCocina::class)->ejecutar([
            'codigo' => $codigo,
            'plato_id' => $plato->id,
            'cantidad_platos' => $cantidadPlatos,
            'realizado_por' => $this->usuarioId(),
            'observaciones' => $observaciones,
        ]);
    }
}
