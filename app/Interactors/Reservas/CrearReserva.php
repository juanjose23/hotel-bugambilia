<?php

declare(strict_types=1);

namespace App\Interactors\Reservas;

use App\BusinessLogic\Reservas\ValidarDisponibilidadHabitacion;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Reservas\Reserva;
use DateTimeImmutable;
use InvalidArgumentException;

final class CrearReserva
{
    public function __construct(
        private readonly ValidarDisponibilidadHabitacion $validarDisponibilidad,
        private readonly GenerarCodigoReserva $generarCodigo
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<int, array{servicio_id: int, cantidad?: int, precio?: float}>  $serviciosAdicionales
     */
    public function ejecutar(array $datos, array $serviciosAdicionales = []): Reserva
    {
        $tipoStr = is_string($datos['tipo_reserva'] ?? null) ? $datos['tipo_reserva'] : 'habitacion';
        $tipo = TipoReserva::from($tipoStr);

        $fechaCheckInStr = is_string($datos['fecha_check_in'] ?? null) ? $datos['fecha_check_in'] : 'now';
        $checkIn = new DateTimeImmutable($fechaCheckInStr);
        $checkOut = ! empty($datos['fecha_check_out']) && is_string($datos['fecha_check_out'])
            ? new DateTimeImmutable($datos['fecha_check_out'])
            : null;

        // Si es reserva de habitación y tiene habitación asignada, validar disponibilidad
        if ($tipo === TipoReserva::HABITACION && ! empty($datos['habitacion_id'])) {
            $habitacionId = (int) (is_numeric($datos['habitacion_id']) ? $datos['habitacion_id'] : 0);
            $fechaSalidaValida = $checkOut ?? $checkIn->modify('+1 day');

            if (! $this->validarDisponibilidad->estaDisponible($habitacionId, $checkIn, $fechaSalidaValida)) {
                throw new InvalidArgumentException('La habitación seleccionada no se encuentra disponible en las fechas especificadas.');
            }
        }

        $codigo = $this->generarCodigo->ejecutar();

        /** @var Reserva $reserva */
        $reserva = Reserva::create([
            'codigo_reserva' => $codigo,
            'cliente_id' => $datos['cliente_id'] ?? null,
            'nombre_cliente' => is_string($datos['nombre_cliente'] ?? null) ? $datos['nombre_cliente'] : 'Huésped General',
            'telefono_cliente' => $datos['telefono_cliente'] ?? null,
            'email_cliente' => $datos['email_cliente'] ?? null,
            'tipo_reserva' => $tipo->value,
            'habitacion_id' => $datos['habitacion_id'] ?? null,
            'espacio_id' => $datos['espacio_id'] ?? null,
            'servicio_id' => $datos['servicio_id'] ?? null,
            'fecha_check_in' => $checkIn->format('Y-m-d'),
            'fecha_check_out' => $checkOut?->format('Y-m-d'),
            'hora_reserva' => $datos['hora_reserva'] ?? null,
            'adultos' => (int) (is_numeric($datos['adultos'] ?? null) ? $datos['adultos'] : 1),
            'ninos' => (int) (is_numeric($datos['ninos'] ?? null) ? $datos['ninos'] : 0),
            'acompanantes' => $datos['acompanantes'] ?? null,
            'estado' => $datos['estado'] ?? EstadoReserva::PENDIENTE->value,
            'total' => (float) (is_numeric($datos['total'] ?? null) ? $datos['total'] : 0.00),
            'notas' => $datos['notas'] ?? null,
        ]);

        // Adjuntar servicios adicionales opcionales
        foreach ($serviciosAdicionales as $srv) {
            if (! empty($srv['servicio_id'])) {
                $reserva->serviciosAdicionales()->attach($srv['servicio_id'], [
                    'cantidad' => $srv['cantidad'] ?? 1,
                    'precio' => $srv['precio'] ?? 0.00,
                ]);
            }
        }

        return $reserva;
    }
}
