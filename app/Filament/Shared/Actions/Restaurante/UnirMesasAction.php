<?php

declare(strict_types=1);

namespace App\Filament\Shared\Actions\Restaurante;

use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Interactors\Restaurante\Mesas\UnirMesas;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Reservas\Reserva;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Throwable;

final class UnirMesasAction
{
    /**
     * @param  (Closure(): void)|null  $onSuccess
     */
    public static function make(?Closure $onSuccess = null): Action
    {
        return Action::make('unirMesas')
            ->label('Unir Mesas')
            ->icon(Heroicon::Link)
            ->color('primary')
            ->modalHeading('Unir Mesas')
            ->modalWidth('lg')
            ->extraAttributes(['dusk' => 'unir-mesas'])
            ->modalSubmitActionLabel('Confirmar Unión')
            ->modalSubmitAction(fn (Action $action) => $action->extraAttributes(['dusk' => 'confirmar-union']))
            ->schema([
                Select::make('mesa_principal_id')
                    ->label('Mesa Principal')
                    ->placeholder('Seleccionar mesa principal...')
                    ->options(function (): array {
                        return Espacio::query()
                            ->where('tipo', TipoEspacio::MESA->value)
                            ->get()
                            ->filter(function (Espacio $mesa): bool {
                                $meta = is_array($mesa->meta_datos) ? $mesa->meta_datos : [];

                                return empty($meta['mesa_principal_id'] ?? null);
                            })
                            ->mapWithKeys(function (Espacio $mesa): array {
                                $estadoLabel = ' — '.$mesa->estado->getLabel();

                                return [$mesa->id => $mesa->nombre.$estadoLabel];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->native(false)
                    ->extraAttributes(['dusk' => 'unir-mesa-principal']),

                CheckboxList::make('mesas_secundarias_ids')
                    ->label('Mesas a Unir (Secundarias)')
                    ->options(function ($get): array {
                        $principalId = $get('mesa_principal_id');

                        return Espacio::query()
                            ->where('tipo', TipoEspacio::MESA->value)
                            ->when($principalId, fn ($q) => $q->where('id', '!=', $principalId))
                            ->get()
                            ->filter(function (Espacio $mesa): bool {
                                $meta = is_array($mesa->meta_datos) ? $mesa->meta_datos : [];

                                return empty($meta['mesa_principal_id'] ?? null) && empty($meta['mesas_unidas'] ?? null);
                            })
                            ->mapWithKeys(function (Espacio $mesa): array {
                                $estadoLabel = ' — '.$mesa->estado->getLabel();

                                return [$mesa->id => $mesa->nombre.$estadoLabel];
                            })
                            ->toArray();
                    })
                    ->columns(2)
                    ->required(),

                Select::make('motivo')
                    ->label('Motivo de Unión')
                    ->options([
                        'uso_inmediato' => 'Uso Inmediato',
                        'reserva_grupal' => 'Reserva Grupal',
                        'evento_especial' => 'Evento Especial',
                    ])
                    ->default('uso_inmediato')
                    ->required()
                    ->live()
                    ->native(false),

                Select::make('reserva_id')
                    ->label('Reserva Asociada (Opcional)')
                    ->placeholder('Sin reserva...')
                    ->options(function (): array {
                        return Reserva::query()
                            ->latest('id')
                            ->take(30)
                            ->get()
                            ->mapWithKeys(fn (Reserva $r): array => [$r->id => $r->codigo_reserva ?? 'Reserva #'.$r->id])
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->visible(fn ($get): bool => $get('motivo') === 'reserva_grupal')
                    ->native(false),
            ])
            ->action(function (array $data) use ($onSuccess): void {
                try {
                    $principalId = (int) ($data['mesa_principal_id'] ?? 0);
                    /** @var array<int|string> $secundariasRaw */
                    $secundariasRaw = $data['mesas_secundarias_ids'] ?? [];
                    $secundarias = array_map(fn ($id): int => (int) $id, $secundariasRaw);
                    $reservaId = ! empty($data['reserva_id']) ? (int) $data['reserva_id'] : null;
                    $motivo = (string) ($data['motivo'] ?? 'uso_inmediato');

                    app(UnirMesas::class)->ejecutar(
                        mesaPrincipalId: $principalId,
                        mesasSecundariasIds: $secundarias,
                        reservaId: $reservaId,
                        motivo: $motivo
                    );

                    Notification::make()
                        ->title('Mesas unidas exitosamente')
                        ->success()
                        ->send();

                    if ($onSuccess !== null) {
                        $onSuccess();
                    }
                } catch (Throwable $e) {
                    Notification::make()
                        ->title('Error al unir mesas')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
