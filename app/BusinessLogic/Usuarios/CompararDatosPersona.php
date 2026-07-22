<?php

declare(strict_types=1);

namespace App\BusinessLogic\Usuarios;

use App\Enums\Usuarios\TipoConflictoIdentidad;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaNatural;

final class CompararDatosPersona
{
    /**
     * Compara datos provistos vs existentes y clasifica la diferencia.
     *
     * @param  array<string, mixed>  $datosProvidos
     * @return array{tipo: string, tipo_conflicto: TipoConflictoIdentidad|null}
     */
    public function comparar(Persona $persona, array $datosProvidos): array
    {
        $personaNatural = $persona->personaNatural;

        if (! $personaNatural instanceof PersonaNatural) {
            return [
                'tipo' => 'conflicto_identidad',
                'tipo_conflicto' => TipoConflictoIdentidad::IdentidadDudosa,
            ];
        }

        $nombreProvido = $this->normalizar(is_string($datosProvidos['primer_nombre'] ?? null) ? $datosProvidos['primer_nombre'] : '');
        $nombreExistente = $this->normalizar($persona->primer_nombre ?? '');

        $apellidoProvido = $this->normalizar(is_string($datosProvidos['primer_apellido'] ?? null) ? $datosProvidos['primer_apellido'] : '');
        $apellidoExistente = $this->normalizar($personaNatural->primer_apellido ?? '');

        $identidadCoincide = levenshtein($nombreProvido, $nombreExistente) <= 2
            && levenshtein($apellidoProvido, $apellidoExistente) <= 2;

        if ($identidadCoincide) {
            $telefonoProvido = $this->normalizar(is_string($datosProvidos['telefono'] ?? null) ? $datosProvidos['telefono'] : '');
            $telefonoExistente = $this->normalizar($persona->telefono ?? '');
            $direccionProvida = $this->normalizar(is_string($datosProvidos['direccion'] ?? null) ? $datosProvidos['direccion'] : '');
            $direccionExistente = $this->normalizar($persona->direccion ?? '');

            $contactoDifiere = $telefonoProvido !== $telefonoExistente
                || $direccionProvida !== $direccionExistente;

            if ($contactoDifiere) {
                return [
                    'tipo' => 'actualizar_contacto',
                    'tipo_conflicto' => null,
                ];
            }

            return [
                'tipo' => 'vincular_directo',
                'tipo_conflicto' => null,
            ];
        }

        return [
            'tipo' => 'conflicto_identidad',
            'tipo_conflicto' => TipoConflictoIdentidad::Homonimia,
        ];
    }

    private function normalizar(string $valor): string
    {
        return mb_strtolower(trim($valor), 'UTF-8');
    }
}
