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

        if (is_array($imagenes)) {
            $record->imagenes()->delete();
            foreach ($imagenes as $index => $path) {
                if ($path) {
                    $record->imagenes()->create([
                        'url' => $path,
                        'orden' => $index + 1,
                    ]);
                }
            }
        }
    }
}
