<?php

namespace App\Filament\Resources\Compras\Recepciones\Actions;

use App\Enums\Compras\EstadoRecepcion;
use App\Models\Compras\RecepcionCompra;
use App\UseCases\Compras\Recepciones\Mutations\GestionarTransicionRecepcion;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class RecepcionEstadoActions
{
    /** @return array<int, Action> */
    public static function make(): array
    {
        return array_map(
            fn (EstadoRecepcion $destino) => Action::make("transition_to_{$destino->value}")
                ->label("Marcar como {$destino->label()}")
                ->color($destino->color())
                ->icon($destino->icon())
                ->requiresConfirmation()
                ->modalHeading('Cambiar estado de recepción')
                ->modalDescription(fn (RecepcionCompra $record) => "¿Está seguro de cambiar el estado de {$record->codigo} de '{$record->estado->label()}' a '{$destino->label()}'?")
                ->modalSubmitActionLabel('Confirmar cambio')
                ->action(function (RecepcionCompra $record) use ($destino): void {
                    try {
                        app(GestionarTransicionRecepcion::class)->execute($record, $destino);

                        Notification::make()
                            ->success()
                            ->title('Estado actualizado')
                            ->body("La recepción {$record->codigo} ahora está en estado '{$destino->label()}'.")
                            ->send();
                    } catch (\InvalidArgumentException $e) {
                        Notification::make()
                            ->danger()
                            ->title('Transición no válida')
                            ->body($e->getMessage())
                            ->send();
                    }
                })
                ->visible(fn (RecepcionCompra $record) => $record->estado->transicionPermitida($destino)
                    && auth()->user()->can('update', $record)
                ),
            [
                EstadoRecepcion::Completa,
                EstadoRecepcion::Parcial,
                EstadoRecepcion::Rechazada,
            ]
        );
    }
}
