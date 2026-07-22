<?php

declare(strict_types=1);

namespace App\Notifications\Activos;

use App\Notifications\NotificadorBase;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoMantenimiento;
use App\Repository\Models\User;
use Illuminate\Support\Collection;

final class NotificadorActivos extends NotificadorBase
{
    public function __construct(
        private readonly DestinatariosActivos $destinatarios,
        private readonly MensajesActivos $mensajes,
    ) {}

    /** @param Collection<int, User> $destinatarios */
    public function mantenimientoProximo(ActivoMantenimiento $mantenimiento, int $dias, Collection $destinatarios): void
    {
        $this->enviar($destinatarios, $this->mensajes->mantenimientoProximo($mantenimiento, $dias));
    }

    /** @param Collection<int, User> $destinatarios */
    public function mantenimientoAtrasado(ActivoMantenimiento $mantenimiento, int $dias, Collection $destinatarios): void
    {
        $this->enviar($destinatarios, $this->mensajes->mantenimientoAtrasado($mantenimiento, $dias));
    }

    /** @param Collection<int, User> $destinatarios */
    public function mantenimientoProlongado(ActivoMantenimiento $mantenimiento, int $dias, Collection $destinatarios): void
    {
        $this->enviar($destinatarios, $this->mensajes->mantenimientoProlongado($mantenimiento, $dias));
    }

    public function garantiaProxima(Activo $activo, int $dias): void
    {
        $usuarios = $this->destinatarios->obtener();

        $this->enviar($usuarios, $this->mensajes->garantiaProxima($activo, $dias));
    }
}
