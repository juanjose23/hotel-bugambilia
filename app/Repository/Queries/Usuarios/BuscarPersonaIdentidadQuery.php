<?php

declare(strict_types=1);

namespace App\Repository\Queries\Usuarios;

use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaNatural;

final class BuscarPersonaIdentidadQuery
{
    public function __construct(
        private readonly BuscarUsuarioCuentaQuery $usuarios,
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     */
    public function porIdentificacion(array $datos): ?Persona
    {
        $tipo = $datos['tipo_identificacion'] ?? null;
        $numero = $datos['numero_identificacion'] ?? null;

        if (! is_string($tipo) || ! is_string($numero) || trim($numero) === '') {
            return null;
        }

        $personaNatural = PersonaNatural::with('persona')
            ->whereRaw("LOWER(REPLACE(REPLACE(tipo_identificacion, '-', ''), ' ', '')) = ?", [$this->normalizar($tipo)])
            ->whereRaw("LOWER(REPLACE(REPLACE(numero_identificacion, '-', ''), ' ', '')) = ?", [$this->normalizar($numero)])
            ->first();

        return $personaNatural?->persona;
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function porEmail(array $datos): ?Persona
    {
        $email = $datos['email'] ?? null;

        if (! is_string($email) || trim($email) === '') {
            return null;
        }

        $user = $this->usuarios->porEmail($email);

        return $user?->persona;
    }

    private function normalizar(string $valor): string
    {
        return mb_strtolower((string) preg_replace('/[^a-z0-9]/i', '', $valor), 'UTF-8');
    }
}
