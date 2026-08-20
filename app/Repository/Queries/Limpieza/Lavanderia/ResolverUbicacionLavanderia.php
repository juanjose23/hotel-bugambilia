<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Lavanderia;

use App\Repository\Models\Catalogos\Ubicacion;

final class ResolverUbicacionLavanderia
{
    public function execute(): Ubicacion
    {
        return Ubicacion::query()->firstOrCreate(
            ['tipo' => 'lavanderia'],
            [
                'nombre' => 'Lavandería Central',
                'descripcion' => 'Bodega virtual para la ropa de cama y blancos en proceso de lavado.',
                'estado' => 1,
            ],
        );
    }
}
