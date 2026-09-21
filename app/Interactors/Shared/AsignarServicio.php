<?php

declare(strict_types=1);

namespace App\Interactors\Shared;

use App\Repository\Models\Shared\ServicioAsignacion;
use App\Repository\Persistencia\Servicios\ServicioRepositorioInterface;

final readonly class AsignarServicio
{
    public function __construct(
        private ServicioRepositorioInterface $servicioRepositorio,
    ) {}

    public function execute(
        int $servicioId,
        string $serviceableType,
        int $serviceableId,
        bool $incluido = false,
        int $estado = 1,
    ): ServicioAsignacion {
        return $this->servicioRepositorio->asignarAServiceable(
            $servicioId,
            $serviceableType,
            $serviceableId,
            $incluido,
            $estado
        );
    }
}
