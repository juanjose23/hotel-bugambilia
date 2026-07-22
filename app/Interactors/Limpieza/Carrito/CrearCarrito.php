<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Carrito;

use App\Repository\Models\Catalogos\Ubicacion;

class CrearCarrito
{
    public function execute(string $nombre, ?string $descripcion = null): Ubicacion
    {
        return Ubicacion::create([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'tipo' => 'carrito',
            'estado' => 1,
            'orden' => 10,
        ]);
    }
}
