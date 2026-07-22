<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\Lote\Tables;

use App\Enums\Inventario\EstadoLote;
use App\Filament\Shared\Concerns\InyectaDesdeContenedor;
use App\Interactors\Inventario\Lotes\EnviarACuarentena\EnviarACuarentena;
use App\Repository\Models\Inventario\Lote;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class EnviarCuarentenaAction
{
    use InyectaDesdeContenedor;

    public function __construct(
        private readonly EnviarACuarentena $interactor,
    ) {}

    public static function make(): Action
    {
        return app(self::class)->doMake();
    }

    private function doMake(): Action
    {
        return Action::make('enviar_cuarentena')
            ->label('Enviar a Cuarentena')
            ->icon(Heroicon::ShieldExclamation)
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Enviar Lote a Cuarentena')
            ->modalDescription(fn (Lote $record) => "El lote $record->codigo_lote será marcado como en CUARENTENA. Su stock no estará disponible para consumo hasta que sea liberado o rechazado.")
            ->schema([
                Textarea::make('motivo')
                    ->label('Motivo de cuarentena')
                    ->placeholder('Ej: Pendiente de verificación de calidad, muestra enviada a laboratorio...')
                    ->rows(3),
            ])
            ->action(function (Lote $record, array $data): void {

                try {
                    $this->interactor->ejecutar(
                        loteId: (int) $record->id,
                        motivo: $data['motivo'] ?? null,
                        creadoPorId: (int) auth()->id(),
                    );

                    Notification::make()
                        ->warning()
                        ->title('Lote enviado a cuarentena')
                        ->body("El lote $record->codigo_lote ha sido reclasificado como CUARENTENA.")
                        ->send();
                } catch (\Throwable $e) {
                    Notification::make()
                        ->danger()
                        ->title('Error al enviar a cuarentena')
                        ->body($e->getMessage())
                        ->send();
                }
            })
            ->visible(fn (Lote $record) => $record->estado === EstadoLote::Disponible
                && $record->cantidad_disponible > 0);
    }
}
