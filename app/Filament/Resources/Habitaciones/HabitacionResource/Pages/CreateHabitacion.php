<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\HabitacionResource\Pages;

use App\Filament\Resources\Habitaciones\HabitacionResource\HabitacionResource;
use App\Interactors\Habitaciones\SincronizarGaleriaImagenesHabitacion;
use App\Repository\Models\Habitaciones\Habitacion;
use Filament\Resources\Pages\CreateRecord;

class CreateHabitacion extends CreateRecord
{
    protected static string $resource = HabitacionResource::class;

    protected function afterCreate(): void
    {
        /** @var Habitacion $record */
        $record = $this->getRecord();
        $imagenes = $this->data['imagenes'] ?? null;

        if (! is_array($imagenes)) {
            return;
        }

        app(SincronizarGaleriaImagenesHabitacion::class)->ejecutar($record, $imagenes);
    }
}
