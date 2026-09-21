<?php

declare(strict_types=1);

namespace App\Interactors\Servicios;

use App\Repository\Models\Servicios\Servicio;
use Illuminate\Validation\ValidationException;

final readonly class ValidarCupoServicio
{
    public function __construct(
        private ObtenerAforoServicio $obtenerAforo,
    ) {}

    /**
     * Valida que la cantidad de participantes de una salida o recorrido del servicio
     * no supere el aforo derivado de la flota fija asignada al servicio.
     *
     * @throws ValidationException Si los participantes sobrepasan el aforo del servicio.
     */
    public function ejecutar(int $servicioId, int $participantes): void
    {
        $servicio = Servicio::query()->findOrFail($servicioId);
        $aforo = $this->obtenerAforo->ejecutar($servicioId);

        if ($participantes <= $aforo) {
            return;
        }

        $nombreRaw = $servicio->getAttribute('nombre');
        $nombre = is_string($nombreRaw) && trim($nombreRaw) !== ''
            ? trim($nombreRaw)
            : "servicio #{$servicioId}";

        throw ValidationException::withMessages([
            'adultos' => "El cupo del recorrido {$nombre} está completo (aforo: {$aforo}).",
        ]);
    }
}
