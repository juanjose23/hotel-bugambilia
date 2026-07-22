<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Compras;

use App\Enums\Compras\EstadoSolicitud;
use App\Repository\Models\Compras\Solicitud;

interface SolicitudRepositorioInterface
{
    /**
     * @param  array<string, mixed>  $datos
     */
    public function crear(array $datos): Solicitud;

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<int, array<string, mixed>>  $items
     */
    public function crearConItems(array $datos, array $items): Solicitud;

    public function actualizarEstado(Solicitud $solicitud, EstadoSolicitud $estado): void;

    /**
     * @param  array<int, array{id: int, cantidad_aprobada: float|int|string}>  $items
     */
    public function actualizarCantidadesAprobadas(Solicitud $solicitud, array $items): void;

    /**
     * @param  array<int, array{cantidad_aprobada: float|int|string}>  $itemsCancelacion
     */
    public function cancelar(Solicitud $solicitud, array $itemsCancelacion, string $nota): void;
}
