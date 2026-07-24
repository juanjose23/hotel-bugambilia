<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\Pages;

use App\Filament\Resources\Reservas\ReservaResource;
use App\Interactors\Reservas\CrearReserva;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateReserva extends CreateRecord
{
    protected static string $resource = ReservaResource::class;

    protected CrearReserva $crearReserva;

    public function boot(CrearReserva $crearReserva): void
    {
        $this->crearReserva = $crearReserva;
    }

    /** @param array<string, mixed> $data */
    protected function handleRecordCreation(array $data): Model
    {
        $servicios = $data['servicios_adicionales'] ?? [];
        $espacios = $data['espacios_adicionales'] ?? [];

        return $this->crearReserva->ejecutar(
            $data,
            is_array($servicios) ? $servicios : [],
            is_array($espacios) ? $espacios : [],
        );
    }
}
