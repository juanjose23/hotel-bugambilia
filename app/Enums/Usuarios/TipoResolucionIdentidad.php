<?php

declare(strict_types=1);

namespace App\Enums\Usuarios;

use Filament\Support\Contracts\HasLabel;

enum TipoResolucionIdentidad: string implements HasLabel
{
    case CrearNueva = 'crear_nueva';
    case VincularDirecto = 'vincular_directo';
    case ActualizarContacto = 'actualizar_contacto';
    case ConflictoIdentidad = 'conflicto_identidad';

    public function getLabel(): string
    {
        return match ($this) {
            self::CrearNueva => 'Crear nueva',
            self::VincularDirecto => 'Vincular directo',
            self::ActualizarContacto => 'Actualizar contacto',
            self::ConflictoIdentidad => 'Conflicto de identidad',
        };
    }
}
