<?php

declare(strict_types=1);

namespace App\Interactors\Publico;

use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Servicios\Servicio;

final class ObtenerPagoPublico
{
    /**
     * @return array{datosReserva: null, serviciosExtras: array<int, array{id: string, nombre: string, descripcion: string, precio: float, moneda: string}>}
     */
    public function sinReserva(): array
    {
        return [
            'datosReserva' => null,
            'serviciosExtras' => $this->serviciosExtras(),
        ];
    }

    /**
     * @return array{datosReserva: array<string, mixed>, serviciosExtras: array<int, array{id: string, nombre: string, descripcion: string, precio: float, moneda: string}>}
     */
    public function paraReserva(Reserva $reserva): array
    {
        $reserva->loadMissing(['habitacion.ubicacion', 'espacio.ubicacion', 'servicio', 'moneda']);

        $recurso = $reserva->habitacion ?? $reserva->espacio ?? $reserva->servicio;
        $nombreRecurso = data_get($recurso, 'nombre') ?? 'Reserva';
        $nombreUbicacion = data_get($reserva->habitacion, 'ubicacion.nombre')
            ?? data_get($reserva->espacio, 'ubicacion.nombre');

        return [
            'datosReserva' => [
                'id' => $reserva->id,
                'codigoReserva' => $reserva->codigo_reserva,
                'habitacion' => $nombreRecurso,
                'ubicacion' => $nombreUbicacion ?? 'Hotel Bugambilias',
                'imagen' => '/images/main-room.webp',
                'calificacion' => 4.9,
                'fechaEntrada' => $reserva->fecha_check_in?->toDateString() ?? '',
                'fechaSalida' => $reserva->fecha_check_out?->toDateString() ?? '',
                'noches' => $this->calcularNoches($reserva),
                'huespedes' => (int) $reserva->adultos + (int) $reserva->ninos,
                'precioHabitacion' => (float) $reserva->subtotal,
                'impuestos' => round((float) $reserva->total - (float) $reserva->subtotal + (float) $reserva->descuento, 2),
                'tarifaServicio' => 0,
                'total' => (float) $reserva->total,
                'monedaCodigo' => $reserva->moneda?->codigo,
            ],
            'serviciosExtras' => $this->serviciosExtras(),
        ];
    }

    /**
     * @return array<int, array{id: string, nombre: string, descripcion: string, precio: float, moneda: string}>
     */
    private function serviciosExtras(): array
    {
        return Servicio::query()
            ->activos()
            ->where('web', true)
            ->with(['precios.moneda'])
            ->get()
            ->map(function (Servicio $servicio): array {
                $precio = $servicio->precios->first();

                return [
                    'id' => (string) $servicio->id,
                    'nombre' => (string) $servicio->nombre,
                    'descripcion' => (string) ($servicio->descripcion ?? ''),
                    'precio' => $precio ? (float) $precio->precio : 0.0,
                    'moneda' => $precio?->moneda->simbolo ?? '$',
                ];
            })
            ->values()
            ->all();
    }

    private function calcularNoches(Reserva $reserva): int
    {
        if ($reserva->fecha_check_in === null || $reserva->fecha_check_out === null) {
            return 0;
        }

        return (int) max(0, $reserva->fecha_check_in->diffInDays($reserva->fecha_check_out));
    }
}
