<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\Pages;

use App\Filament\Resources\Reservas\ReservaResource;
use App\Interactors\Reservas\GenerarCodigoReserva;
use Filament\Resources\Pages\CreateRecord;

class CreateReserva extends CreateRecord
{
    protected static string $resource = ReservaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['codigo_reserva'])) {
            $data['codigo_reserva'] = app(GenerarCodigoReserva::class)->ejecutar();
        }

        return $data;
    }
}
