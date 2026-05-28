<?php

declare(strict_types=1);

namespace App\Services\Activos;

use App\Filament\Resources\Activos\Activo\ActivoResource;
use App\Filament\Resources\Activos\ActivoMantenimiento\ActivoMantenimientoResource;
use App\Models\Activos\Activo;
use App\Models\Activos\ActivoMantenimiento;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class NotificadorActivos
{
    /** @return Collection<int, User> */
    private function obtenerDestinatarios(): Collection
    {
        return User::all();
    }

    private function enviar(User $user, string $title, ?string $body = null, string $icon = 'heroicon-o-information-circle', ?string $url = null, string $status = 'info'): void
    {
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

    /** @param Collection<int, User> $users */
    private function notificarMultiples(Collection $users, string $title, ?string $body = null, string $icon = 'heroicon-o-information-circle', ?string $url = null, string $status = 'info'): void
    {
        foreach ($users as $user) {
            $this->enviar($user, $title, $body, $icon, $url, $status);
        }
    }

    /** @param Collection<int, User>|null $destinatarios */
    public function garantiaProxima(Activo $activo, int $dias, ?Collection $destinatarios = null): void
    {
        $this->notificarMultiples(
            $destinatarios ?? $this->obtenerDestinatarios(),
            'Garantía próxima a vencer',
            "El activo {$activo->codigo_inventario} ({$activo->producto?->nombre}) vencerá su garantía en {$dias} días.",
            'heroicon-o-shield-exclamation',
            ActivoResource::getUrl('view', ['record' => $activo]),
            'warning'
        );
    }

    /** @param Collection<int, User>|null $destinatarios */
    public function mantenimientoAtrasado(ActivoMantenimiento $mantenimiento, int $dias, ?Collection $destinatarios = null): void
    {
        $this->notificarMultiples(
            $destinatarios ?? $this->obtenerDestinatarios(),
            'Mantenimiento atrasado',
            "El activo {$mantenimiento->activo?->codigo_inventario} lleva {$dias} días con mantenimiento {$mantenimiento->estado->label()}.",
            'heroicon-o-clock',
            ActivoMantenimientoResource::getUrl('view', ['record' => $mantenimiento]),
            'warning'
        );
    }

    /** @param Collection<int, User>|null $destinatarios */
    public function mantenimientoProlongado(ActivoMantenimiento $mantenimiento, int $dias, ?Collection $destinatarios = null): void
    {
        $this->notificarMultiples(
            $destinatarios ?? $this->obtenerDestinatarios(),
            'Activo en mantenimiento prolongado',
            "El activo {$mantenimiento->activo?->codigo_inventario} lleva {$dias} días en mantenimiento en curso.",
            'heroicon-o-wrench-screwdriver',
            ActivoMantenimientoResource::getUrl('view', ['record' => $mantenimiento]),
            'danger'
        );
    }

    public function sinMantenimientoHistorico(Activo $activo): void
    {
        $this->notificarMultiples(
            $this->obtenerDestinatarios(),
            'Activo sin mantenimiento histórico',
            "El activo {$activo->codigo_inventario} ({$activo->producto?->nombre}) no registra mantenimientos previos.",
            'heroicon-o-exclamation-triangle',
            ActivoResource::getUrl('view', ['record' => $activo]),
            'warning'
        );
    }

    public function mantenimientoPreventivoProgramado(ActivoMantenimiento $mantenimiento): void
    {
        $this->notificarMultiples(
            $this->obtenerDestinatarios(),
            'Mantenimiento Preventivo Automático',
            "Se ha programado automáticamente un nuevo mantenimiento preventivo para el activo {$mantenimiento->activo?->codigo_inventario}.",
            'heroicon-o-calendar-days',
            ActivoMantenimientoResource::getUrl('view', ['record' => $mantenimiento]),
            'info'
        );
    }

    /** @param Collection<int, User>|null $destinatarios */
    public function mantenimientoProximo(ActivoMantenimiento $mantenimiento, int $dias, ?Collection $destinatarios = null): void
    {
        $this->notificarMultiples(
            $destinatarios ?? $this->obtenerDestinatarios(),
            'Mantenimiento próximo a vencer',
            "El activo {$mantenimiento->activo?->codigo_inventario} tiene un mantenimiento {$mantenimiento->estado->label()} programado para el {$mantenimiento->fecha_programada->format('d/m/Y')} (en {$dias} días).",
            'heroicon-o-bell-alert',
            ActivoMantenimientoResource::getUrl('view', ['record' => $mantenimiento]),
            'warning'
        );
    }
}
