<?php

use App\Jobs\Inventario\VerificarCaducidadesJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    fwrite(STDOUT, Inspiring::quote().PHP_EOL);
})->purpose('Display an inspiring quote');

Artisan::command('inventario:verificar-caducidades', function (): void {
    VerificarCaducidadesJob::dispatchSync();
    fwrite(STDOUT, "Caducidades verificadas exitosamente.\n");
})->purpose('Verificar lotes vencidos y notificar próximos a vencer');

Schedule::job(new VerificarCaducidadesJob)
    ->name('verificar-caducidades')
    ->dailyAt('06:00')
    ->withoutOverlapping();
