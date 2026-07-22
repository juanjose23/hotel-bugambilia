<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Solicitudes;

use App\Enums\Compras\EstadoSolicitud;
use App\Events\Compras\SolicitudAprobada;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Persistencia\Compras\SolicitudRepositorioInterface;
use Illuminate\Support\Facades\DB;

final class AprobarSolicitud
{
    public function __construct(
        private readonly SolicitudRepositorioInterface $solicitudRepositorio,
    ) {}

    /**
     * @param  list<array{id: int, cantidad_aprobada: float|int|string}>  $itemsAprobados
     */
    public function ejecutar(Solicitud $solicitud, array $itemsAprobados = []): void
    {
        DB::transaction(function () use ($solicitud, $itemsAprobados): void {
            $this->solicitudRepositorio->actualizarCantidadesAprobadas($solicitud, $itemsAprobados);
            $this->solicitudRepositorio->actualizarEstado($solicitud, EstadoSolicitud::Aprobada);
        });

        SolicitudAprobada::dispatch($solicitud);
    }
}
