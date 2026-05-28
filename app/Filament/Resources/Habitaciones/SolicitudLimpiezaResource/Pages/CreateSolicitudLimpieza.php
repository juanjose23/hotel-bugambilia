<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\SolicitudLimpiezaResource\Pages;

use App\Filament\Resources\Habitaciones\SolicitudLimpiezaResource\SolicitudLimpiezaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSolicitudLimpieza extends CreateRecord
{
    protected static string $resource = SolicitudLimpiezaResource::class;

    /**
     * Retorna la URL de redirección tras la creación.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
