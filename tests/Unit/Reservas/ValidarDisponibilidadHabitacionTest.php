<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\ValidarDisponibilidadHabitacion;
use App\Repository\Queries\Reservas\ConsultarHabitacionesDisponibles;
use Illuminate\Support\Collection;

test('validar disponibilidad lanza excepcion si checkout es anterior o igual a checkin', function (): void {
    $consultarMock = Mockery::mock(ConsultarHabitacionesDisponibles::class);
    $service = new ValidarDisponibilidadHabitacion($consultarMock);

    $fechaIn = now()->addDays(2);
    $fechaOut = now()->addDay(1);

    $service->validarDisponibilidad($fechaIn, $fechaOut, [1]);
})->throws(DomainException::class, 'La fecha de salida debe ser posterior a la fecha de entrada.');

test('validar disponibilidad lanza excepcion si no se seleccionan habitaciones', function (): void {
    $consultarMock = Mockery::mock(ConsultarHabitacionesDisponibles::class);
    $service = new ValidarDisponibilidadHabitacion($consultarMock);

    $fechaIn = now()->addDays(1);
    $fechaOut = now()->addDays(3);

    $service->validarDisponibilidad($fechaIn, $fechaOut, []);
})->throws(DomainException::class, 'Debe seleccionar al menos una habitación.');

test('validar disponibilidad lanza excepcion si la habitacion solicitada no esta en las disponibles', function (): void {
    $consultarMock = Mockery::mock(ConsultarHabitacionesDisponibles::class);
    $consultarMock->shouldReceive('ejecutar')
        ->once()
        ->andReturn(new Collection([]));

    $service = new ValidarDisponibilidadHabitacion($consultarMock);

    $fechaIn = now()->addDays(1);
    $fechaOut = now()->addDays(3);

    $service->validarDisponibilidad($fechaIn, $fechaOut, [999]);
})->throws(DomainException::class, 'La habitación solicitada (ID: 999) no está disponible');
