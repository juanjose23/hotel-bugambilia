<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\Lote\Tables;

use App\Enums\Inventario\EstadoLote;
use App\Filament\Shared\Concerns\InyectaDesdeContenedor;
use App\Interactors\Inventario\Lotes\TrasladarLote;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\Lote;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class TrasladarLoteAction
{
    use InyectaDesdeContenedor;

    public function __construct(
        private readonly TrasladarLote $interactor,
    ) {}

    public static function make(): Action
    {
        return app(self::class)->doMake();
    }

    private function doMake(): Action
    {
        return Action::make('trasladar_lote')
            ->label('Trasladar')
            ->icon(Heroicon::ArrowsRightLeft)
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Trasladar Lote a Otra Ubicación')
            ->modalDescription(fn (Lote $record) => "El lote $record->codigo_lote será trasladado de «{$record->ubicacion?->nombre}» a la nueva ubicación seleccionada.")
            ->schema([
                Select::make('destino_id')
                    ->label('Ubicación de Destino')
                    ->options(fn (Lote $record) => Ubicacion::query()
                        ->where('id', '!=', $record->ubicacion_id)
                        ->whereNull('padre_id')
                        ->pluck('nombre', 'id'))
                    ->required()
                    ->searchable(),
                TextInput::make('referencia')
                    ->label('Referencia / Folio')
                    ->placeholder('Ej: Orden de traslado, folio interno...')
                    ->maxLength(255),
                Textarea::make('notas')
                    ->label('Notas del traslado')
                    ->placeholder('Observaciones adicionales sobre el traslado...')
                    ->rows(3),
            ])
            ->action(function (Lote $record, array $data): void {

                try {
                    $this->interactor->ejecutar(
                        loteId: (int) $record->id,
                        destinoId: (int) $data['destino_id'],
                        creadoPorId: (int) auth()->id(),
                        referencia: $data['referencia'] ?? null,
                        notas: $data['notas'] ?? null,
                    );

                    Notification::make()
                        ->success()
                        ->title('Lote trasladado')
                        ->body("El lote $record->codigo_lote ha sido trasladado exitosamente.")
                        ->send();
                } catch (\Throwable $e) {
                    Notification::make()
                        ->danger()
                        ->title('Error al trasladar lote')
                        ->body($e->getMessage())
                        ->send();
                }
            })
            ->visible(fn (Lote $record) => $record->estado === EstadoLote::Disponible
                && $record->cantidad_disponible > 0);
    }
}
