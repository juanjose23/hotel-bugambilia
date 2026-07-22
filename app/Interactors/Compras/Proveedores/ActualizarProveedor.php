<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Proveedores;

use App\Repository\Models\Compras\Proveedor;

final class ActualizarProveedor
{
    /** @param array<string, mixed> $datos */
    public function ejecutar(Proveedor $proveedor, array $datos): Proveedor
    {
        /** @var array<string, mixed> $datosPersona */
        $datosPersona = (array) ($datos['persona'] ?? []);
        $tipoPersona = $datos['tipo_persona'] ?? null;
        if (! is_string($tipoPersona)) {
            $tipoPersona = $proveedor->persona?->tipo_persona;
        }
        if (! is_string($tipoPersona)) {
            $tipoPersona = 'natural';
        }

        if ($proveedor->persona) {
            $proveedor->persona->update(array_merge($datosPersona, [
                'tipo_persona' => $tipoPersona,
            ]));
        }

        if ($tipoPersona === 'natural') {
            $proveedor->persona?->personaJuridica()?->delete();

            /** @var array<string, mixed> $datosNatural */
            $datosNatural = (array) ($datos['personaNatural'] ?? []);

            if ($proveedor->persona?->personaNatural) {
                $proveedor->persona->personaNatural->update($datosNatural);
            } elseif ($proveedor->persona) {
                $proveedor->persona->personaNatural()->create($datosNatural);
            }
        } else {
            $proveedor->persona?->personaNatural()?->delete();

            /** @var array<string, mixed> $datosJuridica */
            $datosJuridica = (array) ($datos['personaJuridica'] ?? []);

            if ($proveedor->persona?->personaJuridica) {
                $proveedor->persona->personaJuridica->update($datosJuridica);
            } elseif ($proveedor->persona) {
                $proveedor->persona->personaJuridica()->create($datosJuridica);
            }
        }

        $datosProveedor = array_diff_key($datos, [
            'persona' => [],
            'personaNatural' => [],
            'personaJuridica' => [],
        ]);
        $proveedor->update($datosProveedor);

        return $proveedor;
    }
}
