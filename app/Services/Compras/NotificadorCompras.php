<?php

namespace App\Services\Compras;

use App\Filament\Resources\Compras\Cotizaciones\CotizacionResource;
use App\Filament\Resources\Compras\DevolucionCompra\DevolucionCompraResource;
use App\Filament\Resources\Compras\OrdenesCompra\OrdenCompraResource;
use App\Filament\Resources\Compras\Recepciones\RecepcionResource;
use App\Filament\Resources\Compras\Solicitudes\SolicitudResource;
use App\Models\Compras\Cotizacion;
use App\Models\Compras\DevolucionCompra;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\Solicitud;
use App\Models\User;
use App\Services\Shared\NotificadorBase;
use Illuminate\Support\Collection;

class NotificadorCompras extends NotificadorBase
{
    private function obtenerUsuarioCreadorSolicitud(Solicitud $solicitud): ?User
    {
        $colaborador = $solicitud->relationLoaded('colaborador')
            ? $solicitud->colaborador
            : $solicitud->colaborador()->first();

        if (! $colaborador) {
            return null;
        }

        return User::where('persona_id', $colaborador->persona_id)->first();
    }

    /** @return Collection<int, User> */
    private function obtenerUsuariosCompras(): Collection
    {
        try {
            // 1. Obtener usuarios con permiso explícito
            $users = User::permission('ViewAny:Solicitud')->get();

            // 2. También incluir a los super administradores
            $superAdminRole = config('filament-shield.super_admin.name', 'super_admin');
            try {
                $superAdmins = User::role($superAdminRole)->get();
                $users = $users->merge($superAdmins);
            } catch (\Throwable $e) {
                // Silenciar si el rol o tablas de permisos no están listas
            }

            // 3. Fallback: Si no hay ningún usuario con permiso ni super admin,
            // usar el usuario autenticado actual o los primeros usuarios del sistema para asegurar la visibilidad
            if ($users->isEmpty()) {
                if (auth()->check()) {
                    $users->push(auth()->user());
                } else {
                    $users = User::limit(5)->get();
                }
            }

            return $users->unique('id');
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function notificarCreadorYCompras(?User $creator, string $title, ?string $body = null, string $icon = 'heroicon-o-information', ?string $url = null): void
    {
        $users = $this->obtenerUsuariosCompras();
        if ($creator !== null) {
            $users = $users->merge([$creator]);
        }
        if (auth()->check()) {
            $users = $users->merge([auth()->user()]);
        }
        $this->notificarMultiples($users->unique('id'), $title, $body, $icon, $url);
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
        $this->notificarCreadorYCompras(
            $user,
            'Solicitud Aprobada',
            "La solicitud {$solicitud->codigo} ha sido aprobada y está lista para cotizar.",
            'heroicon-o-check-circle',
            SolicitudResource::getUrl('view', ['record' => $solicitud]),
        );
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

        $this->notificarCreadorYCompras(
            $creator,
            'Recepción Registrada',
            "Se ha registrado una recepción {$recepcion->codigo} en estado 'Pendiente' para la orden {$recepcion->ordenCompra?->codigo}. El almacén debe confirmar la recepción.",
            'heroicon-o-clock',
            RecepcionResource::getUrl('view', ['record' => $recepcion]),
        );
    }

    public function recepcionCompletada(RecepcionCompra $recepcion): void
    {
        $creator = $recepcion->ordenCompra?->solicitud
            ? $this->obtenerUsuarioCreadorSolicitud($recepcion->ordenCompra->solicitud)
            : null;

        $this->notificarCreadorYCompras(
            $creator,
            'Recepción Completada',
            "La recepción {$recepcion->codigo} ha sido completada exitosamente. La orden {$recepcion->ordenCompra?->codigo} ha sido marcada como Recibida.",
            'heroicon-o-check-badge',
            RecepcionResource::getUrl('view', ['record' => $recepcion]),
        );
    }

    public function recepcionRechazada(RecepcionCompra $recepcion): void
    {
        $creator = $recepcion->ordenCompra?->solicitud
            ? $this->obtenerUsuarioCreadorSolicitud($recepcion->ordenCompra->solicitud)
            : null;

        $this->notificarCreadorYCompras(
            $creator,
            'Recepción Rechazada',
            "La recepción {$recepcion->codigo} ha sido rechazada. La orden {$recepcion->ordenCompra?->codigo} vuelve a estado Emitida. Se requiere gestionar el incidente con el proveedor.",
            'heroicon-o-exclamation-triangle',
            RecepcionResource::getUrl('view', ['record' => $recepcion]),
        );
    }

    public function recepcionConDiscrepancia(RecepcionCompra $recepcion): void
    {
        $creator = $recepcion->ordenCompra?->solicitud
            ? $this->obtenerUsuarioCreadorSolicitud($recepcion->ordenCompra->solicitud)
            : null;

        $this->notificarCreadorYCompras(
            $creator,
            'Recepción con Discrepancia',
            "La recepción {$recepcion->codigo} presenta discrepancias. Se requiere investigación de calidad/compras antes de aceptar la orden {$recepcion->ordenCompra?->codigo}.",
            'heroicon-o-shield-exclamation',
            RecepcionResource::getUrl('view', ['record' => $recepcion]),
        );
    }

    public function devolucionCreada(DevolucionCompra $devolucion): void
    {
        $creator = $devolucion->ordenCompra?->solicitud
            ? $this->obtenerUsuarioCreadorSolicitud($devolucion->ordenCompra->solicitud)
            : null;

        $this->notificarCreadorYCompras(
            $creator,
            'Devolución Registrada',
            "Se ha registrado la devolución {$devolucion->codigo} en estado 'Borrador' vinculada a la orden {$devolucion->ordenCompra?->codigo}.",
            'heroicon-o-document-text',
            DevolucionCompraResource::getUrl('view', ['record' => $devolucion]),
        );
    }

    public function devolucionConfirmada(DevolucionCompra $devolucion): void
    {
        $creator = $devolucion->ordenCompra?->solicitud
            ? $this->obtenerUsuarioCreadorSolicitud($devolucion->ordenCompra->solicitud)
            : null;

        $this->notificarCreadorYCompras(
            $creator,
            'Devolución Confirmada',
            "La devolución {$devolucion->codigo} vinculada a la orden {$devolucion->ordenCompra?->codigo} ha sido confirmada. Se ha retirado el stock físico y liberado el saldo del contrato.",
            'heroicon-o-check-circle',
            DevolucionCompraResource::getUrl('view', ['record' => $devolucion]),
        );
    }
}
