<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\HistoricoMedioMillonSeeder;
use Illuminate\Console\Command;

final class GenerarHistoricoMedioMillonCommand extends Command
{
    protected $signature = 'hotel:seed-500k {--limpiar-solo : Solo elimina los registros históricos previos de 500K}';

    protected $description = 'Genera un volumen histórico de 500,000 registros (pedidos, items, reservas y servicios)';

    public function handle(): int
    {
        $this->call('db:seed', [
            '--class' => HistoricoMedioMillonSeeder::class,
        ]);

        return self::SUCCESS;
    }
}
