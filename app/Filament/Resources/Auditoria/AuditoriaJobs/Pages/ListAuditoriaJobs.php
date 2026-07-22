<?php

declare(strict_types=1);

namespace App\Filament\Resources\Auditoria\AuditoriaJobs\Pages;

use App\Enums\Shared\EstadoEjecucionJob;
use App\Enums\Shared\TipoJob;
use App\Filament\Resources\Auditoria\AuditoriaJobs\AuditoriaJobResource;
use App\Interactors\Auditoria\EjecutarJobManual;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListAuditoriaJobs extends ListRecords
{
    protected static string $resource = AuditoriaJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('ejecutarJob')
                ->label('Ejecutar Job')
                ->icon('heroicon-o-play')
                ->color('success')
                ->schema([
                    Select::make('tipo_job')
                        ->label('Seleccionar Job')
                        ->options(TipoJob::class)
                        ->required(),
                ])
                ->modalHeading('Ejecutar Job Manualmente')
                ->modalDescription('Seleccione el job que desea ejecutar. Se registrará en la auditoría.')
                ->modalSubmitActionLabel('Ejecutar')
                ->action(function (array $data): void {
                    $tipoJob = $data['tipo_job'] instanceof TipoJob
                        ? $data['tipo_job']
                        : TipoJob::from($data['tipo_job']);

                    $interactor = app(EjecutarJobManual::class);
                    $resultado = $interactor->ejecutar($tipoJob);

                    if ($resultado->estado === EstadoEjecucionJob::Completado) {
                        Notification::make()
                            ->title('Job ejecutado exitosamente')
                            ->body($resultado->nombre_job)
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Error al ejecutar el job')
                            ->body($resultado->mensaje)
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
