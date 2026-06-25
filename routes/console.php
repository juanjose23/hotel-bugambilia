<?php

declare(strict_types=1);

use App\Jobs\Activos\NotificarMantenimientosJob;
use App\Jobs\Activos\SincronizarEstadoActivoJob;
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

Artisan::command('mantenimiento:procesar-todos', function (): void {
    VerificarMantenimientosPreventivosJob::dispatchSync();
    NotificarMantenimientosJob::dispatchSync();
    SincronizarEstadoActivoJob::dispatchSync();
    VerificarGarantiasJob::dispatchSync();
    fwrite(STDOUT, "Todos los jobs de mantenimiento procesados síncronamente.\n");
})->purpose('Forzar la ejecución síncrona de todos los jobs de mantenimiento y garantías');

$shouldSchedule = function (string $key, string $default = '06:00'): ?string {
    $value = config($key, $default);
    if (! is_string($value)) {
        return null;
    }
    $strValue = strtolower(trim($value));
    if (in_array($strValue, ['null', 'false', 'disabled', 'none', ''], true)) {
        return null;
    }

    return $value;
};

$timezone = config('app.timezone');
$timezoneStr = is_string($timezone) ? $timezone : 'America/Managua';

// 1. Verificar lotes vencidos y próximos a caducar
if ($time = $shouldSchedule('jobs.inventario_caducidades', '06:00')) {
    Schedule::job(new VerificarCaducidadesJob)
        ->name('verificar-caducidades')
        ->dailyAt($time)
        ->withoutOverlapping()
        ->onOneServer()
        ->timezone($timezoneStr);
}

// 2. Generar mantenimientos preventivos lógicos (silencioso)
if ($time = $shouldSchedule('jobs.mtto_preventivo', '06:00')) {
    Schedule::job(new VerificarMantenimientosPreventivosJob)
        ->name('verificar-mantenimientos-preventivos')
        ->dailyAt($time)
        ->withoutOverlapping()
        ->onOneServer()
        ->timezone($timezoneStr);
}

// 3. Notificación unificada de mantenimientos (tecnicos asignados / admin pool, anti-spam)
if ($time = $shouldSchedule('jobs.mtto_notificar_proximos', '07:00')) {
    Schedule::job(new NotificarMantenimientosJob)
        ->name('notificar-mantenimientos')
        ->dailyAt($time)
        ->withoutOverlapping()
        ->onOneServer()
        ->timezone($timezoneStr);
}

// 4. Restaurar estados lógicos de activos completados
if ($time = $shouldSchedule('jobs.mtto_sincronizar', '06:40')) {
    Schedule::job(new SincronizarEstadoActivoJob)
        ->name('sincronizar-estado-activo')
        ->dailyAt($time)
        ->withoutOverlapping()
        ->onOneServer()
        ->timezone($timezoneStr);
}

// 5. Advertir garantías de activos a vencer en los siguientes 30 días
if ($time = $shouldSchedule('jobs.mtto_garantias', '06:15')) {
    Schedule::job(new VerificarGarantiasJob)
        ->name('verificar-garantias-activos')
        ->dailyAt($time)
        ->withoutOverlapping()
        ->onOneServer()
        ->timezone($timezoneStr);
}

// 6. Materializar ejecuciones de limpieza diariamente
if ($time = $shouldSchedule('jobs.limpieza_materializar', '05:30')) {
    Schedule::command('limpieza:materializar-ejecuciones')
        ->name('materializar-ejecuciones-limpieza')
        ->dailyAt($time)
        ->withoutOverlapping()
        ->onOneServer()
        ->timezone($timezoneStr);
}

// 7. Enviar recordatorios de limpieza pendientes/vencidos
if ($time = $shouldSchedule('jobs.limpieza_recordatorio', '12:00')) {
    Schedule::command('limpieza:enviar-recordatorios')
        ->name('enviar-recordatorios-limpieza')
        ->dailyAt($time)
        ->withoutOverlapping()
        ->onOneServer()
        ->timezone($timezoneStr);
}
