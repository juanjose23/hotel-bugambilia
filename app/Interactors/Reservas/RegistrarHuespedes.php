<?php

declare(strict_types=1);

namespace App\Interactors\Reservas;

use App\BusinessLogic\Huespedes\ValidarHuespedes;
use App\Enums\Reservas\EstadoReserva;
use App\Events\Reservas\HuespedModificado;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Reservas\ReservaHuesped;
use Illuminate\Support\Facades\DB;

final class RegistrarHuespedes
{
    public function __construct(
        private readonly ValidarHuespedes $validar,
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     */
    public function agregar(Reserva $reserva, array $datos): ReservaHuesped
    {
        $this->validar->validarCreacion($reserva, $datos);

        return DB::transaction(function () use ($reserva, $datos): ReservaHuesped {
            $detalle = $this->obtenerDetallePrincipal($reserva);

            $huesped = $detalle->huespedes()->create([
                'nombre' => $datos['nombre'],
                'tipo_identificacion' => $datos['tipo_identificacion'] ?? null,
                'identificacion' => $datos['identificacion'] ?? null,
                'tipo_huesped' => $datos['tipo_huesped'],
                'es_titular' => $datos['es_titular'] ?? false,
                'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
                'telefono' => $datos['telefono'] ?? null,
                'email' => $datos['email'] ?? null,
            ]);

            HuespedModificado::dispatch($huesped, 'creado');

            return $huesped;
        });
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizar(Reserva $reserva, ReservaHuesped $huesped, array $datos): ReservaHuesped
    {
        $this->validar->validarActualizacion($reserva, $datos, $huesped);

        return DB::transaction(function () use ($huesped, $datos): ReservaHuesped {
            $datosAnteriores = $huesped->toArray();

            $huesped->update([
                'nombre' => $datos['nombre'] ?? $huesped->nombre,
                'tipo_identificacion' => $datos['tipo_identificacion'] ?? $huesped->tipo_identificacion,
                'identificacion' => $datos['identificacion'] ?? $huesped->identificacion,
                'tipo_huesped' => $datos['tipo_huesped'] ?? $huesped->tipo_huesped,
                'es_titular' => $datos['es_titular'] ?? $huesped->es_titular,
                'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? $huesped->fecha_nacimiento,
                'telefono' => $datos['telefono'] ?? $huesped->telefono,
                'email' => $datos['email'] ?? $huesped->email,
            ]);

            /** @var ReservaHuesped $huespedActualizado */
            $huespedActualizado = $huesped->fresh();
            HuespedModificado::dispatch($huespedActualizado, 'actualizado', $datosAnteriores);

            return $huesped;
        });
    }

    public function eliminar(Reserva $reserva, ReservaHuesped $huesped): void
    {
        if ($reserva->estado->value > EstadoReserva::CONFIRMADA->value) {
            throw new \DomainException('No se pueden eliminar huéspedes de una reserva que ya pasó el check-in.');
        }

        if ($huesped->es_titular) {
            throw new \DomainException('No se puede eliminar el huésped titular. Asigne otro titular primero.');
        }

        DB::transaction(function () use ($huesped): void {
            HuespedModificado::dispatch($huesped, 'eliminado');
            $huesped->delete();
        });
    }

    private function obtenerDetallePrincipal(Reserva $reserva): ReservaDetalle
    {
        $detalle = $reserva->detalles()->whereNull('parent_id')->first();

        if ($detalle === null) {
            $recurso = $reserva->habitacion->reservable
                ?? $reserva->espacio?->reservable;

            $reservableId = $recurso instanceof RecursoReservable
                ? $recurso->id
                : 0;

            $detalle = $reserva->detalles()->create([
                'reservable_id' => $reservableId,
                'fecha_inicio' => $reserva->fecha_check_in,
                'fecha_fin' => $reserva->fecha_check_out,
                'precio_unitario' => 0,
                'subtotal' => 0,
            ]);
        }

        return $detalle;
    }
}
