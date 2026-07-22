<?php

declare(strict_types=1);

namespace App\Notifications\Limpieza;

use App\Notifications\NotificadorBase;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Models\Limpieza\Turno;
use App\Repository\Models\User;
use Illuminate\Support\Collection;

final class NotificadorLimpieza extends NotificadorBase
{
    public function __construct(
        private readonly DestinatariosLimpieza $destinatarios,
        private readonly MensajesLimpieza $mensajes,
    ) {}

    /**
     * Notifica el registro de una nueva solicitud de limpieza.
     */
    public function nuevaSolicitudLimpieza(SolicitudLimpieza $solicitud): void
    {
        $usuarios = $this->destinatarios->obtener();

        $this->enviar($usuarios, $this->mensajes->nuevaSolicitud($solicitud));
    }

    /**
     * Notifica a un operario asignado que tiene una nueva tarea.
     */
    public function personalAsignado(SolicitudLimpieza $solicitud): void
    {
        $usuarios = $this->destinatarios->obtener();

        $this->enviar($usuarios, $this->mensajes->personalAsignado($solicitud));
    }

    /**
     * @param  array<int, array{variante_id: int|null, nombre: string, required: float, available: float}>  $items
     */
    public function faltanteReposicion(LimpiezaEjecucion $ejecucion, array $items, User $destinatario): void
    {
        $nombres = array_map(static fn (array $item): string => (string) $item['nombre'], $items);
        $this->enviar($destinatario, $this->mensajes->faltanteReposicion($ejecucion, $nombres));
    }

    /**
     * @param  Collection<int, User>  $destinatarios
     */
    public function recordatorioPendiente(LimpiezaEjecucion $ejecucion, Collection $destinatarios): void
    {
        $this->enviar($destinatarios, $this->mensajes->recordatorioPendiente($ejecucion));
    }

    /**
     * @param  Collection<int, User>  $destinatarios
     */
    public function nuevasAsignaciones(Turno $turno, int $cantidad, Collection $destinatarios): void
    {
        $this->enviar($destinatarios, $this->mensajes->nuevasAsignaciones($turno, $cantidad));
    }
}
