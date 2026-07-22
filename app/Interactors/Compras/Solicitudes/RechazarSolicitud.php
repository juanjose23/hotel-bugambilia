<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Solicitudes;

use App\Enums\Compras\EstadoSolicitud;
use App\Events\Compras\SolicitudRechazada;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Persistencia\Compras\SolicitudRepositorioInterface;

final class RechazarSolicitud
{
    public function __construct(
        private readonly SolicitudRepositorioInterface $solicitudRepositorio,
    ) {}

    public function ejecutar(Solicitud $solicitud): void
    {
        $this->solicitudRepositorio->actualizarEstado($solicitud, EstadoSolicitud::Rechazada);

        SolicitudRechazada::dispatch($solicitud);
    }
}
