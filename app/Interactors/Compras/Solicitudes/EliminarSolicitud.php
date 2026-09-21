<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Solicitudes;

use App\Repository\Models\Compras\Solicitud;
use App\Repository\Persistencia\Compras\SolicitudRepositorioInterface;

final readonly class EliminarSolicitud
{
    public function __construct(
        private SolicitudRepositorioInterface $repositorio,
    ) {}

    public function ejecutar(Solicitud $solicitud): void
    {
        $this->repositorio->eliminar($solicitud);
    }
}
