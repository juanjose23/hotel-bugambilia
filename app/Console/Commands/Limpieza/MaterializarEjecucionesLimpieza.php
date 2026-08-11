<?php

declare(strict_types=1);

namespace App\Console\Commands\Limpieza;

use App\Interactors\Limpieza\Procesos\MaterializarEjecuciones;
use Illuminate\Console\Command;

class MaterializarEjecucionesLimpieza extends Command
{
    protected $signature = 'limpieza:materializar-ejecuciones {fecha?}';

    protected $description = 'Materializa los horarios de limpieza activos en ejecuciones para un día específico (por defecto hoy).';

    public function __construct(
        private readonly MaterializarEjecuciones $materializarEjecuciones,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $fechaInput = $this->argument('fecha');

        $resultado = $this->materializarEjecuciones->ejecutar(
            is_string($fechaInput) && $fechaInput !== '' ? $fechaInput : null,
        );

        $this->info("Procesando horarios de limpieza para la fecha: {$resultado['fecha']} ({$resultado['dia_semana']})");
        $this->info("Ejecución finalizada. Se crearon {$resultado['creados']} ejecuciones de limpieza.");

        return Command::SUCCESS;
    }
}
