<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Reservas;

use App\Enums\Estancias\EstadoEstancia;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;

final readonly class EstanciaReservaRepositorio
{
    public function estanciaConLock(int $estanciaId): Estancia
    {
        /** @var Estancia $estancia */
        $estancia = Estancia::query()->where('id', $estanciaId)->lockForUpdate()->firstOrFail();

        return $estancia;
    }

    public function existeEstanciaActivaParaDetalle(int $detalleId): bool
    {
        return Estancia::query()
            ->where('reserva_detalle_id', $detalleId)
            ->where('estado', EstadoEstancia::ACTIVA)
            ->exists();
    }

    public function tieneEstanciaActiva(Reserva $reserva): bool
    {
        return $reserva->estancias()
            ->where('estado', EstadoEstancia::ACTIVA)
            ->exists();
    }

    /** @param array<string, mixed> $datos */
    public function crearEstancia(array $datos): Estancia
    {
        return Estancia::query()->create($datos);
    }

    public function estanciaActivaDeReserva(Reserva $reserva): Estancia
    {
        /** @var Estancia $estancia */
        $estancia = Estancia::query()
            ->with('cuenta')
            ->whereBelongsTo($reserva)
            ->lockForUpdate()
            ->firstOrFail();

        return $estancia;
    }

    /** @param array<string, mixed> $datos */
    public function actualizarEstancia(Estancia $estancia, array $datos): Estancia
    {
        $estancia->update($datos);

        return $estancia->refresh();
    }

    public function obtenerReservaDeEstanciaConLock(Estancia $estancia): Reserva
    {
        /** @var Reserva $reserva */
        $reserva = Reserva::query()->where('id', $estancia->reserva_id)->lockForUpdate()->firstOrFail();

        return $reserva;
    }

    public function obtenerDetalleDeEstanciaConLock(Estancia $estancia): ?ReservaDetalle
    {
        /** @var ReservaDetalle|null $detalle */
        $detalle = ReservaDetalle::query()
            ->where('id', $estancia->reserva_detalle_id)
            ->lockForUpdate()
            ->first();

        return $detalle;
    }
}
