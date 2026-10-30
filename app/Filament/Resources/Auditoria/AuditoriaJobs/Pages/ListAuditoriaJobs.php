<?php

declare(strict_types=1);

namespace App\Filament\Resources\Auditoria\AuditoriaJobs\Pages;

use App\Enums\Shared\EstadoEjecucionJob;
use App\Enums\Shared\TipoJob;
use App\Filament\Resources\Auditoria\AuditoriaJobs\AuditoriaJobResource;
use App\Interactors\Auditoria\EjecutarJobManual;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;

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
                        ->required()
                        ->live(),
                    Toggle::make('limpiar_todos')
                        ->label('Limpiar todos los reportes (incluso recientes)')
                        ->helperText('Por defecto sólo se eliminan los reportes expirados (> 30 min). Active esta casilla si desea forzar la eliminación inmediata de todos los archivos en reports y temp-reportes.')
                        ->visible(fn (Get $get): bool => in_array($get('tipo_job'), [TipoJob::LimpiezaReportesPdf->value, TipoJob::LimpiezaReportesPdf], true))
                        ->default(false),
                ])
                ->modalHeading('Ejecutar Job Manualmente')
                ->modalDescription('Seleccione el job que desea ejecutar. Se registrará en la auditoría.')
                ->modalSubmitActionLabel('Ejecutar')
                ->action(function (array $data): void {
                    $tipoJob = $data['tipo_job'] instanceof TipoJob
                        ? $data['tipo_job']
                        : TipoJob::from($data['tipo_job']);

                    $parametros = [];
                    if ($tipoJob === TipoJob::LimpiezaReportesPdf && ! empty($data['limpiar_todos'])) {
                        $parametros['--all'] = true;
                    }

                    $interactor = app(EjecutarJobManual::class);
                    $resultado = $interactor->ejecutar($tipoJob, $parametros);

                    if ($resultado->estado === EstadoEjecucionJob::Completado) {
                        Notification::make()
                            ->title('Job ejecutado exitosamente')
                            ->body($resultado->mensaje ?? $resultado->nombre_job)
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
