<?php

declare(strict_types=1);

namespace App\Console\Commands\Limpieza;

use App\Interactors\Limpieza\Procesos\EnviarRecordatorios;
use Illuminate\Console\Command;

class EnviarRecordatoriosLimpieza extends Command
{
    protected $signature = 'limpieza:enviar-recordatorios';

    protected $description = 'Envía recordatorios para las ejecuciones de limpieza pendientes de hoy cuya hora estimada ya ha pasado.';

    public function __construct(
        private readonly EnviarRecordatorios $enviarRecordatorios,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $resultado = $this->enviarRecordatorios->ejecutar();

        foreach ($resultado['avisos'] as $aviso) {
            $this->warn($aviso);
        }

        $this->info("Se procesaron y enviaron recordatorios para {$resultado['enviadas']} ejecuciones de limpieza.");

        return Command::SUCCESS;
    }
}
