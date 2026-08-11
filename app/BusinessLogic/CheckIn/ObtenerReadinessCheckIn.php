<?php

declare(strict_types=1);

namespace App\BusinessLogic\CheckIn;

use App\Enums\Estancias\EstadoEstancia;
use App\Enums\HabitacionesEspacios\EstadoEspacio as EstadoHabitacion;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoHuesped;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Reservas\ReservaHuesped;
use Illuminate\Support\Collection;

/**
 * Calcula el estado de readiness del check-in para un detalle de reserva específico.
 *
 * @phpstan-type ReadinessArray array{
 *     reserva_confirmada: bool,
 *     detalle_activo: bool,
 *     habitacion_disponible: bool,
 *     habitacion_limpia: bool,
 *     sin_bloqueo_mantenimiento: bool,
 *     sin_estancia_activa: bool,
 *     titular_identificado: bool,
 *     documentacion_completa: bool,
 *     capacidad_valida: bool,
 *     puede_realizar_check_in: bool,
 *     bloqueos: array<int, string>,
 *     advertencias: array<int, string>,
 *     estado_habitacion_label: string,
 *     estado_habitacion_color: string,
 *     habitacion_numero: string,
 *     total_huespedes: int,
 *     adultos_registrados: int,
 *     ninos_registrados: int,
 *     capacidad_habitacion: int,
 * }
 */
final class ObtenerReadinessCheckIn
{
    /**
     * @return ReadinessArray
     */
    public function calcular(ReservaDetalle $detalle): array
    {
        $reserva = $detalle->reserva()->with(['huespedes', 'detalles'])->first();
        $huespedes = $detalle->huespedes()->get();
        $habitacion = $this->resolverHabitacion($detalle);

        $bloqueos = [];
        $advertencias = [];

        // ── Validación 1: Estado de la reserva ──────────────────────────────
        $reservaConfirmada = $reserva !== null && in_array(
            $reserva->estado,
            [EstadoReserva::CONFIRMADA, EstadoReserva::PARCIALMENTE_CHECKED_IN],
            true
        );
        if (! $reservaConfirmada) {
            $bloqueos[] = 'La reserva no está en estado Confirmada.';
        }

        // ── Validación 2: Estado del detalle ─────────────────────────────────
        $detalleActivo = in_array(
            $detalle->estado,
            [EstadoReservaDetalle::CONFIRMADO, EstadoReservaDetalle::PENDIENTE],
            true
        );
        if (! $detalleActivo) {
            $bloqueos[] = "El detalle de habitación está en estado: {$detalle->estado->getLabel()}.";
        }

        // ── Validación 3: Habitación disponible ──────────────────────────────
        $habitacionDisponible = $habitacion !== null && in_array(
            $habitacion->estado,
            [EstadoHabitacion::Disponible, EstadoHabitacion::Reservado],
            true
        );
        if (! $habitacionDisponible) {
            $estadoLabel = $habitacion?->estado->getLabel() ?? 'No asignada';
            $bloqueos[] = "La habitación no está disponible para check-in. Estado: {$estadoLabel}.";
        }

        // ── Validación 4: Habitación limpia (no sucia ni en limpieza) ────────
        $habitacionLimpia = $habitacion !== null && ! in_array(
            $habitacion->estado,
            [EstadoHabitacion::Sucio, EstadoHabitacion::Limpieza],
            true
        );
        if ($habitacion !== null && ! $habitacionLimpia) {
            $bloqueos[] = 'La habitación está sucia o en proceso de limpieza.';
        }

        // ── Validación 5: Sin bloqueo por mantenimiento ───────────────────────
        $sinMantenimiento = $habitacion === null || $habitacion->estado !== EstadoHabitacion::Mantenimiento;
        if (! $sinMantenimiento) {
            $bloqueos[] = 'La habitación está bloqueada por mantenimiento.';
        }

        // ── Validación 6: Sin estancia activa en ese detalle ─────────────────
        $sinEstanciaActiva = ! Estancia::query()
            ->where('reserva_detalle_id', $detalle->id)
            ->whereIn('estado', [EstadoEstancia::ACTIVA->value, EstadoEstancia::EXTENDIDA->value])
            ->exists();
        if (! $sinEstanciaActiva) {
            $bloqueos[] = 'Ya existe una estancia activa para esta habitación.';
        }

        // ── Validación 7: Titular identificado ───────────────────────────────
        $titularIdentificado = $this->tieneTitularIdentificado($huespedes, $reserva);
        if (! $titularIdentificado) {
            $advertencias[] = 'El titular no tiene documento de identificación registrado.';
        }

        // ── Validación 8: Documentación de adultos completa ──────────────────
        $documentacionCompleta = $this->documentacionAdultosCompleta($huespedes);
        if (! $documentacionCompleta) {
            $advertencias[] = 'Hay huéspedes adultos sin número de identificación.';
        }

        // ── Validación 9: Capacidad (advertencia, no bloqueo en UI) ──────────
        $capacidad = $habitacion->detalle->capacidad_adultos ?? $detalle->adultos;
        $capacidadHabitacion = $capacidad > 0 ? $capacidad : 10;
        $totalHuespedes = $huespedes->count();
        $capacidadValida = true; // no bloqueamos desde la UI — el backend revalida
        if ($habitacion !== null && $totalHuespedes > 0 && $totalHuespedes > $capacidadHabitacion) {
            $advertencias[] = "Huéspedes registrados ({$totalHuespedes}) superan la capacidad de la habitación ({$capacidadHabitacion}). El jefe de recepción debe autorizar.";
        }

        $puedeRealizarCheckIn = $bloqueos === [];

        return [
            'reserva_confirmada' => $reservaConfirmada,
            'detalle_activo' => $detalleActivo,
            'habitacion_disponible' => $habitacionDisponible,
            'habitacion_limpia' => $habitacionLimpia,
            'sin_bloqueo_mantenimiento' => $sinMantenimiento,
            'sin_estancia_activa' => $sinEstanciaActiva,
            'titular_identificado' => $titularIdentificado,
            'documentacion_completa' => $documentacionCompleta,
            'capacidad_valida' => $capacidadValida,
            'puede_realizar_check_in' => $puedeRealizarCheckIn,
            'bloqueos' => $bloqueos,
            'advertencias' => $advertencias,
            'estado_habitacion_label' => $habitacion?->estado->getLabel() ?? '—',
            'estado_habitacion_color' => $habitacion?->estado->getColor() ?? 'gray',
            'habitacion_numero' => (string) ($habitacion->numero ?? '—'),
            'total_huespedes' => $totalHuespedes,
            'adultos_registrados' => $huespedes->filter(fn ($h) => $h->tipo_huesped === TipoHuesped::ADULTO)->count(),
            'ninos_registrados' => $huespedes->filter(fn ($h) => in_array($h->tipo_huesped, [TipoHuesped::NINO, TipoHuesped::INFANTE], true))->count(),
            'capacidad_habitacion' => $capacidadHabitacion,
        ];
    }

    private function resolverHabitacion(ReservaDetalle $detalle): ?Habitacion
    {
        $recurso = $detalle->reservable;
        if ($recurso === null) {
            return null;
        }

        return Habitacion::query()
            ->where('reservable_id', $recurso->id)
            ->first();
    }

    /**
     * @param  Collection<int, ReservaHuesped>  $huespedes
     */
    private function tieneTitularIdentificado(Collection $huespedes, ?Reserva $reserva): bool
    {
        // Si hay huéspedes registrados, verificar que el titular tenga identificación
        $titular = $huespedes->first(fn ($h) => (bool) $h->es_titular);

        if ($titular !== null) {
            return is_string($titular->identificacion) && trim($titular->identificacion) !== '';
        }

        // Si no hay huéspedes registrados aún, verificar en la reserva principal
        return $reserva !== null
            && is_string($reserva->nombre_cliente)
            && trim($reserva->nombre_cliente) !== '';
    }

    /**
     * @param  Collection<int, ReservaHuesped>  $huespedes
     */
    private function documentacionAdultosCompleta(Collection $huespedes): bool
    {
        if ($huespedes->isEmpty()) {
            return true; // No se han registrado aún — la validación ocurre al confirmar
        }

        return $huespedes
            ->filter(fn ($h) => $h->tipo_huesped === TipoHuesped::ADULTO)
            ->every(fn ($h) => is_string($h->identificacion) && trim($h->identificacion) !== '');
    }
}
