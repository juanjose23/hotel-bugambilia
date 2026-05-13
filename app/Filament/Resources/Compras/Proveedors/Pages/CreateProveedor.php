<?php

namespace App\Filament\Resources\Compras\Proveedors\Pages;

use App\Filament\Resources\Compras\Proveedors\ProveedorResource;
use App\Models\Personas\Persona;
use App\UseCases\Compras\GenerarCodigoProveedor;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProveedor extends CreateRecord
{
    protected static string $resource = ProveedorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['codigo'] ?? null)) {
            $data['codigo'] = app(GenerarCodigoProveedor::class)->ejecutar();
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $personaData = $data['persona'] ?? [];
        $tipoPersona = $data['tipo_persona'] ?? 'natural';

        $persona = Persona::create(array_merge($personaData, [
            'tipo_persona' => $tipoPersona,
        ]));

        if ($tipoPersona === 'natural') {
            $naturalData = $data['personaNatural'] ?? [];
            $persona->personaNatural()->create($naturalData);
        } else {
            $juridicaData = $data['personaJuridica'] ?? [];
            $persona->personaJuridica()->create($juridicaData);
        }

        $proveedorData = array_diff_key($data, ['persona' => [], 'personaNatural' => [], 'personaJuridica' => []]);
        $proveedorData['persona_id'] = $persona->id;

        return $this->getModel()::create($proveedorData);
    }
}
