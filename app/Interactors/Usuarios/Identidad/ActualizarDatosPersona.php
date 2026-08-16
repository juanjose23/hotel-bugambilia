<?php

declare(strict_types=1);

namespace App\Interactors\Usuarios\Identidad;

use App\Repository\Models\Personas\Persona;
use App\Repository\Persistencia\Usuarios\PersonaPersistencia;
use Illuminate\Support\Facades\DB;

final readonly class ActualizarDatosPersona
{
    public function __construct(
        private PersonaPersistencia $personas,
    ) {}

    /**
     * Actualiza datos de contacto de una Persona existente.
     *
     * @param  array<string, mixed>  $datos
     */
    public function ejecutar(Persona $persona, array $datos): Persona
    {
        return DB::transaction(function () use ($persona, $datos): Persona {
            $tipoPersona = $datos['tipo_persona'] ?? $persona->tipo_persona ?? 'natural';

            $this->personas->actualizarDatosBasicos($persona, $datos);

            if ($tipoPersona === 'juridica') {
                $this->personas->guardarPersonaJuridica($persona, $datos);
            } else {
                $this->personas->guardarPersonaNatural($persona, $datos);
            }

            $refrescada = $persona->fresh();

            if (! $refrescada instanceof Persona) {
                throw new \RuntimeException('No se pudo refrescar la persona.');
            }

            return $refrescada;
        });
    }
}
