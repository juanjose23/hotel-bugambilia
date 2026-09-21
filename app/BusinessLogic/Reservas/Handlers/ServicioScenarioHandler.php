<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Handlers;

use App\BusinessLogic\Reservas\Builders\ReservaBuilder;
use App\BusinessLogic\Reservas\Calculos\AplicarPromocionReserva;
use App\BusinessLogic\Reservas\Calculos\CalcularPeriodoReserva;
use App\BusinessLogic\Reservas\Calculos\CalcularUnidadesReserva;
use App\BusinessLogic\Reservas\Contracts\ReservaScenarioHandlerInterface;
use App\BusinessLogic\Reservas\Data\CrearReservaInputData;
use App\BusinessLogic\Reservas\Validaciones\ValidarSeleccionAdicionales;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Queries\Reservas\ObtenerPromocionReservaQuery;
use App\Repository\Queries\Reservas\ObtenerTarifasReservaQuery;

final readonly class ServicioScenarioHandler implements ReservaScenarioHandlerInterface
{
    public function __construct(
        private ObtenerTarifasReservaQuery $tarifas,
        private ReservaRepositorioInterface $reservas,
        private CalcularPeriodoReserva $calcularPeriodo,
        private CalcularUnidadesReserva $calcularUnidades,
        private ValidarSeleccionAdicionales $validarAdicionales,
        private ObtenerPromocionReservaQuery $promociones,
        private AplicarPromocionReserva $aplicarPromocion,
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
        $checkOut = $input->checkOut ?? $checkIn;
        $horaReserva = $input->horaReserva;
        $adultos = $input->adultos;
        $ninos = $input->ninos;

        // 1. Resolver servicio principal
        $servicioId = $input->entidadPrincipalId;

        // 2. Resolver adicionales
        $servicios = $this->validarAdicionales->resolverServicios($input->serviciosAdicionales, $servicioId);
        $espacios = $this->validarAdicionales->resolverEspacios($input->espaciosAdicionales, null);
        $habitaciones = $this->validarAdicionales->resolverHabitaciones($input->habitacionesAdicionales, null);

        // 3. Periodo y unidades
        $recursoPrincipal = $this->reservas->resolverRecurso(TipoReserva::SERVICIO, $servicioId);
        [$inicio, $fin] = $this->calcularPeriodo->calcular($checkIn, $checkOut, $input->datosOriginales, $recursoPrincipal->duracion_minutos);
        $unidades = $this->calcularUnidades->calcular(TipoReserva::SERVICIO, $checkIn, $checkOut, false, $inicio, $fin);

        // 4. Precio principal y subtotales
        $precioPrincipal = $this->tarifas->servicio($servicioId);
        $subtotalServicios = (float) array_sum(array_map(static fn (array $s): float => (float) $s['precio'] * (int) $s['cantidad'], $servicios));
        $subtotalEspacios = (float) array_sum(array_map(static fn (array $e): float => (float) $e['precio'] * (int) $e['cantidad'], $espacios));
        $subtotalHabitaciones = (float) array_sum(array_map(static fn (array $h): float => (float) $h['precio'] * $unidades, $habitaciones));
        $subtotal = round(($precioPrincipal * $unidades) + $subtotalServicios + $subtotalEspacios + $subtotalHabitaciones, 2);

        // 5. Promoción
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
            ->paraTipo(TipoReserva::SERVICIO)
            ->conCliente(
                clienteId: $input->clienteId,
                nombre: $input->nombreCliente,
                telefono: $input->telefonoCliente,
                email: $input->emailCliente,
            )
            ->conFechas($checkIn, $checkOut, $horaReserva)
            ->conCapacidad($adultos, $ninos)
            ->conPeriodoCalculado($inicio, $fin, $unidades)
            ->conRecursoPrincipal(entidadId: $servicioId, precioPrincipal: $precioPrincipal, servicioId: $servicioId)
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
}
