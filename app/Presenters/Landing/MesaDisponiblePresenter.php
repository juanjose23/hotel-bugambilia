<?php

declare(strict_types=1);

namespace App\Presenters\Landing;

use App\Repository\Models\Espacios\Espacio;

final class MesaDisponiblePresenter
{
    private const int CAPACIDAD_POR_DEFECTO = 4;

    private const string UBICACION_POR_DEFECTO = 'Salón Principal';

    /**
     * @return array{id: int, nombre: string, capacidad: int, ubicacion: string}
     */
    public function mesaLibre(Espacio $mesa): array
    {
        return [
            'id' => (int) $mesa->id,
            'nombre' => (string) $mesa->nombre,
            'capacidad' => $this->capacidad($mesa),
            'ubicacion' => $this->ubicacion($mesa),
        ];
    }

    /**
     * @return array{id: int, nombre: string, capacidad: int, ubicacion: string, zona: string}
     */
    public function mesaConZona(Espacio $mesa, string $zona): array
    {
        return [
            ...$this->mesaLibre($mesa),
            'zona' => $zona,
        ];
    }

    public function capacidad(Espacio $mesa): int
    {
        return (int) ($mesa->capacidad_personas ?? self::CAPACIDAD_POR_DEFECTO);
    }

    private function ubicacion(Espacio $mesa): string
    {
        return $mesa->ubicacion !== null ? (string) $mesa->ubicacion->nombre : self::UBICACION_POR_DEFECTO;
    }
}
