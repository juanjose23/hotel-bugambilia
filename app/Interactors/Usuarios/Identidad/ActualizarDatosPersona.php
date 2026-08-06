<?php

declare(strict_types=1);

namespace App\Interactors\Usuarios\Identidad;

use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaJuridica;
use App\Repository\Models\Personas\PersonaNatural;
use Illuminate\Support\Facades\DB;

final class ActualizarDatosPersona
{
    /**
     * Actualiza datos de contacto de una Persona existente.
     *
     * @param  array<string, mixed>  $datos
     */
    public function ejecutar(Persona $persona, array $datos): Persona
    {
        return DB::transaction(function () use ($persona, $datos): Persona {
            $tipoPersona = $datos['tipo_persona'] ?? $persona->tipo_persona ?? 'natural';
            $cambiosPersona = [];

            $camposPersona = ['primer_nombre', 'segundo_nombre', 'telefono', 'direccion', 'pais_id', 'tipo_persona'];
            foreach ($camposPersona as $campo) {
                if (array_key_exists($campo, $datos)) {
                    $cambiosPersona[$campo] = $datos[$campo];
                }
            }

            if ($cambiosPersona !== []) {
                $persona->update($cambiosPersona);
            }

            if ($tipoPersona === 'juridica') {
                $personaJuridica = $persona->personaJuridica;
                $datosJuridica = [
                    'razon_social' => $datos['razon_social'] ?? $datos['primer_nombre'] ?? '',
                    'tipo_identificacion' => $datos['tipo_identificacion'] ?? null,
                    'numero_identificacion' => $datos['numero_identificacion'] ?? null,
                    'fecha_constitucion' => $datos['fecha_nacimiento'] ?? null,
                ];
                if ($personaJuridica !== null) {
                    $personaJuridica->update($datosJuridica);
                } else {
                    PersonaJuridica::create(array_merge(['persona_id' => $persona->id], $datosJuridica));
                }
            } else {
                $personaNatural = $persona->personaNatural;
                $datosNatural = [
                    'primer_apellido' => $datos['primer_apellido'] ?? '',
                    'segundo_apellido' => $datos['segundo_apellido'] ?? null,
                    'tipo_identificacion' => $datos['tipo_identificacion'] ?? null,
                    'numero_identificacion' => $datos['numero_identificacion'] ?? null,
                    'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
                ];
                if ($personaNatural !== null) {
                    $personaNatural->update($datosNatural);
                } else {
                    PersonaNatural::create(array_merge(['persona_id' => $persona->id], $datosNatural));
                }
            }

            $refrescada = $persona->fresh();

            if (! $refrescada instanceof Persona) {
                throw new \RuntimeException('No se pudo refrescar la persona.');
            }

            return $refrescada;
        });
    }
}
