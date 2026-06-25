<?php

namespace App\UseCases\Usuarios\Mutations;

use App\Models\Personas\Persona;
use App\Models\User;

class GenerarCredencialesUsuario
{
    private const ACCENT_MAP = [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
        'ñ' => 'n', 'Ñ' => 'n',
    ];

    /** @return array{name: string, email: string} */
    public function execute(Persona $persona): array
    {
        $primerNombre = str_replace(
            array_keys(self::ACCENT_MAP),
            array_values(self::ACCENT_MAP),
            mb_strtolower($persona->primer_nombre)
        );

        $primerApellido = str_replace(
            array_keys(self::ACCENT_MAP),
            array_values(self::ACCENT_MAP),
            mb_strtolower($persona->personaNatural->primer_apellido ?? 'x')
        );

        $base = $primerNombre.'.'.$primerApellido;

        $username = $base;
        $counter = 1;

        while (User::where('name', $username)->exists()) {
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
