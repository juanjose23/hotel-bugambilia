<?php

declare(strict_types=1);

namespace App\Filament\Shared\Concerns;

use App\Enums\Activos\EstadoAsignacion;
use App\Interactors\Activos\Gestion\AsignarActivo;
use App\Repository\Models\Activos\ActivoAsignacion;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;

trait TieneActivoAsignaciones
{
    protected function getAsignarActivo(): AsignarActivo
    {
        return $this->asignarActivo;
    }

    /** @return list<Component> */
    protected function getAsignacionDestinoFields(): array
    {
        return [
            Select::make('asignable_type')
                ->label('Tipo de Destino')
                ->placeholder('Seleccione tipo de destino')
                ->options(TipoDestinoAsignacionActivo::destinos())
                ->live()
                ->native(false)
                ->required()
                ->afterStateUpdated(fn (callable $set) => $set('asignable_id', null)),

            Select::make('asignable_id')
                ->label('Destino Específico')
                ->placeholder('Primero seleccione un tipo de destino')
                ->options(function (Get $get) {
                    $tipo = $get('asignable_type');

                    return TipoDestinoAsignacionActivo::opcionesDestino(is_string($tipo) ? $tipo : null);
                })
                ->searchable()
                ->preload()
                ->native(false)
                ->required()
                ->hidden(fn (callable $get) => blank($get('asignable_type'))),
        ];
    }

    protected function getDesvincularAction(string $label = 'Retirar / Reasignar', string $modalHeading = 'Desvincular activo y reasignar destino'): Action
    {
        return Action::make('desvincular')
            ->label($label)
            ->icon('heroicon-o-minus-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading($modalHeading)
            ->schema([
                ...$this->getAsignacionDestinoFields(),

                Textarea::make('motivo')
                    ->label('Motivo de desvinculación')
                    ->required()
                    ->placeholder('Ej. Retiro temporal del activo'),
            ])
            ->action(function (ActivoAsignacion $record, array $data): void {
                $this->getAsignarActivo()->ejecutar(
                    activoId: $record->activo_id,
                    asignableType: $data['asignable_type'],
                    asignableId: (int) $data['asignable_id'],
                    userId: (int) auth()->id(),
                    motivo: $data['motivo']
                );
            })
            ->visible(fn (ActivoAsignacion $record) => $record->estado === EstadoAsignacion::Vigente);
    }
}
