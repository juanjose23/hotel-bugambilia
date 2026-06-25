<?php

namespace App\UseCases\Compras\Proveedores\Mutations;

use App\Models\Compras\Proveedor;

class ActualizarProveedor
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Proveedor $record, array $data): Proveedor
    {
        /** @var array<string, mixed> $personaData */
        $personaData = (array) ($data['persona'] ?? []);
        $tipoPersona = $data['tipo_persona'] ?? $record->persona?->tipo_persona;

        if ($record->persona) {
            $record->persona->update($personaData);
            $record->persona->update(['tipo_persona' => $tipoPersona]);
        }

        if ($tipoPersona === 'natural') {
            $record->persona?->personaJuridica()?->delete();
            /** @var array<string, mixed> $naturalData */
            $naturalData = (array) ($data['personaNatural'] ?? []);
            if ($record->persona?->personaNatural) {
                $record->persona->personaNatural->update($naturalData);
            } elseif ($record->persona) {
                $record->persona->personaNatural()->create($naturalData);
            }
        } else {
            $record->persona?->personaNatural()?->delete();
            /** @var array<string, mixed> $juridicaData */
            $juridicaData = (array) ($data['personaJuridica'] ?? []);
            if ($record->persona?->personaJuridica) {
                $record->persona->personaJuridica->update($juridicaData);
            } elseif ($record->persona) {
                $record->persona->personaJuridica()->create($juridicaData);
            }
        }

        /** @var array<string, mixed> $proveedorData */
        $proveedorData = array_diff_key($data, ['persona' => [], 'personaNatural' => [], 'personaJuridica' => []]);
        $record->update($proveedorData);

        return $record;
    }
}
