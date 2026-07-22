<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\Lote\Tables;

use App\Enums\Inventario\EstadoLote;
use App\Filament\Shared\Concerns\InyectaDesdeContenedor;
use App\Interactors\Inventario\Lotes\RegistrarMerma\RegistrarMerma;
use App\Repository\Models\Inventario\Lote;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class RegistrarMermaAction
{
    use InyectaDesdeContenedor;

    public function __construct(
        private readonly RegistrarMerma $interactor,
    ) {}

    public static function make(): Action
    {
        return app(self::class)->doMake();
    }

    private function doMake(): Action
    {
        return Action::make('registrar_merma')
            ->label('Registrar Merma')
            ->icon(Heroicon::ArrowTrendingDown)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Registrar Merma / Pérdida')
            ->modalDescription(fn (Lote $record) => "Registrará una merma para el lote $record->codigo_lote con $record->cantidad_disponible unidades disponibles.")
            ->schema([
                TextInput::make('cantidad')
                    ->label('Cantidad a dar de baja')
                    ->numeric()
                    ->required()
                    ->minValue(0.01)
                    ->step(0.01)
                    ->suffix('unidades'),
                Textarea::make('motivo')
                    ->label('Motivo de la merma')
                    ->placeholder('Ej: Vencimiento, daño, rechazo de calidad...')
                    ->rows(3),
            ])
            ->action(function (Lote $record, array $data): void {

                try {
                    $this->interactor->ejecutar(
                        loteId: (int) $record->id,
                        cantidad: (float) $data['cantidad'],
                        motivo: $data['motivo'] ?? null,
                        creadoPorId: (int) auth()->id(),
                    );

                    Notification::make()
                        ->success()
                        ->title('Merma registrada')
                        ->body("Se han dado de baja {$data['cantidad']} unidades del lote $record->codigo_lote.")
                        ->send();
                } catch (\Throwable $e) {
                    Notification::make()
                        ->danger()
                        ->title('Error al registrar merma')
                        ->body($e->getMessage())
                        ->send();
                }
            })
            ->visible(fn (Lote $record) => in_array($record->estado, [EstadoLote::Disponible, EstadoLote::Cuarentena])
                && $record->cantidad_disponible > 0);
    }
}
