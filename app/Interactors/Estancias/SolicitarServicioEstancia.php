<?php

declare(strict_types=1);

namespace App\Interactors\Estancias;

use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\OrigenReservaDetalle;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Reservas\Operaciones\SincronizarCuentaReserva;
use App\Repository\Models\Cuentas\CuentaDetalle;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Shared\Precio;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use DomainException;
use Illuminate\Support\Carbon;

final class SolicitarServicioEstancia
{
    public function __construct(
        private readonly CuentaRepositorioInterface $cuentas,
        private readonly SincronizarCuentaReserva $sincronizarCuentaReserva,
    ) {}

    /**
     * Solicita un servicio o consumo durante una estancia activa.
     *
     * @param array{
     *     estancia_id: int,
     *     reservable_id: int,
     *     fecha_inicio?: string|Carbon|null,
     *     fecha_fin?: string|Carbon|null,
     *     cantidad?: int,
     *     adultos?: int,
     *     notas?: string|null,
     *     origen?: OrigenReservaDetalle|int|null,
     *     usuario_id?: int|null,
     * } $datos
     * @return ReservaDetalle|array{tipo: string, detalle: CuentaDetalle}
     */
    public function ejecutar(array $datos): ReservaDetalle|array
    {
        /** @var Estancia $estancia */
        $estancia = Estancia::query()
            ->with(['reserva', 'cuenta', 'reservaDetalle'])
            ->findOrFail((int) $datos['estancia_id']);

        /** @var RecursoReservable $recurso */
        $recurso = RecursoReservable::query()->findOrFail((int) $datos['reservable_id']);

        $origenVal = $datos['origen'] ?? OrigenReservaDetalle::HUESPED;
        $origen = $origenVal instanceof OrigenReservaDetalle
            ? $origenVal
            : (OrigenReservaDetalle::tryFrom((int) $origenVal) ?? OrigenReservaDetalle::HUESPED);

        $cantidad = max(1, (int) ($datos['cantidad'] ?? 1));
        $precioBruto = Precio::query()
            ->where('priceable_type', RecursoReservable::class)
            ->where('priceable_id', $recurso->id)
            ->value('precio')
            ?? $recurso->habitacion?->precios()->first()->precio
            ?? $recurso->espacio?->precios()->first()->precio
            ?? $recurso->servicio?->precios()->first()->precio
            ?? 0.0;
        $precioUnitario = is_numeric($precioBruto) ? (float) $precioBruto : 0.0;

        // Si el recurso no requiere bloqueo de agenda, es un consumo directo a la cuenta
        if ($recurso->control_disponibilidad === ControlDisponibilidad::SIN_BLOQUEO) {
            $cuenta = $estancia->cuenta ?? $estancia->reserva?->cuentas()->where('estado', 2)->latest('id')->first();

            if ($cuenta === null) {
                throw new DomainException('No hay una cuenta activa asignada a la estancia.');
            }

            $subtotal = round($precioUnitario * $cantidad, 2);

            $detalle = $this->cuentas->crearDetalle($cuenta, [
                'moneda_id' => $cuenta->moneda_id,
                'origen_type' => RecursoReservable::class,
                'origen_id' => $recurso->id,
                'espacio_id' => $estancia->getAttribute('espacio_id'),
                'concepto' => $recurso->nombre,
                'descripcion' => "Consumo directo en estancia #{$estancia->id}",
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'estado' => EstadoGeneral::Activo->value,
                'creador_id' => $datos['usuario_id'] ?? null,
            ]);

            return ['tipo' => 'consumo_cuenta', 'detalle' => $detalle];
        }

        // Para recursos que requieren horario/cupos/fechas, se registra un ReservaDetalle agendado
        $fechaInicio = isset($datos['fecha_inicio']) ? Carbon::parse($datos['fecha_inicio']) : now();
        $fechaFin = isset($datos['fecha_fin'])
            ? Carbon::parse($datos['fecha_fin'])
            : ($recurso->duracion_minutos > 0 ? (clone $fechaInicio)->addMinutes((int) $recurso->duracion_minutos) : (clone $fechaInicio)->addHours(1));

        $subtotal = round($precioUnitario * $cantidad, 2);

        /** @var ReservaDetalle $reservaDetalle */
        $reservaDetalle = ReservaDetalle::query()->create([
            'reserva_id' => $estancia->reserva_id,
            'reservable_id' => $recurso->id,
            'parent_id' => $estancia->reserva_detalle_id,
            'estancia_id' => $estancia->id,
            'origen' => $origen,
            'estado' => 2, // Confirmado
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'cantidad' => $cantidad,
            'adultos' => (int) ($datos['adultos'] ?? 1),
            'precio_unitario' => $precioUnitario,
            'subtotal' => $subtotal,
            'notas' => $datos['notas'] ?? null,
        ]);

        if ($estancia->reserva !== null) {
            $cuenta = $estancia->cuenta ?? $estancia->reserva->cuentas()->where('estado', 2)->latest('id')->first();
            if ($cuenta !== null) {
                $this->sincronizarCuentaReserva->ejecutar($estancia->reserva->refresh(), $cuenta, $datos['usuario_id'] ?? null);
            }
        }

        return $reservaDetalle;
    }
}
