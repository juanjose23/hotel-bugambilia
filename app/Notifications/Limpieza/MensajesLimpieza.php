<?php

declare(strict_types=1);

namespace App\Notifications\Limpieza;

use App\Enums\Notifications\TipoNotificacion;
use App\Notifications\DatosNotificacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Models\Limpieza\Turno;
use Filament\Actions\Action;

final class MensajesLimpieza
{
    public function __construct(
        private readonly UrlNotificador $url,
    ) {}

    public function nuevaSolicitud(SolicitudLimpieza $solicitud): DatosNotificacion
    {
        $tipo = $solicitud->limpiable_type ?? 'área';
        $id = $solicitud->limpiable_id;

        return new DatosNotificacion(
            title: 'Nueva solicitud de limpieza',
            body: "Se ha registrado una solicitud de limpieza para {$tipo} #{$id}.",
            type: TipoNotificacion::CleaningRequestCreated,
            actions: [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->solicitud($solicitud))
                    ->button(),
            ],
        );
    }

    public function personalAsignado(SolicitudLimpieza $solicitud): DatosNotificacion
    {
        $nombrePersonal = $solicitud->personal->name ?? 'personal';
        $tipo = $solicitud->limpiable_type ?? 'área';
        $id = $solicitud->limpiable_id;

        return new DatosNotificacion(
            title: 'Personal asignado a limpieza',
            body: "Se ha asignado a {$nombrePersonal} la limpieza de {$tipo} #{$id}.",
            type: TipoNotificacion::CleaningStaffAssigned,
            actions: [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->solicitud($solicitud))
                    ->button(),
            ],
        );
    }

    /** @param array<int, string> $items */
    public function faltanteReposicion(LimpiezaEjecucion $ejecucion, array $items): DatosNotificacion
    {
        $lista = implode(', ', $items);

        return new DatosNotificacion(
            title: 'Faltante de reposición detectado',
            body: "La ejecución #{$ejecucion->id} reporta faltantes en: {$lista}.",
            type: TipoNotificacion::CleaningSuppliesMissing,
            actions: [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->ejecucion($ejecucion))
                    ->button(),
            ],
        );
    }

    public function recordatorioPendiente(LimpiezaEjecucion $ejecucion): DatosNotificacion
    {
        $limpiable = $ejecucion->limpiable;
        $nombre = is_object($limpiable) ? ($limpiable->nombre ?? 'Área sin nombre') : 'Área sin nombre';
        $hora = $ejecucion->horario->hora_estimada ?? 'No definida';

        return new DatosNotificacion(
            title: 'Recordatorio de limpieza pendiente',
            body: "Tienes una tarea de limpieza pendiente en: {$nombre}. Hora estimada: {$hora}.",
            type: TipoNotificacion::CleaningReminder,
            actions: [
                Action::make('view')
                    ->label('Ver Tarea')
                    ->url($this->url->ejecucion($ejecucion))
                    ->button(),
            ],
        );
    }

    public function nuevasAsignaciones(Turno $turno, int $cantidad): DatosNotificacion
    {
        return new DatosNotificacion(
            title: 'Nuevas asignaciones de limpieza',
            body: "Se han generado {$cantidad} nuevas tareas de limpieza pendientes para el turno: {$turno->nombre}.",
            type: TipoNotificacion::CleaningNewAssignments,
            actions: [
                Action::make('view')
                    ->label('Ver Asignaciones')
                    ->url($this->url->solicitudEjecuciones())
                    ->button(),
            ],
        );
    }
}
