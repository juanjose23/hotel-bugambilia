<?php

declare(strict_types=1);

namespace App\Filament\Resources\Shared\Concerns;

use App\Enums\Activos\EstadoAsignacion;
use App\Models\Activos\ActivoAsignacion;
use App\Models\Catalogos\Ubicacion;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\UseCases\Activos\Mutations\Asignacion\AsignarActivo;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;

trait HasActivoAsignaciones
{
    /**
     * @return array<int, Select>
     */
    protected function getAsignacionDestinoFields(): array
    {
        return [
            Select::make('asignable_type')
                ->label('Tipo de Destino')
                ->placeholder('Seleccione tipo de destino')
                ->options([
                    Habitacion::class => 'Habitación',
                    Ubicacion::class => 'Ubicación / Bodega',
                    Espacio::class => 'Espacio / Área Común',
                ])
                ->live()
                ->native(false)
                ->required()
                ->afterStateUpdated(fn (callable $set) => $set('asignable_id', null)),

            Select::make('asignable_id')
                ->label('Destino Específico')
                ->placeholder('Primero seleccione un tipo de destino')
                ->options(function (Get $get) {
                    $type = $get('asignable_type');

                    return match ($type) {
                        Habitacion::class => Habitacion::pluck('nombre', 'id'),
                        Ubicacion::class => Ubicacion::pluck('nombre', 'id'),
                        Espacio::class => Espacio::pluck('nombre', 'id'),
                        default => [],
                    };
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
                app(AsignarActivo::class)->execute(
                    activoId: $record->activo_id,
                    asignableType: $data['asignable_type'],
                    asignableId: (int) $data['asignable_id'],
                    userId: auth()->id() ?? 1,
                    motivo: $data['motivo']
                );
            })
            ->visible(fn (ActivoAsignacion $record) => $record->estado === EstadoAsignacion::Vigente);
    }
}
