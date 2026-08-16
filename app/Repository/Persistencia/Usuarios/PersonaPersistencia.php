<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Usuarios;

use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaJuridica;
use App\Repository\Models\Personas\PersonaNatural;

final class PersonaPersistencia
{
    /**
     * @param  array<string, mixed>  $datos
     */
    public function crearConIdentidad(array $datos): Persona
    {
        $tipoPersona = $datos['tipo_persona'] ?? 'natural';

        $persona = Persona::create([
            'primer_nombre' => $datos['primer_nombre'],
            'segundo_nombre' => $datos['segundo_nombre'] ?? null,
            'tipo_persona' => $tipoPersona,
            'telefono' => $datos['telefono'] ?? null,
            'direccion' => $datos['direccion'] ?? null,
            'pais_id' => $datos['pais_id'] ?? null,
        ]);

        if ($tipoPersona === 'juridica') {
            $this->crearPersonaJuridica($persona, $datos);
        } else {
            $this->crearPersonaNatural($persona, $datos);
        }

        return $persona;
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizarDatosBasicos(Persona $persona, array $datos): void
    {
        $cambios = [];

        foreach (['primer_nombre', 'segundo_nombre', 'telefono', 'direccion', 'pais_id', 'tipo_persona'] as $campo) {
            if (array_key_exists($campo, $datos)) {
                $cambios[$campo] = $datos[$campo];
            }
        }

        if ($cambios !== []) {
            $persona->update($cambios);
        }
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function guardarPersonaJuridica(Persona $persona, array $datos): void
    {
        $datosJuridica = [
            'razon_social' => $datos['razon_social'] ?? $datos['primer_nombre'] ?? '',
            'tipo_identificacion' => $datos['tipo_identificacion'] ?? null,
            'numero_identificacion' => $datos['numero_identificacion'] ?? null,
            'fecha_constitucion' => $datos['fecha_nacimiento'] ?? null,
        ];

        $personaJuridica = $persona->personaJuridica;

        if ($personaJuridica !== null) {
            $personaJuridica->update($datosJuridica);

            return;
        }

        PersonaJuridica::create(array_merge(['persona_id' => $persona->id], $datosJuridica));
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function guardarPersonaNatural(Persona $persona, array $datos): void
    {
        $datosNatural = [
            'primer_apellido' => $datos['primer_apellido'] ?? '',
            'segundo_apellido' => $datos['segundo_apellido'] ?? null,
            'tipo_identificacion' => $datos['tipo_identificacion'] ?? null,
            'numero_identificacion' => $datos['numero_identificacion'] ?? null,
            'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
        ];

        $personaNatural = $persona->personaNatural;

        if ($personaNatural !== null) {
            $personaNatural->update($datosNatural);

            return;
        }

        PersonaNatural::create(array_merge(['persona_id' => $persona->id], $datosNatural));
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function crearPersonaJuridica(Persona $persona, array $datos): void
    {
        PersonaJuridica::create([
            'persona_id' => $persona->id,
            'razon_social' => $datos['razon_social'] ?? $datos['primer_nombre'],
            'tipo_identificacion' => $datos['tipo_identificacion'] ?? null,
            'numero_identificacion' => $datos['numero_identificacion'] ?? null,
            'fecha_constitucion' => $datos['fecha_nacimiento'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function crearPersonaNatural(Persona $persona, array $datos): void
    {
        PersonaNatural::create([
            'persona_id' => $persona->id,
            'primer_apellido' => $datos['primer_apellido'] ?? '',
            'segundo_apellido' => $datos['segundo_apellido'] ?? null,
            'tipo_identificacion' => $datos['tipo_identificacion'] ?? null,
            'numero_identificacion' => $datos['numero_identificacion'] ?? null,
            'sexo' => $datos['sexo'] ?? null,
            'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
        ]);
    }
}
