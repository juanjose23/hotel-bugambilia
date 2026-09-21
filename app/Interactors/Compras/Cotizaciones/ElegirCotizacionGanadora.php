<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Cotizaciones;

use App\Repository\Persistencia\Compras\CotizacionRepositorioInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final readonly class ElegirCotizacionGanadora
{
    public function __construct(
        private ActualizarEstadosCotizacionesSolicitud $actualizarEstados,
        private CotizacionRepositorioInterface $cotizacionRepositorio,
    ) {}

    public function ejecutar(int $cotizacionId): void
    {
        DB::transaction(function () use ($cotizacionId) {
            $cotizacion = $this->cotizacionRepositorio->buscarPorId($cotizacionId);
            $solicitudId = (int) $cotizacion->solicitud_id;

            $userId = Auth::id();
            $userIdInt = is_numeric($userId) ? (int) $userId : null;

            $this->cotizacionRepositorio->marcarGanadora($cotizacion, $solicitudId, $userIdInt);

            $this->actualizarEstados->ejecutar($solicitudId);
        });
    }
}
