<?php

declare(strict_types=1);

namespace App\Notifications\Limpieza;

use App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\LimpiezaEjecucionResource;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\SolicitudLimpieza;

final class UrlNotificador
{
    public function solicitud(SolicitudLimpieza $solicitud): string
    {
        return LimpiezaEjecucionResource::getUrl('index');
    }

    public function solicitudEjecuciones(): string
    {
        return LimpiezaEjecucionResource::getUrl('index');
    }

    public function ejecucion(LimpiezaEjecucion $ejecucion): string
    {
        return LimpiezaEjecucionResource::getUrl('view', ['record' => $ejecucion->id]);
    }
}
