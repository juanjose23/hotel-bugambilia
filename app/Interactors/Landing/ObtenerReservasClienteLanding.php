<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Repository\Models\Reservas\Reserva;
use Illuminate\Support\Facades\Auth;

final class ObtenerReservasClienteLanding
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function ejecutar(?string $emailOrCode = null): array
    {
        $user = Auth::user();

        $query = Reserva::with([
            'habitacion.categoria',
            'espacio',
            'servicio',
            'serviciosAdicionales',
            'detalles.reservable.habitacion.categoria',
            'detalles.reservable.espacio',
            'detalles.reservable.servicio',
            'detalles.huespedes',
        ])
            ->orderBy('id', 'desc');

        if ($user) {
            $query->where('cliente_id', $user->id);
        } elseif ($emailOrCode !== null && trim($emailOrCode) !== '') {
            $val = trim($emailOrCode);
            $query->where(function ($q) use ($val) {
                $q->where('codigo_reserva', $val)
                    ->orWhere('email_cliente', $val);
            });
        } else {
            return [];
        }

        /** @var array<int, array<string, mixed>> $result */
        $result = $query->get()->map(function (Reserva $r): array {
            $detalles = '';
            if ($r->habitacion) {
                $catNombre = $r->habitacion->categoria ? $r->habitacion->categoria->nombre : '';
                $detalles = "Habitación: {$r->habitacion->nombre} ({$catNombre})";
            } elseif ($r->espacio) {
                $detalles = "Restaurante: {$r->espacio->nombre}";
            } elseif ($r->servicio) {
                $detalles = "Servicio: {$r->servicio->nombre}";
            }

            return [
                'id' => $r->id,
                'codigo_reserva' => $r->codigo_reserva,
                'nombre_cliente' => $r->nombre_cliente,
                'tipo_reserva' => $r->tipo_reserva->value,
                'tipo_reserva_label' => $r->tipo_reserva->getLabel(),
                'estado' => $r->estado->value,
                'estado_label' => $r->estado->getLabel(),
                'estado_color' => $r->estado->getColor(),
                'fecha_check_in' => $r->fecha_check_in->format('Y-m-d'),
                'fecha_check_out' => $r->fecha_check_out?->format('Y-m-d'),
                'hora_reserva' => $r->hora_reserva,
                'adultos' => $r->adultos,
                'ninos' => $r->ninos,
                'acompanantes' => $r->acompanantes ?? [],
                'total' => (float) $r->total,
                'detalles' => $detalles,
                'notas' => $r->notas,
                'servicios_adicionales' => $r->serviciosAdicionales->map(fn ($s) => [
                    'id' => $s->id,
                    'nombre' => $s->nombre,
                    'cantidad' => $s->pivot->cantidad ?? 1,
                    'precio' => (float) ($s->pivot->precio ?? 0),
                ])->toArray(),
                'items' => $r->detalles->map(function ($detalle): ?array {
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
                        'huespedes' => $detalle->huespedes->map(fn ($huesped): array => [
                            'id' => $huesped->id,
                            'nombre' => $huesped->nombre,
                            'identificacion' => $huesped->identificacion,
                            'tipo_huesped' => $huesped->tipo_huesped->value,
                            'es_titular' => $huesped->es_titular,
                        ])->all(),
                    ];
                })->filter()->values()->all(),
            ];
        })->values()->all();

        return $result;
    }
}
