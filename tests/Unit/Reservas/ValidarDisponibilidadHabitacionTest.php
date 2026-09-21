<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\Validaciones\ValidarDisponibilidadHabitacion;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

test('validar disponibilidad lanza excepcion si checkout es anterior o igual a checkin', function (): void {
    $service = app(ValidarDisponibilidadHabitacion::class);

    $fechaIn = now()->addDays(2);
    $fechaOut = now()->addDays(1);

    $service->validarDisponibilidad($fechaIn, $fechaOut, [1]);
})->throws(DomainException::class, 'La fecha de salida debe ser posterior a la fecha de entrada.');

test('validar disponibilidad lanza excepcion si no se seleccionan habitaciones', function (): void {
    $service = app(ValidarDisponibilidadHabitacion::class);

    $fechaIn = now()->addDays(1);
    $fechaOut = now()->addDays(3);

    $service->validarDisponibilidad($fechaIn, $fechaOut, []);
})->throws(DomainException::class, 'Debe seleccionar al menos una habitación.');

test('validar disponibilidad lanza excepcion si la habitacion solicitada no esta en las disponibles', function (): void {
    $service = app(ValidarDisponibilidadHabitacion::class);

    $fechaIn = now()->addDays(1);
    $fechaOut = now()->addDays(3);

    $service->validarDisponibilidad($fechaIn, $fechaOut, [999]);
})->throws(DomainException::class, 'La habitación solicitada (ID: 999) no está disponible');
