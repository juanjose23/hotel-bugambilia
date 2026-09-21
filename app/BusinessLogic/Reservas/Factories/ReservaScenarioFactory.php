<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Factories;

use App\BusinessLogic\Reservas\Contracts\ReservaScenarioHandlerInterface;
use App\BusinessLogic\Reservas\Handlers\HabitacionScenarioHandler;
use App\BusinessLogic\Reservas\Handlers\PaqueteScenarioHandler;
use App\BusinessLogic\Reservas\Handlers\RestauranteScenarioHandler;
use App\BusinessLogic\Reservas\Handlers\ServicioScenarioHandler;
use App\Enums\Reservas\TipoReserva;

final readonly class ReservaScenarioFactory
{
    public function __construct(
        private HabitacionScenarioHandler $habitacionHandler,
        private RestauranteScenarioHandler $restauranteHandler,
        private ServicioScenarioHandler $servicioHandler,
        private PaqueteScenarioHandler $paqueteHandler,
    ) {}

    public function fabricar(TipoReserva $tipo): ReservaScenarioHandlerInterface
    {
        return match ($tipo) {
            TipoReserva::HABITACION => $this->habitacionHandler,
            TipoReserva::RESTAURANTE => $this->restauranteHandler,
            TipoReserva::SERVICIO => $this->servicioHandler,
            TipoReserva::PAQUETE => $this->paqueteHandler,
        };
    }
}
