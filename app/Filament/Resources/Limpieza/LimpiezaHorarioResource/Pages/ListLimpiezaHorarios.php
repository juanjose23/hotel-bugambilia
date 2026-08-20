<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaHorarioResource\Pages;

use App\Filament\Resources\Limpieza\LimpiezaHorarioResource\LimpiezaHorarioResource;
use App\Interactors\Limpieza\Procesos\MaterializarEjecuciones;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListLimpiezaHorarios extends ListRecords
{
    protected static string $resource = LimpiezaHorarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('materializarEjecuciones')
                ->label('Generar ejecuciones')
                ->icon('heroicon-o-play')
                ->color('success')
                ->modalHeading('Generar ejecuciones de limpieza')
                ->modalDescription('Crea las ejecuciones pendientes de todos los horarios activos para la fecha seleccionada.')
                ->schema([
                    DatePicker::make('fecha')
                        ->label('Fecha de ejecución')
                        ->default(now())
                        ->required()
                        ->native(false),
                ])
                ->action(function (array $data, MaterializarEjecuciones $materializarEjecuciones): void {
                    $fecha = is_string($data['fecha'] ?? null) ? $data['fecha'] : now()->toDateString();
                    $resultado = $materializarEjecuciones->ejecutar($fecha);

                    Notification::make()
                        ->title('Ejecuciones procesadas')
                        ->body("Fecha {$resultado['fecha']}: se crearon {$resultado['creados']} ejecuciones.")
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
