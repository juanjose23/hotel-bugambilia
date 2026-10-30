<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\EspacioResource\Pages;

use App\Filament\Resources\Habitaciones\EspacioResource\EspacioResource;
use App\Interactors\Espacios\ValidarUbicacionEspacio;
use App\Repository\Models\Espacios\Espacio;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditEspacio extends EditRecord
{
    protected static string $resource = EspacioResource::class;

    protected function beforeSave(): void
    {
        $record = $this->getRecord();
        if (! $record instanceof Espacio) {
            throw new \LogicException('El registro debe ser un espacio.');
        }
        $padre = $this->data['padre_id'] ?? null;
        try {
            app(ValidarUbicacionEspacio::class)->ejecutar($record->id, is_numeric($padre) ? (int) $padre : null);
        } catch (\DomainException $e) {
            throw ValidationException::withMessages(['data.padre_id' => $e->getMessage()]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
