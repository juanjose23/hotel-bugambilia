<?php

namespace App\Console\Commands;

use App\Repository\Models\Catalogos\Producto;
use App\Repository\Queries\Catalogos\GenerarCodigoBarras;
use Illuminate\Console\Command;

class GenerarCodigosBarrasCommand extends Command
{
    public function __construct(
        private readonly GenerarCodigoBarras $generarCodigosBarras,
    ) {
        parent::__construct();
    }

    protected $signature = 'catalogos:generar-codigos-barras {--producto-id= : ID específico del producto}';

    protected $description = 'Genera códigos de barras para productos y sus variantes';

    public function handle(): int
    {
        $productoId = $this->option('producto-id');

        $query = Producto::query();
        if ($productoId) {
            $query->where('id', $productoId);
        }

        $productos = $query->with(['variantes'])->get();

        if ($productos->isEmpty()) {
            $this->error('No se encontraron productos.');

            return self::FAILURE;
        }

        $this->info("Generando códigos de barras para {$productos->count()} producto(s)...");
        $bar = $this->output->createProgressBar($productos->count());

        foreach ($productos as $producto) {
            $codigosGenerados = $this->generarCodigosBarras->generarLote($producto);
            $count = count($codigosGenerados);
            $this->line("\n✓ Producto: {$producto->nombre} ({$count} variantes)");
            $bar->advance();
        }

        $bar->finish();
        $this->info("\n✓ Códigos de barras generados correctamente.");

        return self::SUCCESS;
    }
}
