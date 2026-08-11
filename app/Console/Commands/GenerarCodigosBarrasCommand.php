<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Repository\Queries\Catalogos\GenerarCodigoBarras;
use App\Repository\Queries\Catalogos\ObtenerProductosCodigosBarras;
use Illuminate\Console\Command;

class GenerarCodigosBarrasCommand extends Command
{
    public function __construct(
        private readonly ObtenerProductosCodigosBarras $obtenerProductos,
        private readonly GenerarCodigoBarras $generarCodigosBarras,
    ) {
        parent::__construct();
    }

    protected $signature = 'catalogos:generar-codigos-barras {--producto-id= : ID específico del producto}';

    protected $description = 'Genera códigos de barras para productos y sus variantes';

    public function handle(): int
    {
        $productoId = $this->option('producto-id');
        $productos = $this->obtenerProductos->ejecutar(
            is_numeric($productoId) ? (int) $productoId : null,
        );

        if ($productos->isEmpty()) {
            $this->error('No se encontraron productos.');

            return self::FAILURE;
        }

        $this->info("Generando códigos de barras para {$productos->count()} producto(s)...");
        $bar = $this->output->createProgressBar($productos->count());

        foreach ($productos as $producto) {
            $codigosGenerados = $this->generarCodigosBarras->generarLote($producto);
            $count = count($codigosGenerados);
            $this->line("\nProducto: {$producto->nombre} ({$count} variantes)");
            $bar->advance();
        }

        $bar->finish();
        $this->info("\nCódigos de barras generados correctamente.");

        return self::SUCCESS;
    }
}
