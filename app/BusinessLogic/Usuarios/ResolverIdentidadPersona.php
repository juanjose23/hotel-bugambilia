<?php

declare(strict_types=1);

namespace App\BusinessLogic\Usuarios;

use App\Enums\Usuarios\TipoConflictoIdentidad;
use App\Enums\Usuarios\TipoResolucionIdentidad;
use App\Exceptions\YaTieneCuentaException;
use App\Repository\Models\Personas\Persona;
use App\Repository\Queries\Usuarios\BuscarPersonaIdentidadQuery;

final class ResolverIdentidadPersona
{
    public function __construct(
        private readonly CompararDatosPersona $comparador,
        private readonly BuscarPersonaIdentidadQuery $personas,
    ) {}

    /**
     * Resuelve la identidad de una persona según su identificación y correo.
     *
     * Flujo:
     *   1. Si no existe persona por identificación (ni por correo) → crear nueva.
     *   2. Si la persona ya tiene cuenta (users) → YaTieneCuentaException.
     *   3. Si los datos coinciden → vincular directo.
     *   4. Si solo difieren teléfono/dirección → actualizar contacto y vincular.
     *   5. Si difieren nombres → conflicto de identidad (revisión manual).
     *
     * @param  array<string, mixed>  $datos
     * @return array{tipo: TipoResolucionIdentidad, persona: Persona|null, tipo_conflicto: TipoConflictoIdentidad|null}
     *
     * @throws YaTieneCuentaException
     */
    public function resolver(array $datos): array
    {
        $persona = $this->personas->porIdentificacion($datos)
            ?? $this->personas->porEmail($datos);

        if ($persona === null) {
            return [
                'tipo' => TipoResolucionIdentidad::CrearNueva,
                'persona' => null,
                'tipo_conflicto' => null,
            ];
        }

        if ($persona->user !== null) {
            throw new YaTieneCuentaException(
                'Esta cuenta ya está registrada. Por favor, inicie sesión.',
                $persona
            );
        }

        $comparacion = $this->comparador->comparar($persona, $datos);

        return [
            'tipo' => $comparacion['tipo'],
            'persona' => $persona,
            'tipo_conflicto' => $comparacion['tipo_conflicto'],
        ];
    }
}
