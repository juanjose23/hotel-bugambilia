<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Cotizaciones;

use App\Repository\Persistencia\Compras\CotizacionRepositorioInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final readonly class SeleccionarItemGanador
{
    public function __construct(
        private ActualizarEstadosCotizacionesSolicitud $actualizarEstados,
        private CotizacionRepositorioInterface $cotizacionRepositorio,
    ) {}

    public function ejecutar(int $cotizacionId, int $productoId): void
    {
        DB::transaction(function () use ($cotizacionId, $productoId) {
            $cotizacion = $this->cotizacionRepositorio->buscarPorId($cotizacionId);
            $solicitudId = (int) $cotizacion->solicitud_id;

            $userId = Auth::id();
            $userIdInt = is_numeric($userId) ? (int) $userId : null;

            $this->cotizacionRepositorio->seleccionarItemGanador(
                $cotizacion,
                $solicitudId,
                $productoId,
                $userIdInt,
            );

            $this->actualizarEstados->ejecutar($solicitudId);
        });
    }
}
