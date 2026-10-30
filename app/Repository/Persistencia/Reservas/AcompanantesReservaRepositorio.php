<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Reservas;

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoHuesped;
use App\Repository\Models\Reservas\Reserva;
use DomainException;

final readonly class AcompanantesReservaRepositorio
{
    public function __construct(private ReservaDetalleRepositorio $detalles) {}

    public function bloquear(Reserva $reserva): Reserva
    {
        return Reserva::query()->whereKey($reserva->id)->lockForUpdate()->firstOrFail();
    }

    /** @param array<int, mixed> $personas */
    public function reemplazar(Reserva $reserva, array $personas): void
    {
        $reserva = Reserva::query()->whereKey($reserva->id)->lockForUpdate()->firstOrFail();
        if (! in_array($reserva->estado, [EstadoReserva::PENDIENTE, EstadoReserva::CONFIRMADA], true)) {
            throw new DomainException('Los acompanantes se modifican antes del check-in.');
        }
        if (! $reserva->detalles()->exists()) {
            $this->detalles->detallePrincipalDe($reserva);
        }
        $detalles = $reserva->detalles()->with('reservable.habitacion.detalle')->get()->keyBy('id');
        $habitaciones = $detalles->filter(fn ($d): bool => $d->reservable?->habitacion !== null);
        $destinos = $habitaciones->isNotEmpty() ? $habitaciones : $detalles;
        $existentes = $reserva->huespedes()->where('es_titular', false)->lockForUpdate()->get()->keyBy('id');
        $conservados = [];
        $documentos = [];
        foreach ($personas as $persona) {
            if (! is_array($persona) || ! is_string($persona['nombre'] ?? null) || trim($persona['nombre']) === '') {
                throw new DomainException('Indique el nombre de cada acompanante.');
            }
            $id = is_numeric($persona['huesped_id'] ?? null) ? (int) $persona['huesped_id'] : null;
            $actual = $id !== null ? $existentes->get($id) : null;
            if ($id !== null && $actual === null) {
                throw new DomainException('El acompanante no pertenece a esta reserva o es el titular.');
            }
            $detalleId = is_numeric($persona['reserva_detalle_id'] ?? null) ? (int) $persona['reserva_detalle_id'] : $actual?->reserva_detalle_id;
            $detalleId ??= $destinos->count() === 1 ? $destinos->first()?->id : null;
            $detalle = $detalleId !== null ? $destinos->get($detalleId) : null;
            if ($detalle === null) {
                throw new DomainException('Seleccione la habitacion reservada para cada acompanante.');
            }
            $tipo = match ($persona['tipo'] ?? 'adulto') {
                'nino', 'niño' => TipoHuesped::NINO,
                'bebe', 'infante' => TipoHuesped::INFANTE,
                'adulto' => TipoHuesped::ADULTO,
                default => throw new DomainException('Tipo de acompanante no valido.'),
            };
            $documento = is_string($persona['identificacion'] ?? null) ? trim($persona['identificacion']) : null;
            if ($documento !== null && $documento !== '') {
                if (isset($documentos[$documento]) || $reserva->huespedes()->where('es_titular', true)->where('identificacion', $documento)->exists()) {
                    throw new DomainException('No repita el documento de un huesped en la reserva.');
                }
                $documentos[$documento] = true;
            }
            if ($actual === null) {
                $coincidentes = $existentes->filter(fn ($h): bool => $h->nombre === trim($persona['nombre']) && $h->identificacion === $documento);
                $actual = $coincidentes->count() === 1 ? $coincidentes->first() : null;
            }
            if ($actual !== null && in_array($actual->id, $conservados, true)) {
                throw new DomainException('El mismo acompanante aparece mas de una vez.');
            }
            $datos = ['reserva_detalle_id' => $detalle->id, 'nombre' => trim($persona['nombre']), 'identificacion' => $documento, 'tipo_huesped' => $tipo];
            if ($actual !== null) {
                $actual->update($datos);
                $conservados[] = $actual->id;
            } else {
                $nuevo = $detalle->huespedes()->create($datos + ['es_titular' => false]);
                $conservados[] = $nuevo->id;
            }
        }
        foreach ($existentes as $huesped) {
            if (! in_array($huesped->id, $conservados, true)) {
                $huesped->delete();
            }
        }
        foreach ($destinos as $detalle) {
            $capacidad = $detalle->reservable?->habitacion?->detalle;
            if ($capacidad === null) {
                continue;
            }
            $huespedes = $detalle->huespedes()->get();
            if ($huespedes->where('tipo_huesped', TipoHuesped::ADULTO)->count() > $capacidad->capacidad_adultos
                || $huespedes->where('tipo_huesped', TipoHuesped::NINO)->count() > $capacidad->capacidad_ninos) {
                throw new DomainException('Los acompanantes superan la capacidad de la habitacion seleccionada.');
            }
        }
        $reserva->unsetRelation('huespedes');
    }
}
