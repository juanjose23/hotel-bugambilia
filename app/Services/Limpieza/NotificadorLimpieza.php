<?php

declare(strict_types=1);

namespace App\Services\Limpieza;

use App\Filament\Resources\Habitaciones\SolicitudLimpiezaResource\SolicitudLimpiezaResource;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Limpieza\SolicitudLimpieza;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class NotificadorLimpieza
{
    /**
     * Obtiene los usuarios con permiso de gestión de limpieza o administradores.
     *
     * @return Collection<int, User>
     */
    public function obtenerUsuariosLimpieza(): Collection
    {
        $users = collect();
        try {
            // 1. Obtener usuarios con permiso explícito de actualizar solicitudes de limpieza
            $users = User::permission('Update:SolicitudLimpieza')->get();
        } catch (\Throwable $e) {
            // Silenciar si el permiso no existe en la BD
        }

        try {
            // 2. Incluir super administradores
            $superAdminRole = config('filament-shield.super_admin.name', 'super_admin');
            $superAdmins = User::role($superAdminRole)->get();
            $users = $users->merge($superAdmins);
        } catch (\Throwable $e) {
            // Silenciar si no hay permisos o roles sembrados
        }

        // 3. Fallback en caso de estar vacío
        if ($users->isEmpty()) {
            if (auth()->check()) {
                $users->push(auth()->user());
            } else {
                $users = User::limit(5)->get();
            }
        }

        return $users->unique('id');
    }

    /**
     * Envía una notificación individual.
     */
    private function enviar(
        User $user,
        string $title,
        ?string $body = null,
        string $icon = 'heroicon-o-sparkles',
        ?string $url = null,
        string $status = 'info'
    ): void {
        $notification = Notification::make()
            ->title($title)
            ->icon($icon)
            ->body($body ?? '');

        match ($status) {
            'success' => $notification->success(),
            'warning' => $notification->warning(),
            'danger' => $notification->danger(),
            default => $notification->info(),
        };

        if ($url !== null) {
            $notification->actions([
                Action::make('view')
                    ->label('Ver detalle')
                    ->url($url)
                    ->markAsRead(),
            ]);
        }

        $notification->sendToDatabase($user);
    }

    /**
     * Notifica el registro de una nueva solicitud de limpieza.
     */
    public function nuevaSolicitudLimpieza(SolicitudLimpieza $solicitud): void
    {
        $users = $this->obtenerUsuariosLimpieza();

        $solicitud->load('limpiable');
        $limpiable = $solicitud->limpiable;
        $nombreUbicacion = 'Espacio';
        if ($limpiable instanceof Habitacion) {
            $nombreUbicacion = "Habitación {$limpiable->nombre} (#{$limpiable->numero})";
        } elseif ($limpiable instanceof Espacio) {
            $nombreUbicacion = "Espacio {$limpiable->nombre}";
        }

        // Si hay operario asignado al crearse, notificarle de forma específica y excluirlo de la general
        if ($solicitud->personal_id !== null) {
            $personal = User::find($solicitud->personal_id);
            if ($personal) {
                $this->personalAsignado($solicitud);

                // Excluir de la notificación general para evitar duplicados
                $users = $users->reject(fn (User $u) => $u->id === $personal->id);
            }
        }

        $body = "Se ha registrado una solicitud de limpieza para {$nombreUbicacion} con prioridad ".ucfirst($solicitud->prioridad).'.';

        $url = SolicitudLimpiezaResource::getUrl('view', ['record' => $solicitud]);

        foreach ($users->unique('id') as $user) {
            $this->enviar(
                $user,
                'Nueva Solicitud de Limpieza',
                $body,
                'heroicon-o-sparkles',
                $url,
                'info'
            );
        }
    }

    /**
     * Notifica a un operario asignado que tiene una nueva tarea.
     */
    public function personalAsignado(SolicitudLimpieza $solicitud): void
    {
        if ($solicitud->personal_id === null) {
            return;
        }

        $personal = User::find($solicitud->personal_id);
        if (! $personal) {
            return;
        }

        $solicitud->load('limpiable');
        $limpiable = $solicitud->limpiable;
        $nombreUbicacion = 'Espacio';
        if ($limpiable instanceof Habitacion) {
            $nombreUbicacion = "Habitación {$limpiable->nombre} (#{$limpiable->numero})";
        } elseif ($limpiable instanceof Espacio) {
            $nombreUbicacion = "Espacio {$limpiable->nombre}";
        }

        $body = "Se te ha asignado la limpieza de {$nombreUbicacion} con prioridad ".ucfirst($solicitud->prioridad).'.';

        $url = SolicitudLimpiezaResource::getUrl('view', ['record' => $solicitud]);

        $this->enviar(
            $personal,
            'Nueva Tarea de Limpieza Asignada',
            $body,
            'heroicon-o-clipboard-document-check',
            $url,
            'info'
        );
    }
}
