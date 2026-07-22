<?php

declare(strict_types=1);

namespace App\Notifications\Activos;

use App\Enums\Notifications\TipoNotificacion;
use App\Notifications\DatosNotificacion;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoMantenimiento;
use Filament\Actions\Action;

final class MensajesActivos
{
    public function __construct(
        private readonly UrlNotificador $url,
    ) {}

    public function mantenimientoProximo(ActivoMantenimiento $mantenimiento, int $dias): DatosNotificacion
    {
        $nombre = $this->resolverNombreActivo($mantenimiento);

        return new DatosNotificacion(
            title: "Mantenimiento próximo: {$nombre}",
            body: "El mantenimiento programado para \"{$nombre}\" está en {$dias} días.",
            type: TipoNotificacion::MaintenanceScheduled,
            actions: [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->mantenimiento($mantenimiento))
                    ->button(),
            ],
        );
    }

    public function mantenimientoAtrasado(ActivoMantenimiento $mantenimiento, int $dias): DatosNotificacion
    {
        $nombre = $this->resolverNombreActivo($mantenimiento);

        return new DatosNotificacion(
            title: "Mantenimiento atrasado: {$nombre}",
            body: "El mantenimiento de \"{$nombre}\" lleva {$dias} días de atrasado.",
            type: TipoNotificacion::MaintenanceOverdueNotification,
            actions: [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->mantenimiento($mantenimiento))
                    ->button(),
            ],
        );
    }

    public function mantenimientoProlongado(ActivoMantenimiento $mantenimiento, int $dias): DatosNotificacion
    {
        $nombre = $this->resolverNombreActivo($mantenimiento);

        return new DatosNotificacion(
            title: "Mantenimiento prolongado: {$nombre}",
            body: "El mantenimiento en proceso de \"{$nombre}\" lleva {$dias} días sin completarse.",
            type: TipoNotificacion::MaintenanceInProgress,
            actions: [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->mantenimiento($mantenimiento))
                    ->button(),
            ],
        );
    }

    public function garantiaProxima(Activo $activo, int $dias): DatosNotificacion
    {
        return new DatosNotificacion(
            title: "Garantía próxima a vencer: {$activo->nombre_descriptivo}",
            body: "La garantía del activo \"{$activo->nombre_descriptivo}\" vence en {$dias} días.",
            type: TipoNotificacion::WarrantyExpiring,
            actions: [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->activo($activo))
                    ->button(),
            ],
        );
    }

    private function resolverNombreActivo(ActivoMantenimiento $mantenimiento): string
    {
        $activo = $mantenimiento->activo;

        return $activo->nombre_descriptivo ?? "Activo #{$mantenimiento->activo_id}";
    }
}
