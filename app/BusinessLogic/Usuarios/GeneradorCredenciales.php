<?php

declare(strict_types=1);

namespace App\BusinessLogic\Usuarios;

use App\Repository\Models\Personas\Persona;
use App\Repository\Queries\Usuarios\BuscarUsuarioCuentaQuery;

final readonly class GeneradorCredenciales
{
    private const ACCENT_MAP = [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
        'ñ' => 'n', 'Ñ' => 'n',
    ];

    public function __construct(
        private BuscarUsuarioCuentaQuery $usuarioQuery,
    ) {}

    /** @return array{name: string, email: string} */
    public function generar(Persona $persona): array
    {
        $primerNombre = str_replace(
            array_keys(self::ACCENT_MAP),
            array_values(self::ACCENT_MAP),
            mb_strtolower($persona->primer_nombre ?? 'usuario')
        );

        $primerApellido = str_replace(
            array_keys(self::ACCENT_MAP),
            array_values(self::ACCENT_MAP),
            mb_strtolower($persona->personaNatural->primer_apellido ?? 'x')
        );

        $base = $primerNombre.'.'.$primerApellido;

        $username = $base;
        $counter = 1;

        while ($this->usuarioQuery->existePorName($username)) {
            $username = $base.$counter;
            $counter++;
        }

        $domainVal = config('app.email_domain', 'hotel.com');
        $domain = is_string($domainVal) ? $domainVal : 'hotel.com';

        return [
            'name' => $username,
            'email' => $username.'@'.$domain,
        ];
    }
}
