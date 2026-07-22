<?php

declare(strict_types=1);

namespace App\Notifications\Compras;

use App\Enums\Notifications\TipoNotificacion;
use App\Notifications\DatosNotificacion;
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\DevolucionCompra;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Models\Compras\Solicitud;
use Filament\Actions\Action;

final class MensajesCompra
{
    public function __construct(
        private readonly UrlNotificador $url,
    ) {}

    public function solicitudCreada(Solicitud $solicitud): DatosNotificacion
    {
        $nombreCreador = $solicitud->colaborador?->persona->nombre_completo ?? 'un colaborador';

        return new DatosNotificacion(
            'Solicitud Creada',
            "Se ha registrado la solicitud {$solicitud->codigo} por {$nombreCreador}.",
            TipoNotificacion::Info,
            [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->solicitud($solicitud))
                    ->button(),
            ],
        );
    }

    public function solicitudAprobada(Solicitud $solicitud): DatosNotificacion
    {
        return new DatosNotificacion(
            'Solicitud Aprobada',
            "La solicitud {$solicitud->codigo} ha sido aprobada.",
            TipoNotificacion::Success,
            [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->solicitud($solicitud))
                    ->button(),
            ],
        );
    }

    public function solicitudRechazada(Solicitud $solicitud): DatosNotificacion
    {
        return new DatosNotificacion(
            'Solicitud Rechazada',
            "La solicitud {$solicitud->codigo} ha sido rechazada.",
            TipoNotificacion::Error,
            [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->solicitud($solicitud))
                    ->button(),
            ],
        );
    }

    public function solicitudCancelada(Solicitud $solicitud): DatosNotificacion
    {
        return new DatosNotificacion(
            'Solicitud Cancelada',
            "La solicitud {$solicitud->codigo} ha sido cancelada.",
            TipoNotificacion::Error,
            [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->solicitud($solicitud))
                    ->button(),
            ],
        );
    }

    public function cotizacionCreada(Cotizacion $cotizacion): DatosNotificacion
    {
        $nombreProveedor = $cotizacion->proveedor?->persona->nombre_completo ?? 'proveedor';
        $codigoSolicitud = $cotizacion->solicitud->codigo ?? '';

        return new DatosNotificacion(
            'Nueva Cotización',
            "Se ha registrado una cotización de {$nombreProveedor} para la solicitud {$codigoSolicitud}.",
            TipoNotificacion::Info,
            [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->cotizacion($cotizacion))
                    ->button(),
            ],
        );
    }

    public function ganadorSeleccionado(Cotizacion $cotizacion): DatosNotificacion
    {
        $nombreProveedor = $cotizacion->proveedor?->persona->nombre_completo ?? 'proveedor';
        $codigoSolicitud = $cotizacion->solicitud->codigo ?? '';

        return new DatosNotificacion(
            'Ganador Seleccionado',
            "Se ha elegido la cotización de {$nombreProveedor} como ganadora para la solicitud {$codigoSolicitud}.",
            TipoNotificacion::Success,
            [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->cotizacion($cotizacion))
                    ->button(),
            ],
        );
    }

    public function ordenCreada(OrdenCompra $orden): DatosNotificacion
    {
        $nombreProveedor = $orden->proveedor?->persona->nombre_completo ?? 'proveedor';

        return new DatosNotificacion(
            'Orden de Compra Creada',
            "Se ha creado la Orden de Compra {$orden->codigo} en borrador para el proveedor {$nombreProveedor}.",
            TipoNotificacion::PurchaseOrderCreated,
            [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->ordenCompra($orden))
                    ->button(),
            ],
        );
    }

    public function ordenEmitida(OrdenCompra $orden): DatosNotificacion
    {
        $nombreProveedor = $orden->proveedor?->persona->nombre_completo ?? 'proveedor';

        return new DatosNotificacion(
            'Orden de Compra Emitida',
            "La Orden de Compra {$orden->codigo} ha sido emitida al proveedor {$nombreProveedor}.",
            TipoNotificacion::PurchaseOrderApproved,
            [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->ordenCompra($orden))
                    ->button(),
            ],
        );
    }

    public function ordenCancelada(OrdenCompra $orden): DatosNotificacion
    {
        return new DatosNotificacion(
            'Orden de Compra Cancelada',
            "La Orden de Compra {$orden->codigo} ha sido cancelada.",
            TipoNotificacion::Error,
            [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->ordenCompra($orden))
                    ->button(),
            ],
        );
    }

    public function recepcionCreada(RecepcionCompra $recepcion): DatosNotificacion
    {
        $codigoOC = $recepcion->ordenCompra->codigo ?? '';

        return new DatosNotificacion(
            'Recepción Registrada',
            "Se ha registrado la recepción {$recepcion->codigo} (pendiente) para la OC {$codigoOC}.",
            TipoNotificacion::Info,
            [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->recepcion($recepcion))
                    ->button(),
            ],
        );
    }

    public function recepcionCompletada(RecepcionCompra $recepcion): DatosNotificacion
    {
        $codigoOC = $recepcion->ordenCompra->codigo ?? '';

        return new DatosNotificacion(
            'Recepción Completada',
            "La recepción {$recepcion->codigo} ha sido completada para la OC {$codigoOC}.",
            TipoNotificacion::PurchaseOrderReceived,
            [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->recepcion($recepcion))
                    ->button(),
            ],
        );
    }

    public function recepcionRechazada(RecepcionCompra $recepcion): DatosNotificacion
    {
        $codigoOC = $recepcion->ordenCompra->codigo ?? '';

        return new DatosNotificacion(
            'Recepción Rechazada',
            "La recepción {$recepcion->codigo} ha sido rechazada para la OC {$codigoOC}.",
            TipoNotificacion::Error,
            [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->recepcion($recepcion))
                    ->button(),
            ],
        );
    }

    public function recepcionConDiscrepancia(RecepcionCompra $recepcion): DatosNotificacion
    {
        $codigoOC = $recepcion->ordenCompra->codigo ?? '';

        return new DatosNotificacion(
            'Recepción con Discrepancia',
            "Se detectó discrepancia en la recepción {$recepcion->codigo} para la OC {$codigoOC}.",
            TipoNotificacion::Warning,
            [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->recepcion($recepcion))
                    ->button(),
            ],
        );
    }

    public function devolucionCreada(DevolucionCompra $devolucion): DatosNotificacion
    {
        $codigoRecepcion = $devolucion->recepcionCompra->codigo ?? '';

        return new DatosNotificacion(
            'Devolución Registrada',
            "Se ha registrado la devolución {$devolucion->codigo} (borrador) para la recepción {$codigoRecepcion}.",
            TipoNotificacion::Info,
            [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->devolucion($devolucion))
                    ->button(),
            ],
        );
    }

    public function devolucionConfirmada(DevolucionCompra $devolucion): DatosNotificacion
    {
        $nombreProveedor = $devolucion->ordenCompra?->proveedor?->persona->nombre_completo ?? 'proveedor';

        return new DatosNotificacion(
            'Devolución Confirmada',
            "La devolución {$devolucion->codigo} ha sido confirmada para el proveedor {$nombreProveedor}.",
            TipoNotificacion::Success,
            [
                Action::make('view')
                    ->label('Ver')
                    ->url($this->url->devolucion($devolucion))
                    ->button(),
            ],
        );
    }
}
