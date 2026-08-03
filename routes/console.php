<?php

declare(strict_types=1);

use App\Jobs\Activos\NotificarMantenimientosJob;
use App\Jobs\Activos\SincronizarEstadoActivoJob;
use App\Jobs\Activos\VerificarGarantiasJob;
use App\Jobs\Activos\VerificarMantenimientosPreventivosJob;
use App\Jobs\Inventario\VerificarCaducidadesJob;
use App\Jobs\Reservas\EnviarRecordatoriosReservasJob;
use App\Jobs\Restaurante\ProcesarNoShowsRestauranteJob;
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

Artisan::command('restaurante:procesar-noshows', function (): void {
    ProcesarNoShowsRestauranteJob::dispatchSync();
    fwrite(STDOUT, "No-shows de restaurante procesados exitosamente.\n");
})->purpose('Procesar reservaciones de restaurante que excedieron la tolerancia de 30 minutos');

Artisan::command('mantenimiento:procesar-todos', function (): void {
    VerificarMantenimientosPreventivosJob::dispatchSync();
    NotificarMantenimientosJob::dispatchSync();
    SincronizarEstadoActivoJob::dispatchSync();
    VerificarGarantiasJob::dispatchSync();
    fwrite(STDOUT, "Todos los jobs de mantenimiento procesados síncronamente.\n");
})->purpose('Forzar la ejecución síncrona de todos los jobs de mantenimiento y garantías');

$registerScheduledEvent = function (mixed $event, string $key, string $default, string $timezoneStr): void {
    $time = config($key, $default);
    if (! is_string($time)) {
        return;
    }
    $strValue = strtolower(trim($time));
    if (in_array($strValue, ['null', 'false', 'disabled', 'none', ''], true)) {
        return;
    }

    if (is_string($event)) {
        $scheduled = Schedule::command($event);
    } else {
        assert(is_object($event));
        $scheduled = Schedule::job($event);
    }

    $scheduled->name($key)
        ->withoutOverlapping()
        ->onOneServer()
        ->timezone($timezoneStr);

    if ($strValue === 'hourly') {
        $scheduled->hourly();
    } elseif ($strValue === 'daily') {
        $scheduled->daily();
    } elseif ($strValue === 'everyminute') {
        $scheduled->everyMinute();
    } elseif ($strValue === 'everyfiveminutes') {
        $scheduled->everyFiveMinutes();
    } elseif ($strValue === 'everytenminutes') {
        $scheduled->everyTenMinutes();
    } elseif ($strValue === 'everyfifteenminutes') {
        $scheduled->everyFifteenMinutes();
    } elseif ($strValue === 'everythirtyminutes') {
        $scheduled->everyThirtyMinutes();
    } elseif (count(explode(' ', $time)) === 5) {
        $scheduled->cron($time);
    } else {
        $scheduled->dailyAt($time);
    }
};

$timezone = config('app.timezone');
$timezoneStr = is_string($timezone) ? $timezone : 'America/Managua';

// 1. Verificar lotes vencidos y próximos a caducar
$registerScheduledEvent(new VerificarCaducidadesJob, 'jobs.inventario_caducidades', '06:00', $timezoneStr);

// 2. Generar mantenimientos preventivos lógicos (silencioso)
$registerScheduledEvent(new VerificarMantenimientosPreventivosJob, 'jobs.mtto_preventivo', '06:00', $timezoneStr);

// 3. Notificación unificada de mantenimientos
$registerScheduledEvent(new NotificarMantenimientosJob, 'jobs.mtto_notificar_proximos', '07:00', $timezoneStr);

// 4. Restaurar estados lógicos de activos completados
$registerScheduledEvent(new SincronizarEstadoActivoJob, 'jobs.mtto_sincronizar', '06:40', $timezoneStr);

// 5. Advertir garantías de activos a vencer en los siguientes 30 días
$registerScheduledEvent(new VerificarGarantiasJob, 'jobs.mtto_garantias', '06:15', $timezoneStr);

// 6. Materializar ejecuciones de limpieza
$registerScheduledEvent('limpieza:materializar-ejecuciones', 'jobs.limpieza_materializar', 'hourly', $timezoneStr);

// 7. Enviar recordatorios de limpieza pendientes/vencidos
$registerScheduledEvent('limpieza:enviar-recordatorios', 'jobs.limpieza_recordatorio', '12:00', $timezoneStr);

// 8. Alertar al personal autorizado antes del inicio de una reserva
$registerScheduledEvent(new EnviarRecordatoriosReservasJob, 'jobs.reservas_recordatorio', 'everyfiveminutes', $timezoneStr);

// 9. Procesar reservaciones No-Show de restaurante (tolerancia 30 min)
$registerScheduledEvent(new ProcesarNoShowsRestauranteJob, 'jobs.restaurante_noshows', 'everyfifteenminutes', $timezoneStr);
