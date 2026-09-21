<?php

declare(strict_types=1);

namespace App\Notifications\Reportes\Shared;

use App\Notifications\NotificadorBase;
use App\Repository\Models\User;

final class NotificadorReportes extends NotificadorBase
{
    public function __construct(
        private readonly DestinatariosReporte $destinatarios,
        private readonly MensajesReporte $mensajes,
    ) {}

    public function reporteEnProceso(User $usuario, string $codigoReporte): void
    {
        $usuarios = $this->destinatarios->obtener($usuario);

        $this->enviar($usuarios, $this->mensajes->reporteEnProceso($codigoReporte));
    }

    public function reporteListo(User $usuario, string $codigoReporte, ?string $urlDescarga = null): void
    {
        $usuarios = $this->destinatarios->obtener($usuario);

        $this->enviar($usuarios, $this->mensajes->reporteListo($codigoReporte, $urlDescarga));
    }

    public function reporteFallido(User $usuario, string $codigoReporte, string $motivo): void
    {
        $usuarios = $this->destinatarios->obtener($usuario);

        $this->enviar($usuarios, $this->mensajes->reporteFallido($codigoReporte, $motivo));
    }
}
