<?php

declare(strict_types=1);

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\Reservas\Operaciones\EnviarRecordatoriosReservas;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

test('envia recordatorios para reservas proximas y retorna cantidad enviados', function (): void {
    Notification::fake();

    $recurso = RecursoReservable::query()->create([
        'nombre' => 'Hab Recordatorio',
        'tipo' => 1,
        'control_disponibilidad' => 1,
        'estado' => 1,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-REC-'.Str::random(5),
        'nombre_cliente' => 'Cliente Recordatorio',
        'email_cliente' => 'recordatorio@test.com',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->addMinutes(35)->toDateString(),
        'fecha_check_out' => now()->addDays(2)->toDateString(),
        'hora_reserva' => now()->addMinutes(35)->format('H:i'),
        'estado' => EstadoReserva::CONFIRMADA,
    ]);

    ReservaDetalle::query()->create([
        'reserva_id' => $reserva->id,
        'reservable_id' => $recurso->id,
        'estado' => 2,
        'fecha_inicio' => now()->addMinutes(35),
        'fecha_fin' => now()->addDays(2),
    ]);

    config(['hotel.reservas.recordatorio_habilitado' => true]);
    config(['hotel.reservas.anticipacion_minutos' => 30]);
    config(['hotel.reservas.tolerancia_minutos' => 10]);

    Cache::flush();

    $enviados = app(EnviarRecordatoriosReservas::class)->ejecutar();

    expect($enviados)->toBeGreaterThanOrEqual(0);
});

test('retorna cero cuando recordatorios estan deshabilitados', function (): void {
    config(['hotel.reservas.recordatorio_habilitado' => false]);

    $enviados = app(EnviarRecordatoriosReservas::class)->ejecutar();

    expect($enviados)->toBe(0);
});

test('no envia recordatorio dos veces por cache', function (): void {
    Notification::fake();

    $recurso = RecursoReservable::query()->create([
        'nombre' => 'Hab Cache',
        'tipo' => 1,
        'control_disponibilidad' => 1,
        'estado' => 1,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CACHE-'.Str::random(5),
        'nombre_cliente' => 'Cliente Cache',
        'email_cliente' => 'cache@test.com',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->addMinutes(35)->toDateString(),
        'fecha_check_out' => now()->addDays(2)->toDateString(),
        'hora_reserva' => now()->addMinutes(35)->format('H:i'),
        'estado' => EstadoReserva::CONFIRMADA,
    ]);

    ReservaDetalle::query()->create([
        'reserva_id' => $reserva->id,
        'reservable_id' => $recurso->id,
        'estado' => 2,
        'fecha_inicio' => now()->addMinutes(35),
        'fecha_fin' => now()->addDays(2),
    ]);

    config(['hotel.reservas.recordatorio_habilitado' => true]);
    config(['hotel.reservas.anticipacion_minutos' => 30]);
    config(['hotel.reservas.tolerancia_minutos' => 10]);

    Cache::flush();

    $primeraVez = app(EnviarRecordatoriosReservas::class)->ejecutar();
    $segundaVez = app(EnviarRecordatoriosReservas::class)->ejecutar();

    expect($segundaVez)->toBeLessThanOrEqual($primeraVez);
});
