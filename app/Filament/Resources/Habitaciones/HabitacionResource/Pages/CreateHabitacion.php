<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\HabitacionResource\Pages;

use App\Filament\Resources\Habitaciones\HabitacionResource\HabitacionResource;
use App\Repository\Models\Habitaciones\Habitacion;
use Filament\Resources\Pages\CreateRecord;

class CreateHabitacion extends CreateRecord
{
    protected static string $resource = HabitacionResource::class;

    protected function afterCreate(): void
    {
        /** @var Habitacion $record */
        $record = $this->getRecord();
        $record->loadMissing('imagenes');
        $imagenes = $this->data['imagenes'] ?? null;

        if (is_array($imagenes)) {
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
