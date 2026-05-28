<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\EspacioResource\Pages;

use App\Filament\Resources\Habitaciones\EspacioResource\EspacioResource;
use App\Models\Espacios\Espacio;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewEspacio extends ViewRecord
{
    protected static string $resource = EspacioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('imprimir_hoja')
                ->label('Imprimir Hoja de Espacio')
                ->icon(Heroicon::Printer)
                ->color('info')
                ->url(fn (Espacio $record) => route('reporte.activos.hoja-habitacion.pdf', ['tipo' => 'espacio', 'id' => $record->id]))
                ->openUrlInNewTab()
                ->visible(fn () => auth()->user()->can('Activos:ReporteHojaHabitacion')),
            EditAction::make(),
        ];
    }
}
