<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Gestion;

use App\Actions\Reservas\GenerarCodigoReserva;
use App\BusinessLogic\Reservas\Builders\ReservaBuilder;
use App\BusinessLogic\Reservas\Data\CrearReservaInputData;
use App\BusinessLogic\Reservas\Data\ReservaPasarelaResultado;
use App\BusinessLogic\Reservas\Factories\ReservaScenarioFactory;
use App\BusinessLogic\Reservas\Normalizadores\NormalizarPagoPublicoReserva;
use App\BusinessLogic\Reservas\Resolutores\ResolverTipoPagoReserva;
use App\BusinessLogic\Reservas\Support\ConstruirBitacoraReserva;
use App\BusinessLogic\Reservas\Validaciones\ValidarDisponibilidadRecursoLote;
use App\BusinessLogic\Reservas\Validaciones\ValidarFechasReserva;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Events\Reservas\ReservaCreada;
use App\Interactors\Facturacion\Stripe\CrearIntentoPagoStripe;
use App\Interactors\Reservas\Operaciones\RegistrarCobroInicialReserva;
use App\Interactors\Servicios\ValidarCupoServicio;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Queries\Monedas\ObtenerMonedaPredeterminadaQuery;
use App\Repository\Queries\Reservas\DisponibilidadRecursoQuery;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class CrearReserva
{
    public function __construct(
        private ValidarFechasReserva $validarFechas,
        private ReservaScenarioFactory $scenarioFactory,
        private ReservaRepositorioInterface $reservas,
        private GenerarCodigoReserva $generarCodigo,
        private ObtenerMonedaPredeterminadaQuery $obtenerMonedaPredeterminada,
        private DisponibilidadRecursoQuery $disponibilidadRecursos,
        private ValidarDisponibilidadRecursoLote $validarLote,
        private ConstruirBitacoraReserva $construirBitacoraReserva,
        private ResolverTipoPagoReserva $resolverTipoPago,
        private RegistrarCobroInicialReserva $registrarCobroInicial,
        private CrearIntentoPagoStripe $crearIntentoStripe,
        private NormalizarPagoPublicoReserva $normalizarPagoPublico,
        private ValidarCupoServicio $validarCupoServicio,
    ) {}

    /**
     * @param  CrearReservaInputData|array<string, mixed>  $datos
     * @param  array<int, mixed>  $serviciosAdicionales
     * @param  array<int, mixed>  $espaciosAdicionales
     * @param  array<int, mixed>  $habitacionesAdicionales
     *
     * @throws Throwable
     */
    public function ejecutarConPasarela(
        CrearReservaInputData|array $datos,
        array $serviciosAdicionales = [],
        array $espaciosAdicionales = [],
        array $habitacionesAdicionales = [],
        ?int $clienteId = null,
    ): ReservaPasarelaResultado {
        return DB::transaction(function () use ($datos, $serviciosAdicionales, $espaciosAdicionales, $habitacionesAdicionales, $clienteId): ReservaPasarelaResultado {
            $input = $datos instanceof CrearReservaInputData
                ? $datos
                : CrearReservaInputData::fromArray($datos, $serviciosAdicionales, $espaciosAdicionales, $habitacionesAdicionales, $clienteId);

            $datosNormalizados = $input->datosOriginales;
            $datosNormalizados['cliente_id'] = $clienteId ?? $input->clienteId;
            $datosNormalizados['origen_pago_reserva'] = $input->origenPago !== '' ? $input->origenPago : 'publico';
            $datosNormalizados = $this->normalizarPagoPublico->normalizar($datosNormalizados);

            $inputFinal = CrearReservaInputData::fromArray(
                $datosNormalizados,
                $input->serviciosAdicionales,
                $input->espaciosAdicionales,
                $input->habitacionesAdicionales,
                $clienteId ?? $input->clienteId,
            );

            $reserva = $this->ejecutar($inputFinal);

            $requierePagoStripe = ($datosNormalizados['canal_pago_reserva'] ?? 'stripe') === 'stripe'
                && $reserva->tipo_pago !== TipoPagoReserva::SIN_PAGO;

            $stripePago = null;
            if ($requierePagoStripe) {
                $stripePago = $this->crearIntentoStripe->ejecutarParaReserva($reserva);
            }

            return new ReservaPasarelaResultado(
                reserva: $reserva,
                requierePagoStripe: $requierePagoStripe,
                stripePago: $stripePago,
            );
        });
    }

    /**
     * @param  CrearReservaInputData|array<string, mixed>  $datos
     * @param  array<int, mixed>  $serviciosAdicionales
     * @param  array<int, mixed>  $espaciosAdicionales
     * @param  array<int, mixed>  $habitacionesAdicionales
     *
     * @throws Throwable
     */
    public function ejecutar(
        CrearReservaInputData|array $datos,
        array $serviciosAdicionales = [],
        array $espaciosAdicionales = [],
        array $habitacionesAdicionales = [],
    ): Reserva {
        return DB::transaction(callback: function () use ($datos, $serviciosAdicionales, $espaciosAdicionales, $habitacionesAdicionales): Reserva {
            $input = $datos instanceof CrearReservaInputData
                ? $datos
                : CrearReservaInputData::fromArray($datos, $serviciosAdicionales, $espaciosAdicionales, $habitacionesAdicionales);

            $this->validarFechas->validar($input->checkIn, $input->horaReserva);

            // Guard-clause de cupo: una salida/recorrido de servicio no puede exceder su aforo (flota fija).
            if ($input->tipo === TipoReserva::SERVICIO) {
                $this->validarCupoServicio->ejecutar(
                    servicioId: $input->entidadPrincipalId,
                    participantes: $input->adultos + $input->ninos,
                );
            }

            // 1. Factoría (Factory Method): obtener el manejador según TipoReserva
            $handler = $this->scenarioFactory->fabricar($input->tipo);

            // 2. Builder: construir y validar el agregado de datos
            $builder = ReservaBuilder::nuevo();
            $builder = $handler->configurar(
                builder: $builder,
                datos: $input,
                serviciosAdicionales: $input->serviciosAdicionales,
                espaciosAdicionales: $input->espaciosAdicionales,
                habitacionesAdicionales: $input->habitacionesAdicionales,
            );

            // 3. Persistencia de la cabecera
            $atributosReserva = $builder->construirAtributos(
                generarCodigo: $this->generarCodigo,
                obtenerMoneda: $this->obtenerMonedaPredeterminada,
            );
            $reserva = $this->reservas->crear($atributosReserva);

            // 4. Registrar bitácora inicial
            $this->registrarBitacora($reserva, $builder->obtenerDatosOriginales(), $builder->obtenerResumenRestaurante());

            // 5. Crear detalle principal
            $recursoPrincipal = $this->reservas->resolverRecurso($builder->obtenerTipo(), $builder->obtenerEntidadPrincipalId());
            $detallePrincipal = $this->crearDetallePrincipal(
                reserva: $reserva,
                recursoPrincipal: $recursoPrincipal,
                inicio: $builder->obtenerInicioPeriodo(),
                fin: $builder->obtenerFinPeriodo(),
                subtotal: $builder->obtenerSubtotal(),
                unidades: $builder->obtenerUnidades(),
                tipo: $builder->obtenerTipo(),
                precioPrincipal: $builder->obtenerPrecioPrincipal(),
                builder: $builder,
            );

            // 6. Validar y crear detalles adicionales
            $this->validarYCrearDetallesAdicionales(
                reserva: $reserva,
                detallePrincipal: $detallePrincipal,
                habitaciones: $builder->obtenerHabitacionesAdicionales(),
                servicios: $builder->obtenerServiciosAdicionales(),
                espacios: $builder->obtenerEspaciosAdicionales(),
                inicio: $builder->obtenerInicioPeriodo(),
                fin: $builder->obtenerFinPeriodo(),
                unidades: $builder->obtenerUnidades(),
                resumenRestaurante: $builder->obtenerResumenRestaurante(),
            );

            // 7. Procesar pago inicial
            $reserva = $this->procesarPago($reserva, $builder->obtenerDatosOriginales());

            // 8. Carga de relaciones y despacho de evento
            $reservaCargada = $reserva->load('detalles.reservable', 'detalles.huespedes', 'historialEstados');
            ReservaCreada::dispatch($reservaCargada);

            return $reservaCargada;
        });
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<string, mixed>|null  $resumenRestaurante
     */
    private function registrarBitacora(Reserva $reserva, array $datos, ?array $resumenRestaurante): void
    {
        /** @var list<array{tipo: string, datos: array<string, mixed>}> $entradasBitacora */
        $entradasBitacora = $this->construirBitacoraReserva->paraCreacion($datos, $resumenRestaurante);

        foreach ($entradasBitacora as $entradaBitacora) {
            $reserva->crearEntradaBitacora($entradaBitacora['tipo'], $entradaBitacora['datos']);
        }
    }

    private function crearDetallePrincipal(
        Reserva $reserva,
        RecursoReservable $recursoPrincipal,
        DateTimeImmutable $inicio,
        DateTimeImmutable $fin,
        float $subtotal,
        int $unidades,
        TipoReserva $tipo,
        float $precioPrincipal,
        ReservaBuilder $builder,
    ): ReservaDetalle {
        $this->disponibilidadRecursos->bloquear($recursoPrincipal->id);

        if ($recursoPrincipal->control_disponibilidad !== ControlDisponibilidad::SIN_BLOQUEO
            && $this->disponibilidadRecursos->existeConflicto($recursoPrincipal->id, $inicio, $fin)) {
            throw new InvalidArgumentException("El recurso {$recursoPrincipal->nombre} no se encuentra disponible en las fechas y horas indicadas.");
        }

        $precioUnitarioDetalle = $tipo === TipoReserva::RESTAURANTE
            ? round($subtotal / max(1, $unidades), 2)
            : $precioPrincipal;

        $detallePrincipal = $this->reservas->crearDetalle($reserva, $recursoPrincipal, [
            'estado' => EstadoReservaDetalle::CONFIRMADO,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'cantidad' => 1,
            'precio_unitario' => $precioUnitarioDetalle,
            'subtotal' => round($precioUnitarioDetalle * $unidades, 2),
        ]);

        $this->registrarHuespedes($detallePrincipal, $builder);

        return $detallePrincipal;
    }

    private function registrarHuespedes(ReservaDetalle $detalle, ReservaBuilder $builder): void
    {
        $huespedes = $builder->obtenerHuespedes();
        if ($huespedes === []) {
            $acompanantes = $builder->obtenerAcompanantes();
            if (is_array($acompanantes)) {
                $huespedes = $acompanantes;
            }
        }

        if ($huespedes !== []) {
            $this->reservas->crearHuespedes($detalle, $huespedes);
        }
    }

    /**
     * @param  array<int, array{habitacion_id: int, precio: float}>  $habitaciones
     * @param  array<int, array{servicio_id: int, cantidad: int, precio: float}>  $servicios
     * @param  array<int, array{espacio_id: int, cantidad: int, precio: float}>  $espacios
     * @param  array<string, mixed>|null  $resumenRestaurante
     */
    private function validarYCrearDetallesAdicionales(
        Reserva $reserva,
        ReservaDetalle $detallePrincipal,
        array $habitaciones,
        array $servicios,
        array $espacios,
        DateTimeImmutable $inicio,
        DateTimeImmutable $fin,
        int $unidades,
        ?array $resumenRestaurante,
    ): void {
        $recursos = $this->validarLote->ejecutar($habitaciones, $servicios, $espacios, $inicio, $fin);
        $horasVal = is_numeric($resumenRestaurante['horas'] ?? null) ? (float) $resumenRestaurante['horas'] : null;
        $this->reservas->crearDetallesAdicionales($reserva, $detallePrincipal, $recursos, $habitaciones, $servicios, $espacios, $inicio, $fin, $unidades, $horasVal);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function procesarPago(Reserva $reserva, array $datos): Reserva
    {
        $metodoPagoValor = $datos['metodo_pago_reserva'] ?? $datos['metodo_pago_abono'] ?? null;
        $metodoPago = is_numeric($metodoPagoValor) ? MetodoPago::tryFrom((int) $metodoPagoValor) : null;
        $monedaId = is_numeric($datos['moneda_id'] ?? null) ? (int) $datos['moneda_id'] : null;
        $referenciaPago = is_string($datos['referencia_pago_reserva'] ?? null)
            ? trim($datos['referencia_pago_reserva'])
            : (is_string($datos['referencia_abono'] ?? null) ? trim($datos['referencia_abono']) : null);

        $cargosIds = array_map(
            static function (mixed $id): int|string {
                if (is_int($id)) {
                    return $id;
                }
                if (is_numeric($id)) {
                    return (int) $id;
                }

                return is_string($id) ? $id : '';
            },
            is_array($datos['cargos_facturacion_ids'] ?? null) ? $datos['cargos_facturacion_ids'] : [],
        );

        if ($this->normalizarPagoPublico->esPagoPorStripe($datos)) {
            $reserva = $this->registrarCobroInicial->ejecutar(
                reserva: $reserva,
                tipoPago: TipoPagoReserva::SIN_PAGO,
                monedaId: $monedaId,
                metodoPago: null,
                referencia: null,
                usuarioId: auth()->id() !== null ? (int) auth()->id() : null,
                montoSolicitado: null,
                cargosFacturacionIds: $cargosIds,
            );

            $tipoPagoPolitica = $this->resolverTipoPago->resolver($datos);
            $reserva->crearEntradaBitacora('politica_pago', [
                'canal' => 'sistema_publico',
                'pasarela' => 'stripe',
                'tipo_cliente' => is_string($datos['tipo_cliente_pago'] ?? null) ? trim($datos['tipo_cliente_pago']) : 'publico',
                'tipo_pago_requerido' => $tipoPagoPolitica->value,
                'porcentaje_requerido' => 50,
                'opciones_disponibles' => ['stripe', 'transferencia'],
                'estado' => 'pendiente_pasarela',
            ]);

            return $this->reservas->actualizar($reserva, [
                'tipo_pago' => $tipoPagoPolitica,
                'total_pagado' => 0,
                'saldo' => (float) $reserva->total,
                'estado' => EstadoReserva::PENDIENTE,
            ]);
        }

        return $this->registrarCobroInicial->ejecutar(
            reserva: $reserva,
            tipoPago: $this->resolverTipoPago->resolver($datos),
            monedaId: $monedaId,
            metodoPago: $metodoPago,
            referencia: $referenciaPago !== '' ? $referenciaPago : null,
            usuarioId: auth()->id() !== null ? (int) auth()->id() : null,
            montoSolicitado: is_numeric($datos['monto_pago_reserva'] ?? null) ? (float) $datos['monto_pago_reserva'] : null,
            cargosFacturacionIds: $cargosIds,
        );
    }
}
