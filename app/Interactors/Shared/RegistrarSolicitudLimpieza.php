<?php

declare(strict_types=1);

namespace App\Interactors\Shared;

use App\Interactors\Limpieza\Ejecucion\RegistrarSolicitudLimpieza as LimpiezaRegistrarSolicitudLimpieza;
use App\Repository\Models\Limpieza\SolicitudLimpieza;

class RegistrarSolicitudLimpieza
{
    public function __construct(
        protected LimpiezaRegistrarSolicitudLimpieza $registrarSolicitudLimpieza
    ) {}

    /**
     * @param  mixed  $limpiable
     */
    public function execute(
        $limpiable,
        ?int $limpiableId = null,
        string $prioridad = 'normal',
        ?string $notas = null,
    ): SolicitudLimpieza {
        return $this->registrarSolicitudLimpieza->execute($limpiable, $limpiableId, $prioridad, $notas);
    }
}
