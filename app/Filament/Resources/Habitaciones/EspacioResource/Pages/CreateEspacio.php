<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\EspacioResource\Pages;

use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Filament\Resources\Habitaciones\EspacioResource\EspacioResource;
use App\Interactors\Espacios\ValidarCapacidadMesas;
use App\Repository\Models\Espacios\Espacio;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use OverflowException;

class CreateEspacio extends CreateRecord
{
    protected static string $resource = EspacioResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function beforeCreate(): void
    {
        /** @var array<string, mixed> $data */
        $data = $this->data;
        $tipo = $data['tipo'] ?? null;
        $padreIdRaw = $data['padre_id'] ?? null;
        $padreId = is_numeric($padreIdRaw) ? (int) $padreIdRaw : null;

        if (($tipo === TipoEspacio::MESA->value || $tipo === TipoEspacio::MESA) && $padreId !== null) {
            $padre = Espacio::find($padreId);
            if ($padre instanceof Espacio && $padre->tipo === TipoEspacio::RESTAURANTE) {
                try {
                    app(ValidarCapacidadMesas::class)->execute($padreId, false);
                } catch (OverflowException $e) {
                    Notification::make()
                        ->title('Capacidad máxima de mesas alcanzada')
                        ->body($e->getMessage())
                        ->danger()
                        ->persistent()
                        ->send();

                    $this->halt();
                }
            }
        }
    }
}
