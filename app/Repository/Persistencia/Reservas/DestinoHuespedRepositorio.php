<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Reservas;

use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use DomainException;

final readonly class DestinoHuespedRepositorio
{
    public function __construct(private ReservaDetalleRepositorio $detalles) {}

    public function resolver(Reserva $reserva, ?int $detalleId): ReservaDetalle
    {
        $actual = Reserva::query()->whereKey($reserva->id)->lockForUpdate()->firstOrFail();
        $reserva->setRawAttributes($actual->getAttributes(), true);
        $reserva->unsetRelation('detalles')->unsetRelation('huespedes');
        $detalles = $reserva->detalles()->with('reservable.habitacion')->get();
        if ($detalles->isEmpty()) {
            return $this->detalles->detallePrincipalDe($reserva);
        }
        $habitaciones = $detalles->filter(fn (ReservaDetalle $d): bool => $d->reservable?->habitacion !== null);
        $destinos = $habitaciones->isNotEmpty() ? $habitaciones : $detalles;
        $detalle = $detalleId !== null ? $destinos->firstWhere('id', $detalleId) : ($destinos->count() === 1 ? $destinos->first() : null);
        if ($detalle === null) {
            throw new DomainException('Seleccione el alojamiento reservado para este huesped.');
        }

        return $detalle;
    }
}
