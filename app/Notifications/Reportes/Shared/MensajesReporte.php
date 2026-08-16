<?php

declare(strict_types=1);

namespace App\Notifications\Reportes\Shared;

use App\Enums\Notifications\CanalNotificacion;
use App\Enums\Notifications\TipoNotificacion;
use App\Notifications\DatosNotificacion;
use Filament\Actions\Action;

final class MensajesReporte
{
    public function reporteEnProceso(string $codigoReporte): DatosNotificacion
    {
        return new DatosNotificacion(
            title: 'Reporte en proceso',
            body: "El reporte {$codigoReporte} se esta generando. Recibiras una notificacion cuando este listo.",
            type: TipoNotificacion::Info,
        );
    }

    public function reporteListo(string $codigoReporte, ?string $urlDescarga = null): DatosNotificacion
    {
        $actions = [];

        if ($urlDescarga !== null) {
            $actions[] = Action::make('descargar')
                ->label('Descargar')
                ->url($urlDescarga, shouldOpenInNewTab: true)
                ->color('success')
                ->icon('heroicon-m-arrow-down-tray');
        }

        return new DatosNotificacion(
            title: 'Reporte listo',
            body: "El reporte {$codigoReporte} ha sido generado y esta disponible.",
            type: TipoNotificacion::Success,
            actions: $actions,
            channels: [CanalNotificacion::BaseDeDatos, CanalNotificacion::Correo],
        );
    }
}
