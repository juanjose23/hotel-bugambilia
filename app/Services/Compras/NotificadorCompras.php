<?php

namespace App\Services\Compras;

use App\Filament\Resources\Compras\Cotizaciones\CotizacionResource;
use App\Filament\Resources\Compras\OrdenesCompra\OrdenCompraResource;
use App\Filament\Resources\Compras\Recepciones\RecepcionResource;
use App\Filament\Resources\Compras\Solicitudes\SolicitudResource;
use App\Models\Compras\Cotizacion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\Solicitud;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class NotificadorCompras
{
    private function obtenerUsuarioCreadorSolicitud(Solicitud $solicitud): ?User
    {
        return User::where('persona_id', $solicitud->colaborador->persona_id)->first();
    }

    /** @return Collection<int, User> */
    private function obtenerUsuariosCompras(): Collection
    {
        return User::permission('ViewAny:Solicitud')->get();
    }

    private function enviar(User $user, string $title, ?string $body = null, string $icon = 'heroicon-o-information', ?string $url = null): void
    {
        $notification = Notification::make()
            ->title($title)
            ->icon($icon)
            ->body($body ?? '');

        if ($url !== null) {
            $notification->actions([
                Action::make('view')
                    ->label('Ver')
                    ->url($url)
                    ->markAsRead(),
            ]);
        }

        $notification->sendToDatabase($user);
    }

    /** @param Collection<int, User> $users */
    private function notificarMultiples(Collection $users, string $title, ?string $body = null, string $icon = 'heroicon-o-information', ?string $url = null): void
    {
        foreach ($users as $user) {
            $this->enviar($user, $title, $body, $icon, $url);
        }
    }

    private function notificarCreadorYCompras(?User $creator, string $title, ?string $body = null, string $icon = 'heroicon-o-information', ?string $url = null): void
    {
        $users = $this->obtenerUsuariosCompras();
        if ($creator !== null) {
            $users = $users->merge([$creator])->unique('id');
        }
        $this->notificarMultiples($users, $title, $body, $icon, $url);
    }

    public function solicitudCreada(Solicitud $solicitud): void
    {
        $user = $this->obtenerUsuarioCreadorSolicitud($solicitud);
        if ($user) {
            $this->enviar(
                $user,
                'Solicitud Creada',
                "La solicitud {$solicitud->codigo} ha sido creada exitosamente.",
                'heroicon-o-document-text',
                SolicitudResource::getUrl('view', ['record' => $solicitud]),
            );
        }
    }

    public function solicitudAprobada(Solicitud $solicitud): void
    {
        $user = $this->obtenerUsuarioCreadorSolicitud($solicitud);
        if ($user) {
            $this->enviar(
                $user,
                'Solicitud Aprobada',
                "La solicitud {$solicitud->codigo} ha sido aprobada y está lista para cotizar.",
                'heroicon-o-check-circle',
                SolicitudResource::getUrl('view', ['record' => $solicitud]),
            );
        }
    }

    public function solicitudRechazada(Solicitud $solicitud): void
    {
        $user = $this->obtenerUsuarioCreadorSolicitud($solicitud);
        if ($user) {
            $this->enviar(
                $user,
                'Solicitud Rechazada',
                "La solicitud {$solicitud->codigo} ha sido rechazada.",
                'heroicon-o-x-circle',
                SolicitudResource::getUrl('view', ['record' => $solicitud]),
            );
        }
    }

    public function solicitudCancelada(Solicitud $solicitud): void
    {
        $creator = $this->obtenerUsuarioCreadorSolicitud($solicitud);
        $this->notificarCreadorYCompras(
            $creator,
            'Solicitud Cancelada',
            "La solicitud {$solicitud->codigo} ha sido cancelada.",
            'heroicon-o-x-circle',
            SolicitudResource::getUrl('view', ['record' => $solicitud]),
        );
    }

    public function cotizacionCreada(Cotizacion $cotizacion): void
    {
        $creator = $cotizacion->solicitud ? $this->obtenerUsuarioCreadorSolicitud($cotizacion->solicitud) : null;
        $this->notificarCreadorYCompras(
            $creator,
            'Nueva Cotización',
            "Se ha registrado una cotización de {$cotizacion->proveedor?->codigo} para la solicitud {$cotizacion->solicitud?->codigo}.",
            'heroicon-o-document-currency-dollar',
            CotizacionResource::getUrl('view', ['record' => $cotizacion]),
        );
    }

    public function ganadorSeleccionado(Cotizacion $cotizacion): void
    {
        $creator = $cotizacion->solicitud ? $this->obtenerUsuarioCreadorSolicitud($cotizacion->solicitud) : null;
        $this->notificarCreadorYCompras(
            $creator,
            'Ganador Seleccionado',
            "El proveedor {$cotizacion->proveedor?->codigo} ha sido seleccionado como ganador de la solicitud {$cotizacion->solicitud?->codigo}.",
            'heroicon-o-trophy',
            CotizacionResource::getUrl('view', ['record' => $cotizacion]),
        );
    }

    public function ordenCreada(OrdenCompra $orden): void
    {
        $creator = $orden->solicitud ? $this->obtenerUsuarioCreadorSolicitud($orden->solicitud) : null;
        $this->notificarCreadorYCompras(
            $creator,
            'Orden de Compra Creada',
            "Se ha creado la orden {$orden->codigo} por un total de $".number_format((float) $orden->total, 2).'.',
            'heroicon-o-shopping-cart',
            OrdenCompraResource::getUrl('view', ['record' => $orden]),
        );
    }

    public function ordenEmitida(OrdenCompra $orden): void
    {
        $creator = $orden->solicitud ? $this->obtenerUsuarioCreadorSolicitud($orden->solicitud) : null;
        $this->notificarCreadorYCompras(
            $creator,
            'Orden de Compra Emitida',
            "La orden {$orden->codigo} ha sido emitida oficialmente al proveedor {$orden->proveedor?->codigo}.",
            'heroicon-o-paper-airplane',
            OrdenCompraResource::getUrl('view', ['record' => $orden]),
        );
    }

    public function ordenCancelada(OrdenCompra $orden): void
    {
        $creator = $orden->solicitud ? $this->obtenerUsuarioCreadorSolicitud($orden->solicitud) : null;
        $this->notificarCreadorYCompras(
            $creator,
            'Orden de Compra Cancelada',
            "La orden {$orden->codigo} ha sido cancelada.",
            'heroicon-o-x-circle',
            OrdenCompraResource::getUrl('view', ['record' => $orden]),
        );
    }

    public function recepcionCreada(RecepcionCompra $recepcion): void
    {
        $creator = $recepcion->ordenCompra?->solicitud
            ? $this->obtenerUsuarioCreadorSolicitud($recepcion->ordenCompra->solicitud)
            : null;

        $estadoLabel = $recepcion->estado->label();
        $icon = $recepcion->estado->value === 4
            ? 'heroicon-o-exclamation-triangle'
            : 'heroicon-o-archive-box';

        $this->notificarCreadorYCompras(
            $creator,
            'Recepción Registrada',
            "Se ha registrado una recepción {$recepcion->codigo} con estado '{$estadoLabel}' para la orden {$recepcion->ordenCompra?->codigo}.",
            $icon,
            RecepcionResource::getUrl('view', ['record' => $recepcion]),
        );
    }
}
