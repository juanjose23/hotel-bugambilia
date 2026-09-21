<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Cotizaciones;

use App\BusinessLogic\Compras\VerificarSolicitudBloqueada;
use App\Enums\Compras\EstadoCotizacion;
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Persistencia\Compras\CotizacionRepositorioInterface;
use DomainException;

final readonly class RechazarCotizacion
{
    public function __construct(
        private VerificarSolicitudBloqueada $verificarBloqueo,
        private CotizacionRepositorioInterface $cotizacionRepositorio,
    ) {}

    public function ejecutar(Cotizacion $cotizacion, string $motivo): void
    {
        if ($cotizacion->estado !== EstadoCotizacion::Activa) {
            throw new DomainException('Solo se pueden rechazar cotizaciones activas.');
        }

        $solicitud = $cotizacion->solicitud;
        if ($solicitud === null || $this->verificarBloqueo->estaBloqueada($solicitud)) {
            throw new DomainException('No se puede rechazar la cotización porque la solicitud tiene órdenes activas.');
        }

        $this->cotizacionRepositorio->rechazar($cotizacion, $motivo);
    }
}
