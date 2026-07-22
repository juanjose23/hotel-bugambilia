<?php

declare(strict_types=1);

namespace App\Notifications\Compras;

use App\Filament\Resources\Compras\Cotizaciones\CotizacionResource;
use App\Filament\Resources\Compras\DevolucionCompra\DevolucionCompraResource;
use App\Filament\Resources\Compras\OrdenesCompra\OrdenCompraResource;
use App\Filament\Resources\Compras\Recepciones\RecepcionResource;
use App\Filament\Resources\Compras\Solicitudes\SolicitudResource;
use App\Notifications\Compras\Contracts\UrlNotificadorInterface;
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\DevolucionCompra;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Models\Compras\Solicitud;

final class UrlNotificador implements UrlNotificadorInterface
{
    public function solicitud(Solicitud $solicitud): string
    {
        return SolicitudResource::getUrl('view', ['record' => $solicitud->id]);
    }

    public function cotizacion(Cotizacion $cotizacion): string
    {
        return CotizacionResource::getUrl('view', ['record' => $cotizacion->id]);
    }

    public function ordenCompra(OrdenCompra $orden): string
    {
        return OrdenCompraResource::getUrl('view', ['record' => $orden->id]);
    }

    public function recepcion(RecepcionCompra $recepcion): string
    {
        return RecepcionResource::getUrl('view', ['record' => $recepcion->id]);
    }

    public function devolucion(DevolucionCompra $devolucion): string
    {
        return DevolucionCompraResource::getUrl('view', ['record' => $devolucion->id]);
    }
}
