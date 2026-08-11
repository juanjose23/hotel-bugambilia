<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\HabitacionResource\Pages;

use App\Filament\Resources\Habitaciones\HabitacionResource\HabitacionResource;
use App\Repository\Models\Habitaciones\Habitacion;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHabitacion extends EditRecord
{
    protected static string $resource = HabitacionResource::class;

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

    protected function afterSave(): void
    {
        /** @var Habitacion $record */
        $record = $this->getRecord();
        $imagenes = $this->data['imagenes'] ?? null;

        if (! is_array($imagenes)) {
            return;
        }

        $record->imagenes()->delete();

        $filas = [];
        foreach ($imagenes as $index => $path) {
            if ($path) {
                $filas[] = [
                    'imageable_type' => Habitacion::class,
                    'imageable_id' => $record->id,
                    'url' => $path,
                    'orden' => $index + 1,
                ];
            }
        }

        if ($filas !== []) {
            $record->imagenes()->insert($filas);
        }
    }
}
