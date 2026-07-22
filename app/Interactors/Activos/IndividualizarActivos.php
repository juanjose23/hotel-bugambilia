<?php

declare(strict_types=1);

namespace App\Interactors\Activos;

use App\BusinessLogic\Activos\ProcesadorIndividualizacionActivos;
use App\BusinessLogic\Activos\ReglasIndividualizacion;
use App\Enums\Activos\EstadoIndividualizacion;
use App\Repository\Persistencia\Activos\RegistroIndividualizacionRepositorioInterface;
use App\Repository\Queries\Catalogos\ObtenerUbicacionAlmacen;

class IndividualizarActivos
{
    public function __construct(
        private readonly ProcesadorIndividualizacionActivos $procesador,
        private readonly ReglasIndividualizacion $reglas,
        private readonly RegistroIndividualizacionRepositorioInterface $registroRepositorio,
        private readonly ObtenerUbicacionAlmacen $obtenerAlmacen,
    ) {}

    /** @param array<int, array<string, mixed>> $items */
    public function ejecutar(int $registroId, array $items, int $usuarioId): void
    {
        $registro = $this->registroRepositorio->buscarPorId($registroId);

        if (! $registro) {
            throw new \RuntimeException("No se encontró el registro de individualización con ID {$registroId}");
        }

        if ($registro->estado === EstadoIndividualizacion::Completado) {
            throw new \RuntimeException('Este registro de individualización ya ha sido completado.');
        }

        $this->reglas->validarCantidad($registro->cantidad_registrada, count($items), $registro->cantidad_total);

        $ubicacion = $this->obtenerAlmacen->ejecutar();

        if (! $ubicacion) {
            throw new \RuntimeException('No existe ninguna ubicación activa en el sistema.');
        }

        $this->procesador->procesar($registro, $items, $usuarioId, $ubicacion);
    }
}
