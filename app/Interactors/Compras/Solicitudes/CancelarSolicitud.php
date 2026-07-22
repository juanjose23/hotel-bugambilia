<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Solicitudes;

use App\Events\Compras\SolicitudCancelada;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Persistencia\Compras\SolicitudRepositorioInterface;

final class CancelarSolicitud
{
    public function __construct(
        private readonly SolicitudRepositorioInterface $solicitudRepositorio,
    ) {}

    /**
     * @param  list<array{id: int, cantidad_aprobada: float|int|string}>  $itemsCancelacion
     */
    public function ejecutar(
        Solicitud $solicitud,
        array $itemsCancelacion = [],
        string $notaCompras = 'Cancelado desde listado de solicitudes'
    ): void {
        $this->solicitudRepositorio->cancelar($solicitud, $itemsCancelacion, $notaCompras);

        SolicitudCancelada::dispatch($solicitud);
    }
}
