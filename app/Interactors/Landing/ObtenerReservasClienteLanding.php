<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Enums\Reservas\EstadoReserva;
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
            'cliente.persona',
            'habitacion.categoria',
            'habitacion.detalle',
            'habitacion.inventarioFijo.activo.producto',
            'habitacion.servicioAsignaciones.servicio',
            'espacio',
            'servicio',
            'serviciosAdicionales',
            'cuentas.cargos',
            'cuentas.pagos',
            'estancia.cuenta.cargos',
            'estancia.cuenta.pagos',
            'detalles.reservable.habitacion.categoria',
            'detalles.reservable.espacio',
            'detalles.reservable.servicio',
            'detalles.huespedes',
        ])->orderBy('id', 'desc');

        if ($user) {
            $clienteId = $user->persona?->cliente?->id;
            $emailUser = $user->email;

            return $query->where(function (Builder $q) use ($clienteId, $emailUser): void {
                if ($clienteId !== null) {
                    $q->where('cliente_id', $clienteId);
                }
                if (is_string($emailUser) && trim($emailUser) !== '') {
                    if ($clienteId !== null) {
                        $q->orWhere('email_cliente', trim($emailUser));
                    } else {
                        $q->where('email_cliente', trim($emailUser));
                    }
                }
            });
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
            'nombre_cliente' => $this->resolverNombreCliente($r),
            'tipo_reserva' => $r->tipo_reserva->value,
            'tipo_reserva_label' => $r->tipo_reserva->getLabel(),
            'estado' => $r->estado->value,
            'estado_label' => $r->estado->getLabel(),
            'estado_color' => $r->estado->getColor(),
            'can_generar_voucher' => $this->puedeGenerarVoucher($r),
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
            'activos_habitacion' => $this->mapearActivosHabitacion($r),
            'servicios_habitacion' => $this->mapearServiciosHabitacion($r),
            'estado_cuenta' => $this->mapearEstadoCuenta($r),
            'items' => $this->mapearItems($r),
        ];
    }

    private function puedeGenerarVoucher(Reserva $reserva): bool
    {
        return ! in_array($reserva->estado, [
            EstadoReserva::CANCELADA,
            EstadoReserva::NO_SHOW,
        ], true);
    }

    private function resolverNombreCliente(Reserva $reserva): ?string
    {
        $nombreCliente = $reserva->cliente?->nombre_completo;

        if (is_string($nombreCliente) && trim($nombreCliente) !== '') {
            return $nombreCliente;
        }

        return $reserva->nombre_cliente;
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

    // -------------------------------------------------------------------------
    // Activos de la Habitación
    // -------------------------------------------------------------------------

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapearActivosHabitacion(Reserva $r): array
    {
        $habitacion = $r->habitacion;
        if ($habitacion === null) {
            return [];
        }

        return $habitacion->inventarioFijo
            ->map(function ($asignacion): ?array {
                $activo = $asignacion->activo;
                if ($activo === null) {
                    return null;
                }

                $producto = $activo->producto;
                $productoNombre = $producto !== null ? $producto->nombre : 'Equipamiento';
                $categoriaNombre = ($producto !== null && $producto->categoria !== null) ? $producto->categoria->nombre : 'Amenidades';
                $estadoLabel = $activo->estado->getLabel();

                return [
                    'id' => $activo->id,
                    'codigo' => $activo->codigo ?? "ACT-{$activo->id}",
                    'nombre' => $productoNombre,
                    'descripcion' => $activo->descripcion ?? 'Equipamiento disponible en la habitación',
                    'categoria' => $categoriaNombre,
                    'estado' => $estadoLabel,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    // -------------------------------------------------------------------------
    // Servicios Incluidos en la Habitación
    // -------------------------------------------------------------------------

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapearServiciosHabitacion(Reserva $r): array
    {
        $habitacion = $r->habitacion;
        if ($habitacion === null) {
            return [];
        }

        return $habitacion->servicioAsignaciones
            ->map(function ($asignacion): ?array {
                $servicio = $asignacion->servicio;
                if ($servicio === null) {
                    return null;
                }

                return [
                    'id' => $servicio->id,
                    'nombre' => $servicio->nombre,
                    'descripcion' => $servicio->descripcion ?? 'Servicio incluido para su estancia',
                    'incluido' => true,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    // -------------------------------------------------------------------------
    // Estado de Cuenta Financiero
    // -------------------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    private function mapearEstadoCuenta(Reserva $r): array
    {
        $cargos = [];
        $totalCargos = 0.0;
        $totalPagado = 0.0;

        // Cuentas vinculadas a la reserva o a la estancia
        $cuentas = $r->cuentas;

        foreach ($cuentas as $cuenta) {
            foreach ($cuenta->cargos as $cargo) {
                $monto = (float) $cargo->monto;
                $totalCargos += $monto;
                $cargos[] = [
                    'id' => $cargo->id,
                    'fecha' => $cargo->created_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y'),
                    'descripcion' => $cargo->concepto ?? 'Cargo de consumo',
                    'monto' => $monto,
                    'categoria' => $cargo->categoria ?? 'Hospedaje',
                ];
            }

            foreach ($cuenta->pagos as $pago) {
                $totalPagado += (float) $pago->monto;
            }
        }

        // Si no hay cargos específicos registrados en cuenta, usar tarifa base de reserva
        if ($cargos === []) {
            $montoBase = (float) $r->total;
            $cargos[] = [
                'id' => 1,
                'fecha' => $r->created_at?->format('d/m/Y') ?? now()->format('d/m/Y'),
                'descripcion' => "Estancia Base — {$r->detalles}",
                'monto' => $montoBase,
                'categoria' => 'Hospedaje',
            ];
            $totalCargos = $montoBase;
            $totalPagado = $montoBase; // Asumir pagado en confirmación de reserva
        }

        $subtotal = round($totalCargos / 1.15, 2);
        $impuestos = round($totalCargos - $subtotal, 2);
        $saldoPendiente = max(0.0, round($totalCargos - $totalPagado, 2));

        return [
            'cargos' => $cargos,
            'subtotal' => $subtotal,
            'impuestos' => $impuestos,
            'total' => $totalCargos,
            'total_pagado' => $totalPagado,
            'saldo_pendiente' => $saldoPendiente,
        ];
    }
}
