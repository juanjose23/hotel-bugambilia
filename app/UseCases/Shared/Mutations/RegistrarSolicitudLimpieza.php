<?php

declare(strict_types=1);

namespace App\UseCases\Shared\Mutations;

use App\Models\Limpieza\SolicitudLimpieza;
use App\UseCases\Limpieza\Mutations\RegistrarSolicitudLimpieza as LimpiezaRegistrarSolicitudLimpieza;

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
