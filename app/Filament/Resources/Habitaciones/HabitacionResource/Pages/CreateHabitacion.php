<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\HabitacionResource\Pages;

use App\Filament\Resources\Habitaciones\HabitacionResource\HabitacionResource;
use App\Interactors\Habitaciones\SincronizarGaleriaImagenesHabitacion;
use App\Interactors\Habitaciones\SincronizarVistasHabitacion;
use App\Repository\Models\Habitaciones\Habitacion;
use Filament\Resources\Pages\CreateRecord;

class CreateHabitacion extends CreateRecord
{
    protected ?bool $hasDatabaseTransactions = true;

    protected static string $resource = HabitacionResource::class;

    protected function afterCreate(): void
    {
        /** @var Habitacion $record */
        $record = $this->getRecord();
        $vistas = $this->data['vista_ids'] ?? [];
        app(SincronizarVistasHabitacion::class)->ejecutar($record, is_array($vistas) ? $vistas : []);
        $imagenes = $this->data['imagenes'] ?? null;

        if (! is_array($imagenes)) {
            return;
        }

        app(SincronizarGaleriaImagenesHabitacion::class)->ejecutar($record, $imagenes);
    }
}
