<?php

declare(strict_types=1);

namespace App\Filament\Resources\Auditoria\AuditoriaJobs\Tables;

use App\Enums\Shared\EstadoEjecucionJob;
use App\Enums\Shared\TipoJob;
use App\Interactors\Auditoria\EjecutarJobManual;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditoriaJobsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['usuario.persona']))
            ->columns([
                TextColumn::make('nombre_job')
                    ->label('Job')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipo_ejecucion')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'manual' => 'info',
                        'automatico' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (EstadoEjecucionJob $state): string => $state->getColor())
                    ->formatStateUsing(fn (EstadoEjecucionJob $state): string => $state->getLabel()),
                TextColumn::make('usuario.name')
                    ->label('Usuario')
                    ->formatStateUsing(function ($record): string {
                        $user = $record->usuario;
                        if (! $user) {
                            return 'Sistema';
                        }
                        if ($user->persona) {
                            return $user->persona->nombre_completo ?? $user->name;
                        }

                        return $user->name;
                    })
                    ->searchable(),
                TextColumn::make('ejecutado_en')
                    ->label('Ejecutado')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('completado_en')
                    ->label('Completado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(EstadoEjecucionJob::class),
                SelectFilter::make('tipo_job')
                    ->label('Tipo de Job')
                    ->options(TipoJob::class),
                SelectFilter::make('tipo_ejecucion')
                    ->label('Tipo de Ejecución')
                    ->options([
                        'manual' => 'Manual',
                        'automatico' => 'Automático',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                Action::make('repetir')
                    ->label('Ejecutar de nuevo')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Ejecutar Job')
                    ->modalDescription(fn ($record): string => "¿Desea ejecutar nuevamente: {$record->nombre_job}?")
                    ->modalSubmitActionLabel('Ejecutar')
                    ->action(function ($record) {
                        $tipoJob = $record->tipo_job;
                        if (! $tipoJob instanceof TipoJob) {
                            return;
                        }

                        $interactor = app(EjecutarJobManual::class);
                        $resultado = $interactor->ejecutar($tipoJob);

                        if ($resultado->estado === EstadoEjecucionJob::Completado) {
                            Notification::make()
                                ->title('Job ejecutado exitosamente')
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
            ]);
    }
}
