<?php

declare(strict_types=1);

namespace App\BusinessLogic\Usuarios;

use App\Repository\Models\Personas\Persona;

final class SincronizarContactoPersona
{
    /**
     * Decide qué campos de contacto deben actualizarse en la persona.
     *
     * Solo se actualiza el contacto provisto y diferente al existente.
     * Es una regla de negocio pura: no accede a la base de datos.
     *
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    public function decidirCambios(Persona $persona, array $datos): array
    {
        $cambios = [];

        if (isset($datos['telefono']) && $datos['telefono'] !== $persona->telefono) {
            $cambios['telefono'] = $datos['telefono'];
        }

        if (isset($datos['direccion']) && $datos['direccion'] !== $persona->direccion) {
            $cambios['direccion'] = $datos['direccion'];
        }

        return $cambios;
    }
}
