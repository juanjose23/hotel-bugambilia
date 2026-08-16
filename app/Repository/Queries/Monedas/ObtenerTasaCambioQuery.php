<?php

declare(strict_types=1);

namespace App\Repository\Queries\Monedas;

use App\Repository\Models\Monedas\TasaCambio;

final class ObtenerTasaCambioQuery
{
    public function ejecutar(
        \DateTimeInterface|string $fecha,
        string $origenCodigo = 'USD',
        string $destinoCodigo = 'NIO',
    ): float {
        return TasaCambio::obtenerTasa($fecha, $origenCodigo, $destinoCodigo);
    }

    public function ejecutarRegistro(
        \DateTimeInterface|string $fecha,
        string $origenCodigo = 'USD',
        string $destinoCodigo = 'NIO',
    ): ?TasaCambio {
        return TasaCambio::resolverTasa($fecha, $origenCodigo, $destinoCodigo);
    }
}
