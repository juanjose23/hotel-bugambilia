<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Handlers;

use App\BusinessLogic\Reservas\Builders\ReservaBuilder;
use App\BusinessLogic\Reservas\Calculos\AplicarPromocionReserva;
use App\BusinessLogic\Reservas\Calculos\CalcularPeriodoReserva;
use App\BusinessLogic\Reservas\Calculos\CalcularResumenRestauranteLogica;
use App\BusinessLogic\Reservas\Calculos\CalcularUnidadesReserva;
use App\BusinessLogic\Reservas\Contracts\ReservaScenarioHandlerInterface;
use App\BusinessLogic\Reservas\Data\CrearReservaInputData;
use App\BusinessLogic\Reservas\Validaciones\ValidarSeleccionAdicionales;
use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Queries\Reservas\DisponibilidadRecursoQuery;
use App\Repository\Queries\Reservas\ObtenerPromocionReservaQuery;
use App\Repository\Queries\Reservas\ObtenerTarifasReservaQuery;
use DomainException;

final readonly class RestauranteScenarioHandler implements ReservaScenarioHandlerInterface
{
    public function __construct(
        private DisponibilidadRecursoQuery $disponibilidad,
        private CalcularResumenRestauranteLogica $calcularResumenRestauranteLogica,
        private ValidarSeleccionAdicionales $validarAdicionales,
        private ReservaRepositorioInterface $reservas,
        private ObtenerTarifasReservaQuery $tarifas,
        private CalcularPeriodoReserva $calcularPeriodo,
        private CalcularUnidadesReserva $calcularUnidades,
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
        $checkOut = $input->checkOut;
        $horaReserva = $input->horaReserva;
        $adultos = $input->adultos;
        $ninos = $input->ninos;

        // 1. Resolver entidad de restaurante / mesa
        $espacioId = $input->entidadPrincipalId;

        // 2. Completar espacios sugeridos
        $espaciosAdicionalesCompletados = $this->calcularResumenRestauranteLogica->completarEspaciosSugeridos(
            $espacioId,
            $input->datosOriginales,
            $input->espaciosAdicionales,
            $input->itemsPreorden,
        );

        // 3. Resolver adicionales
        $servicios = $this->validarAdicionales->resolverServicios($input->serviciosAdicionales, null);
        $espacios = $this->validarAdicionales->resolverEspacios($espaciosAdicionalesCompletados, $espacioId);
        $habitaciones = $this->validarAdicionales->resolverHabitaciones($input->habitacionesAdicionales, null);

        // 4. Periodo y unidades
        $recursoPrincipal = $this->reservas->resolverRecurso(TipoReserva::RESTAURANTE, $espacioId);
        [$inicio, $fin] = $this->calcularPeriodo->calcular($checkIn, $checkOut, $input->datosOriginales, $recursoPrincipal->duracion_minutos);
        $esPorHora = $this->tarifas->espacioEsPorHora($espacioId);
        $unidades = $this->calcularUnidades->calcular(TipoReserva::RESTAURANTE, $checkIn, $checkOut, $esPorHora, $inicio, $fin);

        // 5. Validar disponibilidad del recurso en el horario solicitado
        $this->disponibilidad->bloquear($recursoPrincipal->id);
        if ($recursoPrincipal->control_disponibilidad !== ControlDisponibilidad::SIN_BLOQUEO
            && $this->disponibilidad->existeConflicto($recursoPrincipal->id, $inicio, $fin)) {
            throw new DomainException("La mesa/espacio seleccionado ya cuenta con una reservación activa para la fecha {$inicio->format('Y-m-d')} y el horario indicado.");
        }

        // 6. Resumen de restaurante y subtotales
        $resumenRestaurante = $this->calcularResumenRestauranteLogica->ejecutar(
            $espacioId,
            $input->datosOriginales,
            $espaciosAdicionalesCompletados,
            $input->itemsPreorden,
        );
        $totalResumen = $resumenRestaurante['total'] ?? $resumenRestaurante['subtotal'] ?? 0.0;
        $subtotal = is_numeric($totalResumen) ? (float) $totalResumen : 0.0;

        // 7. Promoción
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
            ->paraTipo(TipoReserva::RESTAURANTE)
            ->conCliente(
                clienteId: $input->clienteId,
                nombre: $input->nombreCliente,
                telefono: $input->telefonoCliente,
                email: $input->emailCliente,
            )
            ->conFechas($checkIn, $checkOut, $horaReserva)
            ->conCapacidad($adultos, $ninos)
            ->conPeriodoCalculado($inicio, $fin, $unidades)
            ->conRecursoPrincipal(entidadId: $espacioId, precioPrincipal: 0.0, espacioId: $espacioId)
            ->conServiciosAdicionales($servicios)
            ->conEspaciosAdicionales($espacios)
            ->conHabitacionesAdicionales($habitaciones)
            ->conResumenRestaurante($resumenRestaurante)
            ->conMoneda($input->monedaId)
            ->conPromocion($promocion?->id)
            ->conTotales($totales['subtotal'], $totales['descuento'], $totales['total'])
            ->conNotas($input->notas)
            ->conAcompanantes($input->datosOriginales['acompanantes'] ?? null)
            ->conHuespedes($huespedes)
            ->conDatosOriginales($input->datosOriginales);
    }
}
