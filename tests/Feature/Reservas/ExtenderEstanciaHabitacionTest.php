<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\Data\ExtenderEstanciaData;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Events\Reservas\EstanciaHabitacionExtendida;
use App\Interactors\Reservas\Habitaciones\ExtenderEstanciaHabitacion;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use Illuminate\Support\Facades\Event;

test('extiende estancia con nueva fecha de salida', function (): void {
    Event::fake([EstanciaHabitacionExtendida::class]);

    $recurso = RecursoReservable::query()->create([
        'nombre' => 'Hab Extender',
        'tipo' => 1,
        'control_disponibilidad' => 1,
        'estado' => 1,
    ]);

    $habitacion = Habitacion::factory()->create([
        'reservable_id' => $recurso->id,
        'estado' => EstadoEspacio::Ocupado,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-EXT-'.Str::random(5),
        'nombre_cliente' => 'Cliente Extender',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->subDay()->toDateString(),
        'fecha_check_out' => now()->addDay()->toDateString(),
        'estado' => EstadoReserva::CHECKED_IN,
    ]);

    $detalle = ReservaDetalle::query()->create([
        'reserva_id' => $reserva->id,
        'reservable_id' => $recurso->id,
        'estado' => 3,
        'fecha_inicio' => now()->subDay(),
        'fecha_fin' => now()->addDay(),
    ]);

    $estancia = Estancia::query()->create([
        'reserva_id' => $reserva->id,
        'reserva_detalle_id' => $detalle->id,
        'habitacion_id' => $habitacion->id,
        'check_in_at' => now()->subDay(),
        'fecha_salida_programada' => now()->addDay(),
        'estado' => EstadoEstancia::ACTIVA,
    ]);

    $nuevaFechaSalida = now()->addDays(3);

    $resultado = app(ExtenderEstanciaHabitacion::class)->ejecutar(
        new ExtenderEstanciaData(
            estanciaId: $estancia->id,
            nuevaFechaSalida: $nuevaFechaSalida,
            observaciones: 'Extension por solicitud del cliente',
        ),
    );

    expect($resultado->estado)->toBe(EstadoEstancia::EXTENDIDA);
    Event::assertDispatched(EstanciaHabitacionExtendida::class);
});

test('lanza excepcion cuando nueva fecha no es posterior a la actual', function (): void {
    $recurso = RecursoReservable::query()->create([
        'nombre' => 'Hab NoExtender',
        'tipo' => 1,
        'control_disponibilidad' => 1,
        'estado' => 1,
    ]);

    $habitacion = Habitacion::factory()->create([
        'reservable_id' => $recurso->id,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-NOEXT-'.Str::random(5),
        'nombre_cliente' => 'Cliente NoExtender',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->subDay()->toDateString(),
        'fecha_check_out' => now()->addDay()->toDateString(),
        'estado' => EstadoReserva::CHECKED_IN,
    ]);

    $detalle = ReservaDetalle::query()->create([
        'reserva_id' => $reserva->id,
        'reservable_id' => $recurso->id,
        'estado' => 3,
        'fecha_inicio' => now()->subDay(),
        'fecha_fin' => now()->addDay(),
    ]);

    $estancia = Estancia::query()->create([
        'reserva_id' => $reserva->id,
        'reserva_detalle_id' => $detalle->id,
        'habitacion_id' => $habitacion->id,
        'check_in_at' => now()->subDay(),
        'fecha_salida_programada' => now()->addDay(),
        'estado' => EstadoEstancia::ACTIVA,
    ]);

    app(ExtenderEstanciaHabitacion::class)->ejecutar(
        new ExtenderEstanciaData(
            estanciaId: $estancia->id,
            nuevaFechaSalida: now()->subDay(),
        ),
    );
})->throws(DomainException::class, 'posterior a la fecha de salida actual');

test('lanza excepcion cuando estancia no esta activa', function (): void {
    $recurso = RecursoReservable::query()->create([
        'nombre' => 'Hab Finalizada',
        'tipo' => 1,
        'control_disponibilidad' => 1,
        'estado' => 1,
    ]);

    $habitacion = Habitacion::factory()->create([
        'reservable_id' => $recurso->id,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-FINAL-'.Str::random(5),
        'nombre_cliente' => 'Cliente Finalizada',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->subDays(5)->toDateString(),
        'fecha_check_out' => now()->subDays(2)->toDateString(),
        'estado' => EstadoReserva::CHECKED_OUT,
    ]);

    $estancia = Estancia::query()->create([
        'reserva_id' => $reserva->id,
        'habitacion_id' => $habitacion->id,
        'check_in_at' => now()->subDays(5),
        'check_out_at' => now()->subDays(2),
        'estado' => EstadoEstancia::FINALIZADA,
    ]);

    app(ExtenderEstanciaHabitacion::class)->ejecutar(
        new ExtenderEstanciaData(
            estanciaId: $estancia->id,
            nuevaFechaSalida: now()->addDays(2),
        ),
    );
})->throws(DomainException::class, 'no se encuentra activa');
