<?php

declare(strict_types=1);

namespace App\Interactors\Reservas;

use App\BusinessLogic\Reservas\AplicarPromocionReserva;
use App\BusinessLogic\Reservas\CalcularPeriodoReserva;
use App\BusinessLogic\Reservas\CalcularTotalReserva;
use App\BusinessLogic\Reservas\CalcularUnidadesReserva;
use App\BusinessLogic\Reservas\ValidarDisponibilidadHabitacion;
use App\BusinessLogic\Reservas\ValidarSeleccionAdicionales;
use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoReserva;
use App\Events\Reservas\ReservaCreada;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Queries\Reservas\DisponibilidadRecursoQuery;
use App\Repository\Queries\Reservas\ObtenerPromocionReservaQuery;
use App\Repository\Queries\Reservas\ObtenerTarifasReservaQuery;
use App\Repository\Queries\Reservas\ReservaDisponibilidadQuery;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class CrearReserva
{
    public function __construct(
        private readonly ValidarDisponibilidadHabitacion $validarDisponibilidad,
        private readonly CalcularTotalReserva $calcularTotal,
        private readonly ReservaDisponibilidadQuery $disponibilidad,
        private readonly ObtenerTarifasReservaQuery $tarifas,
        private readonly ReservaRepositorioInterface $reservas,
        private readonly GenerarCodigoReserva $generarCodigo,
        private readonly DisponibilidadRecursoQuery $disponibilidadRecursos,
        private readonly ObtenerPromocionReservaQuery $promociones,
        private readonly AplicarPromocionReserva $aplicarPromocion,
        private readonly CalcularPeriodoReserva $calcularPeriodo,
        private readonly CalcularUnidadesReserva $calcularUnidades,
        private readonly ValidarSeleccionAdicionales $validarAdicionales,
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<int, mixed>  $serviciosAdicionales
     * @param  array<int, mixed>  $espaciosAdicionales
     */
    public function ejecutar(array $datos, array $serviciosAdicionales = [], array $espaciosAdicionales = []): Reserva
    {
        return DB::transaction(function () use ($datos, $serviciosAdicionales, $espaciosAdicionales): Reserva {
            $tipoValor = $datos['tipo_reserva'] ?? null;
            $checkInValor = $datos['fecha_check_in'] ?? null;
            $checkOutValor = $datos['fecha_check_out'] ?? null;

            if (! is_string($tipoValor) || ! is_string($checkInValor)) {
                throw new InvalidArgumentException('Los datos de la reserva no son válidos.');
            }

            $tipo = TipoReserva::from($tipoValor);
            $checkIn = new DateTimeImmutable($checkInValor);
            $checkOut = is_string($checkOutValor) ? new DateTimeImmutable($checkOutValor) : null;
            $entidadPrincipalId = $this->idEntidadPrincipal($tipo, $datos);

            $precioPrincipal = $this->obtenerPrecioPrincipal($tipo, $datos, $checkIn, $checkOut);
            $servicios = $this->validarAdicionales->resolverServicios(
                $serviciosAdicionales,
                $tipo === TipoReserva::SERVICIO ? $entidadPrincipalId : null,
            );
            $espacios = $this->validarAdicionales->resolverEspacios(
                $espaciosAdicionales,
                $tipo === TipoReserva::RESTAURANTE ? $entidadPrincipalId : null,
            );

            $recursoPrincipal = $this->reservas->resolverRecurso(
                $tipo,
                $entidadPrincipalId,
            );
            [$inicio, $fin] = $this->calcularPeriodo->calcular($checkIn, $checkOut, $datos, $recursoPrincipal->duracion_minutos);

            $esPorHora = $tipo === TipoReserva::RESTAURANTE && $this->tarifas->espacioEsPorHora($entidadPrincipalId);
            $unidades = $this->calcularUnidades->calcular($tipo, $checkIn, $checkOut, $esPorHora, $inicio, $fin);

            $subtotal = $this->calcularTotal->calcular($precioPrincipal, $unidades, [...$servicios, ...$espacios]);
            $promocionId = is_numeric($datos['promocion_id'] ?? null) ? (int) $datos['promocion_id'] : null;
            $promocion = $promocionId !== null ? $this->promociones->vigente($promocionId) : null;
            $totales = $this->aplicarPromocion->calcular(
                $subtotal,
                $promocion?->descuento_porcentaje !== null ? (float) $promocion->descuento_porcentaje : null,
                $promocion?->descuento_monto !== null ? (float) $promocion->descuento_monto : null,
            );

            $reserva = $this->reservas->crear([
                'codigo_reserva' => $this->generarCodigo->ejecutar(),
                'cliente_id' => $datos['cliente_id'] ?? null,
                'nombre_cliente' => $datos['nombre_cliente'],
                'telefono_cliente' => $datos['telefono_cliente'] ?? null,
                'email_cliente' => $datos['email_cliente'] ?? null,
                'tipo_reserva' => $tipo,
                'habitacion_id' => $datos['habitacion_id'] ?? null,
                'espacio_id' => $datos['espacio_id'] ?? null,
                'servicio_id' => $datos['servicio_id'] ?? null,
                'promocion_id' => $promocion?->id,
                'fecha_check_in' => $checkIn->format('Y-m-d'),
                'fecha_check_out' => $checkOut?->format('Y-m-d'),
                'hora_reserva' => $datos['hora_reserva'] ?? null,
                'adultos' => $datos['adultos'] ?? 1,
                'ninos' => $datos['ninos'] ?? 0,
                'solicita_cuenta' => (bool) ($datos['solicita_cuenta'] ?? false),
                'limite_cuenta_solicitado' => is_numeric($datos['limite_cuenta_solicitado'] ?? null)
                    ? (float) $datos['limite_cuenta_solicitado']
                    : null,
                'acompanantes' => $datos['acompanantes'] ?? null,
                'estado' => EstadoReserva::PENDIENTE,
                'subtotal' => $totales['subtotal'],
                'descuento' => $totales['descuento'],
                'total' => $totales['total'],
                'notas' => $datos['notas'] ?? null,
            ]);

            $this->reservas->adjuntarServicios($reserva, $servicios);

            $this->disponibilidadRecursos->bloquear($recursoPrincipal->id);

            if ($recursoPrincipal->control_disponibilidad !== ControlDisponibilidad::SIN_BLOQUEO
                && $this->disponibilidadRecursos->existeConflicto($recursoPrincipal->id, $inicio, $fin)) {
                throw new InvalidArgumentException('El recurso seleccionado ya no está disponible en el periodo solicitado.');
            }

            $detallePrincipal = $this->reservas->crearDetalle($reserva, $recursoPrincipal, [
                'estado' => EstadoReservaDetalle::PENDIENTE,
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
                'cantidad' => 1,
                'adultos' => $this->enteroOpcional($datos, 'adultos', 1),
                'ninos' => $this->enteroOpcional($datos, 'ninos', 0),
                'precio_unitario' => $precioPrincipal,
                'subtotal' => round($precioPrincipal * $unidades, 2),
                'notas' => $datos['notas'] ?? null,
            ]);
            $huespedes = $datos['acompanantes'] ?? [];
            $this->reservas->crearHuespedes($detallePrincipal, is_array($huespedes) ? $huespedes : []);

            foreach ($servicios as $servicio) {
                $recursoServicio = $this->reservas->resolverRecurso(TipoReserva::SERVICIO, $servicio['servicio_id']);
                $this->disponibilidadRecursos->bloquear($recursoServicio->id);

                if ($recursoServicio->control_disponibilidad !== ControlDisponibilidad::SIN_BLOQUEO
                    && $this->disponibilidadRecursos->existeConflicto($recursoServicio->id, $inicio, $fin)) {
                    throw new InvalidArgumentException("El servicio {$recursoServicio->nombre} no está disponible en el periodo solicitado.");
                }

                $this->reservas->crearDetalle($reserva, $recursoServicio, [
                    'parent_id' => $detallePrincipal->id,
                    'estado' => EstadoReservaDetalle::PENDIENTE,
                    'fecha_inicio' => $inicio,
                    'fecha_fin' => $fin,
                    'cantidad' => $servicio['cantidad'],
                    'precio_unitario' => $servicio['precio'],
                    'subtotal' => round($servicio['precio'] * $servicio['cantidad'], 2),
                ]);
            }

            foreach ($espacios as $espacio) {
                $recursoEspacio = $this->reservas->resolverRecurso(TipoReserva::RESTAURANTE, $espacio['espacio_id']);
                $this->disponibilidadRecursos->bloquear($recursoEspacio->id);

                if ($recursoEspacio->control_disponibilidad !== ControlDisponibilidad::SIN_BLOQUEO
                    && $this->disponibilidadRecursos->existeConflicto($recursoEspacio->id, $inicio, $fin)) {
                    throw new InvalidArgumentException("El espacio {$recursoEspacio->nombre} no está disponible en el periodo solicitado.");
                }

                $this->reservas->crearDetalle($reserva, $recursoEspacio, [
                    'parent_id' => $detallePrincipal->id,
                    'estado' => EstadoReservaDetalle::PENDIENTE,
                    'fecha_inicio' => $inicio,
                    'fecha_fin' => $fin,
                    'cantidad' => $espacio['cantidad'],
                    'precio_unitario' => $espacio['precio'],
                    'subtotal' => round($espacio['precio'] * $espacio['cantidad'], 2),
                ]);
            }

            $reservaCargada = $reserva->load('detalles.reservable', 'detalles.huespedes', 'historialEstados');

            ReservaCreada::dispatch($reservaCargada);

            return $reservaCargada;
        });
    }

    /** @param array<string, mixed> $datos */
    private function obtenerPrecioPrincipal(TipoReserva $tipo, array $datos, DateTimeImmutable $checkIn, ?DateTimeImmutable $checkOut): float
    {
        return match ($tipo) {
            TipoReserva::HABITACION => $this->precioHabitacion($this->enteroRequerido($datos, 'habitacion_id'), $checkIn, $checkOut),
            TipoReserva::RESTAURANTE => $this->tarifas->espacio($this->enteroRequerido($datos, 'espacio_id')),
            TipoReserva::SERVICIO => $this->tarifas->servicio($this->enteroRequerido($datos, 'servicio_id')),
            TipoReserva::PAQUETE => throw new InvalidArgumentException('Las reservas de paquete todavía no tienen una regla de tarifa configurada.'),
        };
    }

    private function precioHabitacion(int $habitacionId, DateTimeImmutable $checkIn, ?DateTimeImmutable $checkOut): float
    {
        $salida = $checkOut ?? $checkIn->modify('+1 day');
        $this->disponibilidad->bloquearHabitacion($habitacionId);
        $conflicto = $this->disponibilidad->existeConflicto($habitacionId, $checkIn, $salida);

        if (! $this->validarDisponibilidad->estaDisponible($conflicto)) {
            throw new InvalidArgumentException('La habitación seleccionada no se encuentra disponible en las fechas especificadas.');
        }

        return $this->tarifas->habitacion($habitacionId);
    }

    /** @param array<string|int, mixed> $datos */
    private function enteroRequerido(array $datos, string $campo): int
    {
        $valor = $datos[$campo] ?? null;

        if (is_int($valor)) {
            return $valor;
        }

        if (is_string($valor) && ctype_digit($valor)) {
            return (int) $valor;
        }

        throw new InvalidArgumentException("El campo {$campo} no es válido.");
    }

    /** @param array<string, mixed> $datos */
    private function enteroOpcional(array $datos, string $campo, int $predeterminado): int
    {
        $valor = $datos[$campo] ?? $predeterminado;

        return is_numeric($valor) ? (int) $valor : $predeterminado;
    }

    /** @param array<string, mixed> $datos */
    private function idEntidadPrincipal(TipoReserva $tipo, array $datos): int
    {
        return match ($tipo) {
            TipoReserva::HABITACION => $this->enteroRequerido($datos, 'habitacion_id'),
            TipoReserva::RESTAURANTE => $this->enteroRequerido($datos, 'espacio_id'),
            TipoReserva::SERVICIO => $this->enteroRequerido($datos, 'servicio_id'),
            TipoReserva::PAQUETE => throw new InvalidArgumentException('Los paquetes todavía no están habilitados.'),
        };
    }
}
