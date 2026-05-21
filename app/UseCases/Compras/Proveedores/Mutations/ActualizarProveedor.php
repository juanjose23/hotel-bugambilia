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
        $personaData = $data['persona'] ?? [];
        $tipoPersona = $data['tipo_persona'] ?? $record->persona->tipo_persona;

        $record->persona->update($personaData);
        $record->persona->update(['tipo_persona' => $tipoPersona]);

        if ($tipoPersona === 'natural') {
            $record->persona?->personaJuridica()?->delete();
            $naturalData = $data['personaNatural'] ?? [];
            if ($record->persona->personaNatural) {
                $record->persona->personaNatural->update($naturalData);
            } else {
                $record->persona->personaNatural()->create($naturalData);
            }
        } else {
            $record->persona?->personaNatural()?->delete();
            $juridicaData = $data['personaJuridica'] ?? [];
            if ($record->persona->personaJuridica) {
                $record->persona->personaJuridica->update($juridicaData);
            } else {
                $record->persona->personaJuridica()->create($juridicaData);
            }
        }

        $proveedorData = array_diff_key($data, ['persona' => [], 'personaNatural' => [], 'personaJuridica' => []]);
        $record->update($proveedorData);

        return $record;
    }
}
