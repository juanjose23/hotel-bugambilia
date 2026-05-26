<?php

declare(strict_types=1);

namespace App\Filament\Resources\Espacios\Espacio\Pages;

use App\Filament\Resources\Espacios\Espacio\EspacioResource;
use App\Models\Espacios\Espacio;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditEspacio extends EditRecord
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
            DeleteAction::make(),
        ];
    }
}
