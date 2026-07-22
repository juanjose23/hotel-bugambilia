<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Proveedores;

use App\Repository\Models\Compras\Proveedor;
use App\Repository\Models\Personas\Persona;

final class CrearProveedor
{
    /** @param array<string, mixed> $datos */
    public function ejecutar(array $datos): Proveedor
    {
        /** @var array<string, mixed> $datosPersona */
        $datosPersona = (array) ($datos['persona'] ?? []);
        $tipoPersona = $datos['tipo_persona'] ?? null;
        if (! is_string($tipoPersona)) {
            $tipoPersona = 'natural';
        }

        $persona = Persona::create(array_merge($datosPersona, [
            'tipo_persona' => $tipoPersona,
        ]));

        if ($tipoPersona === 'natural') {
            /** @var array<string, mixed> $datosNatural */
            $datosNatural = (array) ($datos['personaNatural'] ?? []);
            $persona->personaNatural()->create($datosNatural);
        } else {
            /** @var array<string, mixed> $datosJuridica */
            $datosJuridica = (array) ($datos['personaJuridica'] ?? []);
            $persona->personaJuridica()->create($datosJuridica);
        }

        $datosProveedor = array_diff_key($datos, [
            'persona' => [],
            'personaNatural' => [],
            'personaJuridica' => [],
        ]);
        $datosProveedor['persona_id'] = $persona->id;

        return Proveedor::create($datosProveedor);
    }
}
