<?php

declare(strict_types=1);

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Events\Reservas\HuespedModificado;
use App\Interactors\Reservas\Gestion\RegistrarHuespedes;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use Illuminate\Support\Facades\Event;

function crearReservaConDetalle(int $adultos = 2): array
{
    $recurso = RecursoReservable::query()->create([
        'nombre' => 'Hab Huespedes',
        'tipo' => 1,
        'control_disponibilidad' => 1,
        'estado' => 1,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-HUES-'.Str::random(5),
        'nombre_cliente' => 'Cliente Huespedes',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->addDay()->toDateString(),
        'fecha_check_out' => now()->addDays(3)->toDateString(),
        'estado' => EstadoReserva::CONFIRMADA,
        'adultos' => $adultos,
    ]);

    $detalle = ReservaDetalle::query()->create([
        'reserva_id' => $reserva->id,
        'reservable_id' => $recurso->id,
        'estado' => 2,
        'fecha_inicio' => now()->addDay(),
        'fecha_fin' => now()->addDays(3),
        'cantidad' => 1,
        'adultos' => $adultos,
    ]);

    return [$reserva, $detalle];
}

test('agrega un huesped a la reserva', function (): void {
    Event::fake([HuespedModificado::class]);

    [$reserva] = crearReservaConDetalle();

    $huesped = app(RegistrarHuespedes::class)->agregar($reserva, [
        'nombre' => 'Juan Perez',
        'tipo_huesped' => 1,
        'tipo_identificacion' => 1,
        'es_titular' => true,
        'identificacion' => '12345678',
    ]);

    expect($huesped->nombre)->toBe('Juan Perez');
    expect($huesped->es_titular)->toBeTrue();
    Event::assertDispatched(HuespedModificado::class);
});

test('actualiza un huesped existente', function (): void {
    Event::fake([HuespedModificado::class]);

    [$reserva] = crearReservaConDetalle();

    $huesped = app(RegistrarHuespedes::class)->agregar($reserva, [
        'nombre' => 'Maria Garcia',
        'tipo_huesped' => 1,
        'tipo_identificacion' => 1,
        'es_titular' => true,
        'identificacion' => '87654321',
    ]);

    $actualizado = app(RegistrarHuespedes::class)->actualizar($reserva, $huesped, [
        'nombre' => 'Maria Garcia Lopez',
    ]);

    expect($actualizado->nombre)->toBe('Maria Garcia Lopez');
});

test('elimina un huesped no titular', function (): void {
    Event::fake([HuespedModificado::class]);

    [$reserva] = crearReservaConDetalle(3);

    $titular = app(RegistrarHuespedes::class)->agregar($reserva, [
        'nombre' => 'Titular Principal',
        'tipo_huesped' => 1,
        'tipo_identificacion' => 1,
        'es_titular' => true,
        'identificacion' => '11111111',
    ]);

    $companero = app(RegistrarHuespedes::class)->agregar($reserva, [
        'nombre' => 'Companero',
        'tipo_huesped' => 1,
        'tipo_identificacion' => 1,
        'es_titular' => false,
        'identificacion' => '22222222',
    ]);

    app(RegistrarHuespedes::class)->eliminar($reserva, $companero);

    expect($reserva->huespedes()->count())->toBe(1);
});

test('lanza excepcion al eliminar huesped titular', function (): void {
    [$reserva] = crearReservaConDetalle();

    $titular = app(RegistrarHuespedes::class)->agregar($reserva, [
        'nombre' => 'Titular',
        'tipo_huesped' => 1,
        'tipo_identificacion' => 1,
        'es_titular' => true,
        'identificacion' => '33333333',
    ]);

    app(RegistrarHuespedes::class)->eliminar($reserva, $titular);
})->throws(DomainException::class, 'No se puede eliminar el huésped titular');

test('lanza excepcion al agregar huesped despues de checkin', function (): void {
    [$reserva] = crearReservaConDetalle();
    $reserva->update(['estado' => EstadoReserva::CHECKED_IN]);

    app(RegistrarHuespedes::class)->agregar($reserva, [
        'nombre' => 'Post Check-in',
        'tipo_huesped' => 1,
        'tipo_identificacion' => 1,
        'identificacion' => '44444444',
    ]);
})->throws(DomainException::class);
