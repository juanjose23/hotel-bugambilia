<?php

use App\Jobs\Activos\DetectarMantenimientosAtrasadosJob;
use App\Jobs\Activos\SincronizarEstadoActivoJob;
use App\Jobs\Activos\VerificarActivosSinMantenimientoHistoricoJob;
use App\Jobs\Activos\VerificarGarantiasJob;
use App\Jobs\Activos\VerificarMantenimientosPreventivosJob;
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

Schedule::job(new VerificarMantenimientosPreventivosJob)
    ->name('verificar-mantenimientos-preventivos')
    ->dailyAt('06:15')
    ->withoutOverlapping();

Schedule::job(new VerificarGarantiasJob)
    ->name('verificar-garantias-activos')
    ->dailyAt('06:20')
    ->withoutOverlapping();

Schedule::job(new DetectarMantenimientosAtrasadosJob)
    ->name('detectar-mantenimientos-atrasados')
    ->dailyAt('06:25')
    ->withoutOverlapping();

Schedule::job(new VerificarActivosSinMantenimientoHistoricoJob)
    ->name('verificar-activos-sin-mantenimiento-historico')
    ->dailyAt('06:30')
    ->withoutOverlapping();

Schedule::job(new SincronizarEstadoActivoJob)
    ->name('sincronizar-estado-activo')
    ->dailyAt('06:35')
    ->withoutOverlapping();
