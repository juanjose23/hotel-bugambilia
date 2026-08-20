<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaHorarioResource\Pages;

use App\Filament\Resources\Limpieza\LimpiezaHorarioResource\LimpiezaHorarioResource;
use App\Interactors\Limpieza\Procesos\MaterializarEjecuciones;
use App\Repository\Models\Limpieza\LimpiezaHorario;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewLimpiezaHorario extends ViewRecord
{
    protected static string $resource = LimpiezaHorarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('materializarHorario')
                ->label('Generar ejecuciones')
                ->icon('heroicon-o-play')
                ->color('success')
                ->modalHeading('Generar ejecuciones de limpieza')
                ->modalDescription('Crea las ejecuciones pendientes de este horario planificado para la fecha seleccionada.')
                ->schema([
                    DatePicker::make('fecha')
                        ->label('Fecha de ejecución')
                        ->default(now())
                        ->required()
                        ->native(false),
                ])
                ->action(function (array $data, MaterializarEjecuciones $materializarEjecuciones): void {
                    if (! $this->record instanceof LimpiezaHorario) {
                        return;
                    }

                    $fecha = is_string($data['fecha'] ?? null) ? $data['fecha'] : now()->toDateString();
                    $recordKey = $this->record->getKey();

                    if (! is_numeric($recordKey)) {
                        return;
                    }

                    $resultado = $materializarEjecuciones->ejecutarHorario((int) $recordKey, $fecha);

                    Notification::make()
                        ->title('Ejecuciones procesadas')
                        ->body("Fecha {$resultado['fecha']}: se crearon {$resultado['creados']} ejecuciones.")
                        ->success()
                        ->send();
                }),
            EditAction::make(),
        ];
    }
}
