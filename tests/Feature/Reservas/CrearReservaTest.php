<?php

declare(strict_types=1);

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\Reservas\CrearReserva;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\Reserva;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Database\Seeders\HabitacionSeeder;
use Database\Seeders\PaisSeeder;

test('se puede crear una reserva de habitación correctamente mediante el interactor', function () {
    $this->seed([
        PaisSeeder::class,
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        HabitacionSeeder::class,
    ]);

    $habitacion = Habitacion::first();

    $interactor = app(CrearReserva::class);

    $reserva = $interactor->ejecutar([
        'nombre_cliente' => 'Juan José',
        'telefono_cliente' => '+505 8888 8888',
        'email_cliente' => 'juan@ejemplo.com',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion?->id,
        'fecha_check_in' => '2026-08-01',
        'fecha_check_out' => '2026-08-03',
        'adultos' => 2,
        'ninos' => 0,
        'total' => 150.00,
    ]);

    expect($reserva)->toBeInstanceOf(Reserva::class)
        ->and($reserva->nombre_cliente)->toBe('Juan José')
        ->and($reserva->estado)->toBe(EstadoReserva::PENDIENTE)
        ->and($reserva->codigo_reserva)->toContain('RES-2026-');

    $this->assertDatabaseHas('reservas', [
        'id' => $reserva->id,
        'nombre_cliente' => 'Juan José',
        'codigo_reserva' => $reserva->codigo_reserva,
    ]);
});

test('se puede realizar una petición POST a /reservas para crear una reservación', function () {
    $this->seed([
        PaisSeeder::class,
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        HabitacionSeeder::class,
    ]);

    $habitacion = Habitacion::first();

    $response = $this->post(route('reservas.crear'), [
        'nombre_cliente' => 'María Lopez',
        'telefono_cliente' => '+505 7777 7777',
        'email_cliente' => 'maria@ejemplo.com',
        'tipo_reserva' => 'habitacion',
        'habitacion_id' => $habitacion?->id,
        'fecha_check_in' => '2026-09-10',
        'fecha_check_out' => '2026-09-12',
        'adultos' => 2,
        'total' => 200.00,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('reservas', [
        'nombre_cliente' => 'María Lopez',
        'email_cliente' => 'maria@ejemplo.com',
    ]);
});
