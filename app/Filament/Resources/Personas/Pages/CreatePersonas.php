<?php

namespace App\Filament\Resources\Personas\Pages;

use App\Models\Persona;
use App\UseCases\Personas\CrearPersona;
use App\Filament\Resources\Personas\PersonasResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePersonas extends CreateRecord
{
    protected static string $resource = PersonasResource::class;

    protected function handleRecordCreation(array $data): Persona
    {
        return app(CrearPersona::class)->execute($data);
    }
}