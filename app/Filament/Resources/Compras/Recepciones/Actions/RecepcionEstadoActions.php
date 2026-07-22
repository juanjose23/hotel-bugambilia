<?php

namespace App\Filament\Resources\Compras\Recepciones\Actions;

use App\BusinessLogic\Compras\ValidarTransicionRecepcion;
use App\Enums\Compras\EstadoRecepcion;
use App\Filament\Shared\Concerns\InyectaDesdeContenedor;
use App\Interactors\Compras\Recepciones\GestionarTransicionRecepcion;
use App\Repository\Models\Compras\RecepcionCompra;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use InvalidArgumentException;

class RecepcionEstadoActions
{
    use InyectaDesdeContenedor;

    private readonly GestionarTransicionRecepcion $gestionarTransicionRecepcion;

    private readonly ValidarTransicionRecepcion $validarTransicion;

    public function __construct(
        GestionarTransicionRecepcion $gestionarTransicionRecepcion,
        ValidarTransicionRecepcion $validarTransicion,
    ) {
        $this->gestionarTransicionRecepcion = $gestionarTransicionRecepcion;
        $this->validarTransicion = $validarTransicion;
    }

    /** @return array<int, Action> */
    public static function acciones(): array
    {
        return app(self::class)->doMake();
    }

    /** @return array<int, Action> */
    private function doMake(): array
    {
        return array_map(
            fn (EstadoRecepcion $destino) => Action::make("transition_to_$destino->value")
                ->label("Marcar como {$destino->label()}")
                ->color(is_string($destino->color()) ? $destino->color() : 'gray')
                ->icon($destino->icon())
                ->requiresConfirmation()
                ->modalHeading('Cambiar estado de recepción')
                ->modalDescription(fn (RecepcionCompra $record) => "¿Está seguro de cambiar el estado de $record->codigo de '{$record->estado->label()}' a '{$destino->label()}'?")
                ->modalSubmitActionLabel('Confirmar cambio')
                ->action(function (RecepcionCompra $record) use ($destino): void {
                    try {
                        $this->gestionarTransicionRecepcion->ejecutar($record, $destino);

                        Notification::make()
                            ->success()
                            ->title('Estado actualizado')
                            ->body("La recepción $record->codigo ahora está en estado '{$destino->label()}'.")
                            ->send();
                    } catch (InvalidArgumentException $e) {
                        Notification::make()
                            ->danger()
                            ->title('Transición no válida')
                            ->body($e->getMessage())
                            ->send();
                    }
                })
                ->visible(fn (RecepcionCompra $record) => $this->validarTransicion->esPermitida($record->estado, $destino)
                    && (auth()->user()?->can('update', $record) ?? false)
                ),
            [
                EstadoRecepcion::Completa,
                EstadoRecepcion::Parcial,
                EstadoRecepcion::Rechazada,
            ]
        );
    }
}
