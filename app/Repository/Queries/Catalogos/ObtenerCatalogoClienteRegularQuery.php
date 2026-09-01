<?php

declare(strict_types=1);

namespace App\Repository\Queries\Catalogos;

use App\Repository\Models\Catalogos\Catalogo;

final class ObtenerCatalogoClienteRegularQuery
{
    public const string CODIGO_REGULAR = 'CLI_REGULAR';

    public function obtener(): ?Catalogo
    {
        return Catalogo::query()
            ->whereIn('codigo', [self::CODIGO_REGULAR, 'cliente_regular'])
            ->first();
    }
}
