<?php

declare(strict_types=1);

namespace App\Filament\Resources\Estancias\EstanciaResource\Pages;

use App\Filament\Resources\Estancias\EstanciaResource;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

final class ViewEstancia extends ViewRecord
{
    protected static string $resource = EstanciaResource::class;

    protected function resolveRecord(int|string $key): Model
    {
        $record = parent::resolveRecord($key);
        $record->loadMissing(['reserva.moneda', 'cuenta.moneda', 'habitacion', 'usuarioCheckIn', 'usuarioCheckOut']);

        return $record;
    }
}
