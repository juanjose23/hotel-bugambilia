<?php

namespace App\UseCases\Compras\Proveedores\Mutations;

use App\Models\Compras\Proveedor;
use App\Models\Personas\Persona;

class CrearProveedor
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Proveedor
    {
        /** @var array<string, mixed> $personaData */
        $personaData = (array) ($data['persona'] ?? []);
        $tipoPersona = $data['tipo_persona'] ?? 'natural';

        $persona = Persona::create(array_merge($personaData, [
            'tipo_persona' => $tipoPersona,
        ]));

        if ($tipoPersona === 'natural') {
            /** @var array<string, mixed> $naturalData */
            $naturalData = (array) ($data['personaNatural'] ?? []);
            $persona->personaNatural()->create($naturalData);
        } else {
            /** @var array<string, mixed> $juridicaData */
            $juridicaData = (array) ($data['personaJuridica'] ?? []);
            $persona->personaJuridica()->create($juridicaData);
        }

        /** @var array<string, mixed> $proveedorData */
        $proveedorData = array_diff_key($data, ['persona' => [], 'personaNatural' => [], 'personaJuridica' => []]);
        $proveedorData['persona_id'] = $persona->id;

        return Proveedor::create($proveedorData);
    }
}
