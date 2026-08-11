<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

final class ObtenerReservasClienteLanding
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function ejecutar(?string $emailOrCode = null): array
    {
        $query = $this->construirQuery($emailOrCode);

        if ($query === null) {
            return [];
        }

        return $query->get()
            ->map(fn (Reserva $r): array => $this->reservaToArray($r))
            ->values()
            ->all();
    }

    // -------------------------------------------------------------------------
    // Query
    // -------------------------------------------------------------------------

    /**
     * @return ?Builder<Reserva>
     */
    private function construirQuery(?string $emailOrCode): ?Builder
    {
        $user = Auth::user();
        /** @var Builder<Reserva> $query */
        $query = Reserva::with([
            'habitacion.categoria',
            'espacio',
            'servicio',
            'serviciosAdicionales',
            'detalles.reservable.habitacion.categoria',
            'detalles.reservable.espacio',
            'detalles.reservable.servicio',
            'detalles.huespedes',
        ])->orderBy('id', 'desc');

        if ($user) {
            $clienteId = $user->persona?->cliente?->id;

            if ($clienteId === null) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where('cliente_id', $clienteId);
        }

        if ($emailOrCode !== null && trim($emailOrCode) !== '') {
            $val = trim($emailOrCode);

            return $query->where(fn (Builder $q) => $q
                ->where('codigo_reserva', $val)
                ->orWhere('email_cliente', $val));
        }

        return null;
    }

    // -------------------------------------------------------------------------
    // Reserva
    // -------------------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    private function reservaToArray(Reserva $r): array
    {
        return [
            'id' => $r->id,
            'codigo_reserva' => $r->codigo_reserva,
            'nombre_cliente' => $r->nombre_cliente,
            'tipo_reserva' => $r->tipo_reserva->value,
            'tipo_reserva_label' => $r->tipo_reserva->getLabel(),
            'estado' => $r->estado->value,
            'estado_label' => $r->estado->getLabel(),
            'estado_color' => $r->estado->getColor(),
            'fecha_check_in' => $r->fecha_check_in?->format('Y-m-d'),
            'fecha_check_out' => $r->fecha_check_out?->format('Y-m-d'),
            'hora_reserva' => $r->hora_reserva,
            'adultos' => $r->adultos,
            'ninos' => $r->ninos,
            'acompanantes' => $r->acompanantes ?? [],
            'total' => (float) $r->total,
            'detalles' => $this->resolverDetallesTexto($r),
            'notas' => $r->notas,
            'servicios_adicionales' => $this->mapearServiciosAdicionales($r),
            'items' => $this->mapearItems($r),
        ];
    }

    private function resolverDetallesTexto(Reserva $r): string
    {
        if ($r->habitacion) {
            $catNombre = $r->habitacion->categoria->nombre ?? '';

            return "Habitación: {$r->habitacion->nombre} ({$catNombre})";
        }

        if ($r->espacio) {
            return "Restaurante: {$r->espacio->nombre}";
        }

        if ($r->servicio) {
            return "Servicio: {$r->servicio->nombre}";
        }

        return '';
    }

    // -------------------------------------------------------------------------
    // Servicios adicionales
    // -------------------------------------------------------------------------

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapearServiciosAdicionales(Reserva $r): array
    {
        return $r->serviciosAdicionales
            ->map(fn ($s): array => [
                'id' => $s->id,
                'nombre' => $s->nombre,
                'cantidad' => $s->pivot->cantidad ?? 1,
                'precio' => (float) ($s->pivot->precio ?? 0),
            ])
            ->values()
            ->all();
    }

    // -------------------------------------------------------------------------
    // Detalles / Items
    // -------------------------------------------------------------------------

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapearItems(Reserva $r): array
    {
        return $r->detalles
            ->map(fn (ReservaDetalle $detalle): ?array => $this->detalleToArray($detalle))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function detalleToArray(ReservaDetalle $detalle): ?array
    {
        $recurso = $detalle->reservable;

        if ($recurso === null) {
            return null;
        }

        return [
            'id' => $detalle->id,
            'reservable_id' => $detalle->reservable_id,
            'tipo' => $recurso->tipo->value,
            'tipo_label' => $recurso->tipo->getLabel(),
            'nombre' => $recurso->nombre,
            'estado' => $detalle->estado->value,
            'estado_label' => $detalle->estado->getLabel(),
            'fecha_inicio' => $detalle->fecha_inicio->format('Y-m-d H:i:s'),
            'fecha_fin' => $detalle->fecha_fin?->format('Y-m-d H:i:s'),
            'cantidad' => $detalle->cantidad,
            'adultos' => $detalle->adultos,
            'ninos' => $detalle->ninos,
            'subtotal' => (float) $detalle->subtotal,
            'huespedes' => $this->mapearHuespedes($detalle),
        ];
    }

    // -------------------------------------------------------------------------
    // Huéspedes
    // -------------------------------------------------------------------------

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapearHuespedes(ReservaDetalle $detalle): array
    {
        return $detalle->huespedes
            ->map(fn ($huesped): array => [
                'id' => $huesped->id,
                'nombre' => $huesped->nombre,
                'identificacion' => $huesped->identificacion,
                'tipo_huesped' => $huesped->tipo_huesped->value,
                'es_titular' => $huesped->es_titular,
            ])
            ->all();
    }
}
