<?php

declare(strict_types=1);

namespace App\Notifications\Compras;

use App\Notifications\NotificadorBase;
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\DevolucionCompra;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Models\Compras\Solicitud;

final class NotificadorCompras extends NotificadorBase
{
    public function __construct(
        private readonly DestinatariosCompra $destinatarios,
        private readonly MensajesCompra $mensajes,
    ) {}

    public function solicitudCreada(Solicitud $solicitud): void
    {
        $usuarios = $this->destinatarios->obtener(
            $solicitud->colaborador?->persona?->user,
        );

        $this->enviar($usuarios, $this->mensajes->solicitudCreada($solicitud));
    }

    public function solicitudAprobada(Solicitud $solicitud): void
    {
        $usuarios = $this->destinatarios->obtener(
            $solicitud->colaborador?->persona?->user,
        );

        $this->enviar($usuarios, $this->mensajes->solicitudAprobada($solicitud));
    }

    public function solicitudRechazada(Solicitud $solicitud): void
    {
        $usuarios = $this->destinatarios->obtener(
            $solicitud->colaborador?->persona?->user,
        );

        $this->enviar($usuarios, $this->mensajes->solicitudRechazada($solicitud));
    }

    public function solicitudCancelada(Solicitud $solicitud): void
    {
        $usuarios = $this->destinatarios->obtener(
            $solicitud->colaborador?->persona?->user,
        );

        $this->enviar($usuarios, $this->mensajes->solicitudCancelada($solicitud));
    }

    public function abastecimientoCocinaCreado(Solicitud $solicitud): void
    {
        $usuarios = $this->destinatarios->obtenerComprasEInventario(
            $solicitud->colaborador?->persona?->user,
        );

        $this->enviar($usuarios, $this->mensajes->abastecimientoCocinaCreado($solicitud));
    }

    /**
     * @param  list<string>  $traslados
     */
    public function abastecimientoCocinaResueltoConInventario(Solicitud $solicitud, array $traslados = []): void
    {
        $usuarios = $this->destinatarios->obtenerComprasEInventario(
            $solicitud->colaborador?->persona?->user,
        );

        $this->enviar($usuarios, $this->mensajes->abastecimientoCocinaResueltoConInventario($solicitud, $traslados));
    }

    /**
     * @param  list<string>  $faltantes
     */
    public function abastecimientoCocinaRequiereCompra(Solicitud $solicitud, array $faltantes): void
    {
        $usuarios = $this->destinatarios->obtenerComprasEInventario(
            $solicitud->colaborador?->persona?->user,
        );

        $this->enviar($usuarios, $this->mensajes->abastecimientoCocinaRequiereCompra($solicitud, $faltantes));
    }

    public function cotizacionCreada(Cotizacion $cotizacion): void
    {
        $usuarios = $this->destinatarios->obtener(
            $cotizacion->creadaPor,
        );

        $this->enviar($usuarios, $this->mensajes->cotizacionCreada($cotizacion));
    }

    public function ganadorSeleccionado(Cotizacion $cotizacion): void
    {
        $usuarios = $this->destinatarios->obtener(
            $cotizacion->creadaPor,
        );

        $this->enviar($usuarios, $this->mensajes->ganadorSeleccionado($cotizacion));
    }

    public function ordenCreada(OrdenCompra $orden): void
    {
        $usuarios = $this->destinatarios->obtener(
            $orden->solicitud?->colaborador?->persona?->user,
        );

        $this->enviar($usuarios, $this->mensajes->ordenCreada($orden));
    }

    public function ordenEmitida(OrdenCompra $orden): void
    {
        $usuarios = $this->destinatarios->obtener(
            $orden->solicitud?->colaborador?->persona?->user,
        );

        $this->enviar($usuarios, $this->mensajes->ordenEmitida($orden));
    }

    public function ordenCancelada(OrdenCompra $orden): void
    {
        $usuarios = $this->destinatarios->obtener(
            $orden->solicitud?->colaborador?->persona?->user,
        );

        $this->enviar($usuarios, $this->mensajes->ordenCancelada($orden));
    }

    public function recepcionCreada(RecepcionCompra $recepcion): void
    {
        $usuarios = $this->destinatarios->obtener(
            $recepcion->receptor,
        );

        $this->enviar($usuarios, $this->mensajes->recepcionCreada($recepcion));
    }

    public function recepcionCompletada(RecepcionCompra $recepcion): void
    {
        $usuarios = $this->destinatarios->obtener(
            $recepcion->receptor,
        );

        $this->enviar($usuarios, $this->mensajes->recepcionCompletada($recepcion));
    }

    public function recepcionRechazada(RecepcionCompra $recepcion): void
    {
        $usuarios = $this->destinatarios->obtener(
            $recepcion->receptor,
        );

        $this->enviar($usuarios, $this->mensajes->recepcionRechazada($recepcion));
    }

    public function recepcionConDiscrepancia(RecepcionCompra $recepcion): void
    {
        $usuarios = $this->destinatarios->obtener(
            $recepcion->receptor,
        );

        $this->enviar($usuarios, $this->mensajes->recepcionConDiscrepancia($recepcion));
    }

    public function devolucionCreada(DevolucionCompra $devolucion): void
    {
        $usuarios = $this->destinatarios->obtener(
            $devolucion->creador,
        );

        $this->enviar($usuarios, $this->mensajes->devolucionCreada($devolucion));
    }

    public function devolucionConfirmada(DevolucionCompra $devolucion): void
    {
        $usuarios = $this->destinatarios->obtener(
            $devolucion->creador,
        );

        $this->enviar($usuarios, $this->mensajes->devolucionConfirmada($devolucion));
    }
}
