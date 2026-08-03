<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\SolicitudLimpiezaResource\Pages;

use App\Filament\Resources\Limpieza\SolicitudLimpiezaResource\SolicitudLimpiezaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListSolicitudesLimpieza extends ListRecords
{
    protected static string $resource = SolicitudLimpiezaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nueva Solicitud'),
        ];
    }
}
