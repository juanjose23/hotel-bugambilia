<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Handlers;

use App\BusinessLogic\Reservas\Builders\ReservaBuilder;
use App\BusinessLogic\Reservas\Calculos\AplicarPromocionReserva;
use App\BusinessLogic\Reservas\Calculos\CalcularPeriodoReserva;
use App\BusinessLogic\Reservas\Calculos\CalcularUnidadesReserva;
use App\BusinessLogic\Reservas\Contracts\ReservaScenarioHandlerInterface;
use App\BusinessLogic\Reservas\Data\CrearReservaInputData;
use App\BusinessLogic\Reservas\Resolutores\ResolverHabitacionDisponibleLogica;
use App\BusinessLogic\Reservas\Validaciones\ValidarDisponibilidadHabitacion;
use App\BusinessLogic\Reservas\Validaciones\ValidarSeleccionAdicionales;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Queries\Reservas\DisponibilidadRecursoQuery;
use App\Repository\Queries\Reservas\ObtenerPromocionReservaQuery;
use App\Repository\Queries\Reservas\ObtenerTarifasReservaQuery;
use DateTimeImmutable;
use InvalidArgumentException;

final readonly class HabitacionScenarioHandler implements ReservaScenarioHandlerInterface
{
    public function __construct(
        private ResolverHabitacionDisponibleLogica $resolverHabitacion,
        private ValidarDisponibilidadHabitacion $validarDisponibilidad,
        private DisponibilidadRecursoQuery $disponibilidad,
        private ObtenerTarifasReservaQuery $tarifas,
        private ReservaRepositorioInterface $reservas,
        private ObtenerPromocionReservaQuery $promociones,
        private AplicarPromocionReserva $aplicarPromocion,
        private CalcularPeriodoReserva $calcularPeriodo,
        private CalcularUnidadesReserva $calcularUnidades,
        private ValidarSeleccionAdicionales $validarAdicionales,
    ) {}

    /**
     * @param  CrearReservaInputData|array<string, mixed>  $datos
     * @param  array<int, mixed>  $serviciosAdicionales
     * @param  array<int, mixed>  $espaciosAdicionales
     * @param  array<int, mixed>  $habitacionesAdicionales
     */
    public function configurar(
        ReservaBuilder $builder,
        CrearReservaInputData|array $datos,
        array $serviciosAdicionales = [],
        array $espaciosAdicionales = [],
        array $habitacionesAdicionales = [],
    ): ReservaBuilder {
        $input = $datos instanceof CrearReservaInputData
            ? $datos
            : CrearReservaInputData::fromArray($datos, $serviciosAdicionales, $espaciosAdicionales, $habitacionesAdicionales);

        $checkIn = $input->checkIn;
        $checkOut = $input->checkOut ?? $checkIn->modify('+1 day');
        $adultos = $input->adultos;
        $ninos = $input->ninos;

        // 1. Resolver y validar habitación principal
        $entidadSolicitadaId = $input->entidadPrincipalId;
        $habitacionId = $this->resolverHabitacion->resolver(
            habitacionSolicitadaId: $entidadSolicitadaId,
            checkIn: $checkIn,
            checkOut: $checkOut,
            adultos: $adultos,
            ninos: $ninos,
        );

        // 2. Resolver adicionales
        $servicios = $this->validarAdicionales->resolverServicios($input->serviciosAdicionales, null);
        $espacios = $this->validarAdicionales->resolverEspacios($input->espaciosAdicionales, null);
        $habitaciones = $this->validarAdicionales->resolverHabitaciones($input->habitacionesAdicionales, $habitacionId);

        // 3. Periodo y unidades
        $recursoPrincipal = $this->reservas->resolverRecurso(TipoReserva::HABITACION, $habitacionId);
        [$inicio, $fin] = $this->calcularPeriodo->calcular($checkIn, $checkOut, $input->datosOriginales, $recursoPrincipal->duracion_minutos);
        $unidades = $this->calcularUnidades->calcular(TipoReserva::HABITACION, $checkIn, $checkOut, false, $inicio, $fin);

        // 4. Validar disponibilidad y obtener precio de habitación
        $precioPrincipal = $this->obtenerPrecioHabitacion($habitacionId, $recursoPrincipal, $checkIn, $checkOut);

        // 5. Cálculo de subtotales
        $subtotalServicios = (float) array_sum(array_map(static fn (array $s): float => (float) $s['precio'] * (int) $s['cantidad'], $servicios));
        $subtotalEspacios = (float) array_sum(array_map(static fn (array $e): float => (float) $e['precio'] * (int) $e['cantidad'], $espacios));
        $subtotalHabitaciones = (float) array_sum(array_map(static fn (array $h): float => (float) $h['precio'] * $unidades, $habitaciones));
        $subtotal = round(($precioPrincipal * $unidades) + $subtotalServicios + $subtotalEspacios + $subtotalHabitaciones, 2);

        // 6. Promoción
        $promocionId = is_numeric($input->datosOriginales['promocion_id'] ?? null) ? (int) $input->datosOriginales['promocion_id'] : null;
        $promocion = $promocionId !== null ? $this->promociones->vigente($promocionId) : null;
        $totales = $this->aplicarPromocion->calcular(
            $subtotal,
            $promocion?->descuento_porcentaje !== null ? (float) $promocion->descuento_porcentaje : null,
            $promocion?->descuento_monto !== null ? (float) $promocion->descuento_monto : null,
            $promocion?->precio_paquete !== null ? (float) $promocion->precio_paquete : null,
        );

        $huespedes = $input->huespedes !== []
            ? $input->huespedes
            : (is_array($input->datosOriginales['acompanantes'] ?? null) ? $input->datosOriginales['acompanantes'] : []);

        return $builder
            ->paraTipo(TipoReserva::HABITACION)
            ->conCliente(
                clienteId: $input->clienteId,
                nombre: $input->nombreCliente,
                telefono: $input->telefonoCliente,
                email: $input->emailCliente,
            )
            ->conFechas($checkIn, $checkOut)
            ->conCapacidad($adultos, $ninos)
            ->conPeriodoCalculado($inicio, $fin, $unidades)
            ->conRecursoPrincipal(entidadId: $habitacionId, precioPrincipal: $precioPrincipal, habitacionId: $habitacionId)
            ->conServiciosAdicionales($servicios)
            ->conEspaciosAdicionales($espacios)
            ->conHabitacionesAdicionales($habitaciones)
            ->conMoneda($input->monedaId)
            ->conPromocion($promocion?->id)
            ->conTotales($totales['subtotal'], $totales['descuento'], $totales['total'])
            ->conNotas($input->notas)
            ->conAcompanantes($input->datosOriginales['acompanantes'] ?? null)
            ->conHuespedes($huespedes)
            ->conDatosOriginales($input->datosOriginales);
    }

    private function obtenerPrecioHabitacion(
        int $habitacionId,
        RecursoReservable $recurso,
        DateTimeImmutable $checkIn,
        ?DateTimeImmutable $checkOut,
    ): float {
        $salida = $checkOut ?? $checkIn->modify('+1 day');
        $this->disponibilidad->bloquear($recurso->id);
        $conflicto = $this->disponibilidad->existeConflicto($recurso->id, $checkIn, $salida);

        if (! $this->validarDisponibilidad->estaDisponible($conflicto)) {
            throw new InvalidArgumentException('La habitación seleccionada no se encuentra disponible en las fechas especificadas.');
        }

        return $this->tarifas->habitacion($habitacionId);
    }
}
