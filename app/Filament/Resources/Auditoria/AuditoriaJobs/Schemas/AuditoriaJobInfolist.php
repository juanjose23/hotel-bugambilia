<?php

declare(strict_types=1);

namespace App\Filament\Resources\Auditoria\AuditoriaJobs\Schemas;

use App\Enums\Shared\EstadoEjecucionJob;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AuditoriaJobInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Job')
                    ->schema([
                        TextEntry::make('nombre_job')
                            ->label('Job'),
                        TextEntry::make('tipo_ejecucion')
                            ->label('Tipo de Ejecución')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'manual' => 'info',
                                'automatico' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->color(fn (EstadoEjecucionJob $state): string => $state->getColor())
                            ->formatStateUsing(fn (EstadoEjecucionJob $state): string => $state->getLabel()),
                        TextEntry::make('usuario.name')
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
                            }),
                    ])
                    ->columns(2),
                Section::make('Tiempos')
                    ->schema([
                        TextEntry::make('ejecutado_en')
                            ->label('Ejecutado')
                            ->dateTime(),
                        TextEntry::make('completado_en')
                            ->label('Completado')
                            ->dateTime()
                            ->placeholder('—'),
                        TextEntry::make('duracion')
                            ->label('Duración')
                            ->state(function ($record): string {
                                if (! $record->ejecutado_en || ! $record->completado_en) {
                                    return '—';
                                }

                                $segundos = $record->ejecutado_en->diffInSeconds($record->completado_en);

                                return "{$segundos}s";
                            }),
                    ])
                    ->columns(3),
                Section::make('Resultado')
                    ->schema([
                        TextEntry::make('mensaje')
                            ->label('Mensaje')
                            ->placeholder('Sin mensaje')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
