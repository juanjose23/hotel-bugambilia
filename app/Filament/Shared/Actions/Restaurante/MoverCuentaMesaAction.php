<?php

declare(strict_types=1);

namespace App\Filament\Shared\Actions\Restaurante;

use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Interactors\Restaurante\Mesas\MoverCuentaMesa;
use App\Repository\Models\Espacios\Espacio;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Throwable;

final class MoverCuentaMesaAction
{
    /**
     * @param  (Closure(): void)|null  $onSuccess
     */
    public static function make(?Closure $onSuccess = null): Action
    {
        return Action::make('moverCuenta')
            ->label('Mover Cuenta')
            ->icon(Heroicon::ArrowsRightLeft)
            ->color('warning')
            ->modalHeading('Mover Cuenta entre Mesas')
            ->modalWidth('lg')
            ->extraAttributes(['dusk' => 'mover-cuenta'])
            ->modalSubmitActionLabel('Mover Cuenta')
            ->fillForm(fn (array $arguments) => [
                'mesa_origen_id' => ! empty($arguments['mesa_origen_id']) ? (int) $arguments['mesa_origen_id'] : null,
            ])
            ->schema([
                Select::make('mesa_origen_id')
                    ->label('Mesa Origen')
                    ->placeholder('Seleccionar mesa origen...')
                    ->options(function (): array {
                        return Espacio::query()
                            ->where('tipo', TipoEspacio::MESA->value)
                            ->whereHas('pedidosActivos')
                            ->get()
                            ->mapWithKeys(fn (Espacio $m): array => [$m->id => $m->nombre])
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->native(false)
                    ->extraAttributes(['dusk' => 'mover-cuenta-origen']),

                Select::make('mesa_destino_id')
                    ->label('Mesa Destino')
                    ->placeholder('Seleccionar mesa destino...')
                    ->options(function ($get): array {
                        $origenId = $get('mesa_origen_id');

                        return Espacio::query()
                            ->where('tipo', TipoEspacio::MESA->value)
                            ->when($origenId, fn ($q) => $q->where('id', '!=', $origenId))
                            ->get()
                            ->mapWithKeys(fn (Espacio $m): array => [$m->id => $m->nombre])
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false)
                    ->extraAttributes(['dusk' => 'mover-cuenta-destino']),
            ])
            ->action(function (array $data) use ($onSuccess): void {
                try {
                    $origenId = (int) ($data['mesa_origen_id'] ?? 0);
                    $destinoId = (int) ($data['mesa_destino_id'] ?? 0);

                    app(MoverCuentaMesa::class)->ejecutar(
                        mesaOrigenId: $origenId,
                        mesaDestinoId: $destinoId
                    );

                    Notification::make()
                        ->title('Cuenta trasladada exitosamente')
                        ->success()
                        ->send();

                    if ($onSuccess !== null) {
                        $onSuccess();
                    }
                } catch (Throwable $e) {
                    Notification::make()
                        ->title('Error al mover cuenta')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
