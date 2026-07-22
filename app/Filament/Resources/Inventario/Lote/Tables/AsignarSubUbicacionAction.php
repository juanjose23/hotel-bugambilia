<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\Lote\Tables;

use App\Enums\Inventario\EstadoLote;
use App\Filament\Shared\Concerns\InyectaDesdeContenedor;
use App\Interactors\Inventario\Lotes\AsignarSubUbicacion\AsignarSubUbicacionLote;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\Lote;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class AsignarSubUbicacionAction
{
    use InyectaDesdeContenedor;

    public function __construct(
        private readonly AsignarSubUbicacionLote $interactor,
    ) {}

    public static function make(): Action
    {
        return app(self::class)->doMake();
    }

    private function doMake(): Action
    {
        return Action::make('asignar_sub_ubicacion')
            ->label('Asignar Sub-Ubicación')
            ->icon(Heroicon::MapPin)
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Asignar Sub-Ubicación al Lote')
            ->modalDescription('Seleccione la sub-ubicación (estante/nivel/posición) donde desea ubicar este lote físicamente.')
            ->schema([
                Select::make('ubicacion_detalle_id')
                    ->label('Sub-Ubicación')
                    ->options(fn (Lote $record) => Ubicacion::query()
                        ->where('padre_id', $record->ubicacion_id)
                        ->pluck('nombre', 'id'))
                    ->required()
                    ->searchable(),
            ])
            ->action(function (Lote $record, array $data): void {

                try {
                    $this->interactor->ejecutar((int) $record->id, (int) $data['ubicacion_detalle_id']);

                    Notification::make()
                        ->success()
                        ->title('Sub-Ubicación asignada')
                        ->body("El lote $record->codigo_lote ha sido reubicado exitosamente.")
                        ->send();
                } catch (\Throwable $e) {
                    Notification::make()
                        ->danger()
                        ->title('Error al asignar sub-ubicación')
                        ->body($e->getMessage())
                        ->send();
                }
            })
            ->visible(fn (Lote $record) => $record->estado === EstadoLote::Disponible);
    }
}
