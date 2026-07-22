<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Cotizaciones;

use App\BusinessLogic\Compras\ResolverEstrategiaCompra;
use App\Repository\Models\Compras\Solicitud;

final class ObtenerRecomendacionLogistica
{
    public function __construct(
        private readonly ResolverEstrategiaCompra $resolverEstrategia,
    ) {}

    /** @return array<string, mixed> */
    public function ejecutar(Solicitud $solicitud): array
    {
        $solicitud->loadMissing('items');
        $cotizaciones = $solicitud->cotizaciones()->with(['proveedor.persona.personaJuridica', 'items'])->get();

        return $this->resolverEstrategia->ejecutar($solicitud, $cotizaciones);
    }
}
