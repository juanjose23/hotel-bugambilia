<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\HabitacionResource\Pages;

use App\Filament\Resources\Habitaciones\HabitacionResource\HabitacionResource;
use App\Models\Habitaciones\Habitacion;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewHabitacion extends ViewRecord
{
    protected static string $resource = HabitacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('imprimir_hoja')
                ->label('Imprimir Hoja de Habitación')
                ->icon(Heroicon::Printer)
                ->color('info')
                ->url(fn (Habitacion $record) => route('reporte.activos.hoja-habitacion.pdf', ['tipo' => 'habitacion', 'id' => $record->id]))
                ->openUrlInNewTab()
                ->visible(fn () => auth()->user()->can('Activos:ReporteHojaHabitacion')),
            EditAction::make(),
        ];
    }
}
