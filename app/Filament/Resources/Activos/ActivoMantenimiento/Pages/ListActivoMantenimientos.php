<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\ActivoMantenimiento\Pages;

use App\Filament\Pages\Activos\CalendarioMantenimientos;
use App\Filament\Resources\Activos\ActivoMantenimiento\ActivoMantenimientoResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListActivoMantenimientos extends ListRecords
{
    protected static string $resource = ActivoMantenimientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('ver_calendario')
                ->label('Ver Calendario')
                ->icon('heroicon-m-calendar-days')
                ->color('gray')
                ->url(fn (): string => CalendarioMantenimientos::getUrl()),
            CreateAction::make()->label('Nueva Orden de Mantenimiento'),
        ];
    }
}
