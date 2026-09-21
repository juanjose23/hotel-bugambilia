<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\Factories\ReservaScenarioFactory;
use App\BusinessLogic\Reservas\Handlers\HabitacionScenarioHandler;
use App\BusinessLogic\Reservas\Handlers\PaqueteScenarioHandler;
use App\BusinessLogic\Reservas\Handlers\RestauranteScenarioHandler;
use App\BusinessLogic\Reservas\Handlers\ServicioScenarioHandler;
use App\Enums\Reservas\TipoReserva;
use Tests\TestCase;

uses(TestCase::class);

test('scenario factory resuelve el handler correspondiente para cada TipoReserva', function (): void {
    $habitacionHandler = app(HabitacionScenarioHandler::class);
    $restauranteHandler = app(RestauranteScenarioHandler::class);
    $servicioHandler = app(ServicioScenarioHandler::class);
    $paqueteHandler = app(PaqueteScenarioHandler::class);

    $factory = new ReservaScenarioFactory(
        $habitacionHandler,
        $restauranteHandler,
        $servicioHandler,
        $paqueteHandler,
    );

    expect($factory->fabricar(TipoReserva::HABITACION))->toBe($habitacionHandler)
        ->and($factory->fabricar(TipoReserva::RESTAURANTE))->toBe($restauranteHandler)
        ->and($factory->fabricar(TipoReserva::SERVICIO))->toBe($servicioHandler)
        ->and($factory->fabricar(TipoReserva::PAQUETE))->toBe($paqueteHandler);
});
