<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Gestion;

use App\Actions\Reservas\GenerarCodigoReserva;
use App\BusinessLogic\Reservas\AplicarPromocionReserva;
use App\BusinessLogic\Reservas\CalcularPeriodoReserva;
use App\BusinessLogic\Reservas\CalcularResumenRestauranteLogica;
use App\BusinessLogic\Reservas\CalcularUnidadesReserva;
use App\BusinessLogic\Reservas\ConstruirBitacoraReserva;
use App\BusinessLogic\Reservas\LeerDatoReserva;
use App\BusinessLogic\Reservas\ParsearPayloadReserva;
use App\BusinessLogic\Reservas\ResolverHabitacionDisponibleLogica;
use App\BusinessLogic\Reservas\ResolverIdEntidadPrincipal;
use App\BusinessLogic\Reservas\ResolverTipoPagoReserva;
use App\BusinessLogic\Reservas\ValidarDisponibilidadHabitacion;
use App\BusinessLogic\Reservas\ValidarDisponibilidadRecursoLote;
use App\BusinessLogic\Reservas\ValidarFechasReserva;
use App\BusinessLogic\Reservas\ValidarSeleccionAdicionales;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Events\Reservas\ReservaCreada;
use App\Interactors\Reservas\Operaciones\RegistrarCobroInicialReserva;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Queries\Monedas\ObtenerMonedaPredeterminadaQuery;
use App\Repository\Queries\Reservas\DisponibilidadRecursoQuery;
use App\Repository\Queries\Reservas\ObtenerPromocionReservaQuery;
use App\Repository\Queries\Reservas\ObtenerTarifasReservaQuery;
use App\Repository\Queries\Reservas\ReservaDisponibilidadQuery;
use DateTimeImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class CrearReserva
{
    public function __construct(
        private ParsearPayloadReserva $parsearPayload,
        private ResolverIdEntidadPrincipal $resolverIdEntidad,
        private ResolverHabitacionDisponibleLogica $resolverHabitacion,
        private ResolverTipoPagoReserva $resolverTipoPago,
        private ValidarDisponibilidadHabitacion $validarDisponibilidad,
        private ReservaDisponibilidadQuery $disponibilidad,
        private ObtenerTarifasReservaQuery $tarifas,
        private ReservaRepositorioInterface $reservas,
        private GenerarCodigoReserva $generarCodigo,
        private DisponibilidadRecursoQuery $disponibilidadRecursos,
        private ObtenerPromocionReservaQuery $promociones,
        private AplicarPromocionReserva $aplicarPromocion,
        private CalcularPeriodoReserva $calcularPeriodo,
        private CalcularUnidadesReserva $calcularUnidades,
        private ValidarSeleccionAdicionales $validarAdicionales,
        private RegistrarCobroInicialReserva $registrarCobroInicial,
        private ValidarFechasReserva $validarFechas,
        private CalcularResumenRestauranteLogica $calcularResumenRestauranteLogica,
        private LeerDatoReserva $leerDato,
        private ConstruirBitacoraReserva $construirBitacoraReserva,
        private ObtenerMonedaPredeterminadaQuery $obtenerMonedaPredeterminada,
        private ValidarDisponibilidadRecursoLote $validarLote,
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<int, mixed>  $serviciosAdicionales
     * @param  array<int, mixed>  $espaciosAdicionales
     * @param  array<int, mixed>  $habitacionesAdicionales
     *
     * @throws Throwable
     */
    public function ejecutar(
        array $datos,
        array $serviciosAdicionales = [],
        array $espaciosAdicionales = [],
        array $habitacionesAdicionales = [],
    ): Reserva {
        return DB::transaction(callback: function () use ($datos, $serviciosAdicionales, $espaciosAdicionales, $habitacionesAdicionales): Reserva {
            $entrada = $this->parsearPayload->parsear($datos);

            $this->validarFechas->validar($entrada['checkIn'], $entrada['horaReserva']);

            [$entidadId, $datos, $espaciosAdicionales] = $this->resolverEntidadYDisponibilidad(
                tipo: $entrada['tipo'],
                datos: $datos,
                checkIn: $entrada['checkIn'],
                checkOut: $entrada['checkOut'],
                horaReserva: $entrada['horaReserva'],
                espaciosAdicionales: $espaciosAdicionales,
                itemsPreorden: $entrada['itemsPreorden'],
            );

            [$servicios, $espacios, $habitaciones] = $this->resolverAdicionales(
                tipo: $entrada['tipo'],
                entidadId: $entidadId,
                serviciosAdicionales: $serviciosAdicionales,
                espaciosAdicionales: $espaciosAdicionales,
                habitacionesAdicionales: $habitacionesAdicionales,
            );

            $recursoPrincipal = $this->reservas->resolverRecurso($entrada['tipo'], $entidadId);
            [$inicio, $fin] = $this->calcularPeriodo->calcular($entrada['checkIn'], $entrada['checkOut'], $datos, $recursoPrincipal->duracion_minutos);
            $esPorHora = $entrada['tipo'] === TipoReserva::RESTAURANTE && $this->tarifas->espacioEsPorHora($entidadId);
            $unidades = $this->calcularUnidades->calcular($entrada['tipo'], $entrada['checkIn'], $entrada['checkOut'], $esPorHora, $inicio, $fin);

            [$subtotal, $precioPrincipal, $resumenRestaurante] = $this->calcularSubtotalReserva(
                tipo: $entrada['tipo'],
                entidadId: $entidadId,
                datos: $datos,
                checkIn: $entrada['checkIn'],
                checkOut: $entrada['checkOut'],
                servicios: $servicios,
                espacios: $espacios,
                habitaciones: $habitaciones,
                unidades: $unidades,
                itemsPreorden: $entrada['itemsPreorden'],
                espaciosAdicionales: $espaciosAdicionales,
            );

            [$totales, $promocionId] = $this->calcularTotalesConPromocion($datos, $subtotal);

            $reserva = $this->reservas->crear(
                $this->construirAtributosReserva(
                    tipo: $entrada['tipo'],
                    datos: $datos,
                    checkIn: $entrada['checkIn'],
                    checkOut: $entrada['checkOut'],
                    horaReserva: $entrada['horaReserva'],
                    totales: $totales,
                    promocionId: $promocionId,
                )
            );

            $this->registrarBitacora($reserva, $datos, $resumenRestaurante);

            $detallePrincipal = $this->crearDetallePrincipal(
                reserva: $reserva,
                recursoPrincipal: $recursoPrincipal,
                inicio: $inicio,
                fin: $fin,
                subtotal: $subtotal,
                unidades: $unidades,
                tipo: $entrada['tipo'],
                precioPrincipal: $precioPrincipal,
                datos: $datos,
            );

            $this->validarYCrearDetallesAdicionales($reserva, $detallePrincipal, $habitaciones, $servicios, $espacios, $inicio, $fin, $unidades, $resumenRestaurante);

            $reserva = $this->procesarPago($reserva, $datos);

            $reservaCargada = $reserva->load('detalles.reservable', 'detalles.huespedes', 'historialEstados');
            ReservaCreada::dispatch($reservaCargada);

            return $reservaCargada;
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Resolución de entidad principal y validación de disponibilidad
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Resuelve la habitación o espacio disponible y valida conflictos de disponibilidad.
     * Para HABITACION: delega a ResolverHabitacionDisponibleLogica.
     * Para RESTAURANTE: valida conflicto de espacio y completa espacios sugeridos.
     *
     * @param  array<string, mixed>  $datos
     * @param  array<int, mixed>  $espaciosAdicionales
     * @param  array<mixed>  $itemsPreorden
     * @return array{0: int, 1: array<string, mixed>, 2: array<int, mixed>}
     */
    private function resolverEntidadYDisponibilidad(
        TipoReserva $tipo,
        array $datos,
        DateTimeImmutable $checkIn,
        ?DateTimeImmutable $checkOut,
        ?string $horaReserva,
        array $espaciosAdicionales,
        array $itemsPreorden,
    ): array {
        $entidadId = $this->resolverIdEntidad->resolver($tipo, $datos);

        if ($tipo === TipoReserva::HABITACION) {
            $entidadId = $this->resolverHabitacion->resolver(
                habitacionSolicitadaId: $entidadId,
                checkIn: $checkIn,
                checkOut: $checkOut,
                adultos: $this->leerDato->enteroOpcional($datos, 'adultos', 1),
                ninos: $this->leerDato->enteroOpcional($datos, 'ninos', 0),
            );
            $datos['habitacion_id'] = $entidadId;
        }

        if ($tipo === TipoReserva::RESTAURANTE && $entidadId > 0) {
            if ($this->disponibilidad->existeConflictoEspacio($entidadId, $checkIn, $horaReserva)) {
                throw new DomainException("La mesa/espacio seleccionado ya cuenta con una reservación activa para la fecha {$checkIn->format('Y-m-d')} y la hora indicada.");
            }

            $espaciosAdicionales = $this->calcularResumenRestauranteLogica->completarEspaciosSugeridos(
                $entidadId, $datos, $espaciosAdicionales, $itemsPreorden,
            );
        }

        return [$entidadId, $datos, $espaciosAdicionales];
    }

    /**
     * Resuelve los adicionales (servicios, espacios, habitaciones) según el tipo de reserva.
     *
     * @param  array<int, mixed>  $serviciosAdicionales
     * @param  array<int, mixed>  $espaciosAdicionales
     * @param  array<int, mixed>  $habitacionesAdicionales
     * @return array{0: array<int, array{servicio_id: int, cantidad: int, precio: float}>, 1: array<int, array{espacio_id: int, cantidad: int, precio: float}>, 2: array<int, array{habitacion_id: int, cantidad: int, precio: float}>}
     */
    private function resolverAdicionales(
        TipoReserva $tipo,
        int $entidadId,
        array $serviciosAdicionales,
        array $espaciosAdicionales,
        array $habitacionesAdicionales,
    ): array {
        $servicios = $this->validarAdicionales->resolverServicios(
            $serviciosAdicionales,
            $tipo === TipoReserva::SERVICIO ? $entidadId : null,
        );
        $espacios = $this->validarAdicionales->resolverEspacios(
            $espaciosAdicionales,
            $tipo === TipoReserva::RESTAURANTE ? $entidadId : null,
        );
        $habitaciones = $this->validarAdicionales->resolverHabitaciones(
            $habitacionesAdicionales,
            $tipo === TipoReserva::HABITACION ? $entidadId : null,
        );

        return [$servicios, $espacios, $habitaciones];
    }

    /**
     * Registra las entradas de bitácora de creación de la reserva.
     *
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

    /**
     * Valida disponibilidad por lote y crea los detalles adicionales de la reserva.
     *
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

    // ─────────────────────────────────────────────────────────────────────────
    // Cálculo de subtotales
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Calcula el subtotal de la reserva según el tipo:
     * - RESTAURANTE: delega al resumen del restaurante.
     * - Otros: suma precio principal + adicionales ponderados por unidades.
     *
     * @param  array<string, mixed>  $datos
     * @param  array<int, array{servicio_id: int, cantidad: int, precio: float}>  $servicios
     * @param  array<int, array{espacio_id: int, cantidad: int, precio: float}>  $espacios
     * @param  array<int, array{habitacion_id: int, precio: float}>  $habitaciones
     * @param  array<mixed>  $itemsPreorden
     * @param  array<int, mixed>  $espaciosAdicionales
     * @return array{0: float, 1: float, 2: ?array<string, mixed>}
     */
    private function calcularSubtotalReserva(
        TipoReserva $tipo,
        int $entidadId,
        array $datos,
        DateTimeImmutable $checkIn,
        ?DateTimeImmutable $checkOut,
        array $servicios,
        array $espacios,
        array $habitaciones,
        int $unidades,
        array $itemsPreorden,
        array $espaciosAdicionales,
    ): array {
        if ($tipo === TipoReserva::RESTAURANTE) {
            $resumenRestaurante = $this->calcularResumenRestauranteLogica->ejecutar($entidadId, $datos, $espaciosAdicionales, $itemsPreorden);
            $totalResumen = $resumenRestaurante['total'] ?? $resumenRestaurante['subtotal'] ?? 0.0;
            $subtotal = is_numeric($totalResumen) ? (float) $totalResumen : 0.0;

            return [$subtotal, 0.0, $resumenRestaurante];
        }

        $precioPrincipal = $this->obtenerPrecioPrincipal($tipo, $datos, $checkIn, $checkOut);
        $subtotalServicios = (float) array_sum(array_map(static fn (array $s): float => (float) $s['precio'] * (int) $s['cantidad'], $servicios));
        $subtotalEspacios = (float) array_sum(array_map(static fn (array $e): float => (float) $e['precio'] * (int) $e['cantidad'], $espacios));
        $subtotalHabitaciones = (float) array_sum(array_map(static fn (array $h): float => (float) $h['precio'] * $unidades, $habitaciones));
        $subtotal = round(($precioPrincipal * $unidades) + $subtotalServicios + $subtotalEspacios + $subtotalHabitaciones, 2);

        return [$subtotal, $precioPrincipal, null];
    }

    /**
     * Aplica la promoción al subtotal y retorna los totales calculados junto con el ID de promoción usado.
     *
     * @param  array<string, mixed>  $datos
     * @return array{0: array{subtotal: float, descuento: float, total: float}, 1: int|null}
     */
    private function calcularTotalesConPromocion(array $datos, float $subtotal): array
    {
        $promocionId = is_numeric($datos['promocion_id'] ?? null) ? (int) $datos['promocion_id'] : null;
        $promocion = $promocionId !== null ? $this->promociones->vigente($promocionId) : null;

        $totales = $this->aplicarPromocion->calcular(
            $subtotal,
            $promocion?->descuento_porcentaje !== null ? (float) $promocion->descuento_porcentaje : null,
            $promocion?->descuento_monto !== null ? (float) $promocion->descuento_monto : null,
            $promocion?->precio_paquete !== null ? (float) $promocion->precio_paquete : null,
        );

        return [$totales, $promocion?->id];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Construcción del modelo y metadata
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Construye el arreglo de atributos para persistir la reserva.
     *
     * @param  array<string, mixed>  $datos
     * @param  array{subtotal: float, descuento: float, total: float}  $totales
     * @return array<string, mixed>
     */
    private function construirAtributosReserva(
        TipoReserva $tipo,
        array $datos,
        DateTimeImmutable $checkIn,
        ?DateTimeImmutable $checkOut,
        ?string $horaReserva,
        array $totales,
        ?int $promocionId,
    ): array {
        $clienteIdVal = $datos['cliente_id'] ?? null;
        $clienteId = is_numeric($clienteIdVal) && (int) $clienteIdVal > 0 ? (int) $clienteIdVal : null;

        return [
            'codigo_reserva' => $this->generarCodigo->ejecutar(),
            'cliente_id' => $clienteId,
            'nombre_cliente' => $datos['nombre_cliente'],
            'telefono_cliente' => $datos['telefono_cliente'] ?? null,
            'email_cliente' => $datos['email_cliente'] ?? null,
            'tipo_reserva' => $tipo,
            'habitacion_id' => $datos['habitacion_id'] ?? null,
            'espacio_id' => $datos['espacio_id'] ?? null,
            'servicio_id' => $datos['servicio_id'] ?? null,
            'promocion_id' => $promocionId,
            'moneda_id' => is_numeric($datos['moneda_id'] ?? null)
                ? (int) $datos['moneda_id']
                : $this->obtenerMonedaPredeterminada->ejecutar()?->id,
            'fecha_check_in' => $checkIn->format('Y-m-d'),
            'fecha_check_out' => $checkOut?->format('Y-m-d'),
            'hora_reserva' => $horaReserva,
            'adultos' => $this->leerDato->enteroOpcional($datos, 'adultos', 1),
            'ninos' => $this->leerDato->enteroOpcional($datos, 'ninos', 0),
            'subtotal' => $totales['subtotal'],
            'descuento' => $totales['descuento'],
            'total' => $totales['total'],
            'total_pagado' => 0,
            'saldo' => $totales['total'],
            'estado' => EstadoReserva::CONFIRMADA,
            'notas' => $datos['notas'] ?? $datos['observaciones'] ?? null,
            'acompanantes' => $datos['acompanantes'] ?? null,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Creación de detalles de la reserva
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Verifica disponibilidad del recurso principal, crea el detalle y registra huéspedes.
     *
     * @param  array<string, mixed>  $datos
     */
    private function crearDetallePrincipal(
        Reserva $reserva,
        RecursoReservable $recursoPrincipal,
        DateTimeImmutable $inicio,
        DateTimeImmutable $fin,
        float $subtotal,
        int $unidades,
        TipoReserva $tipo,
        float $precioPrincipal,
        array $datos,
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

        $this->registrarHuespedes($detallePrincipal, $datos);

        return $detallePrincipal;
    }

    /**
     * Extrae y filtra huéspedes del payload, luego los persiste en el detalle.
     *
     * @param  array<string, mixed>  $datos
     */
    private function registrarHuespedes(ReservaDetalle $detalle, array $datos): void
    {
        $huespedes = is_array($datos['huespedes'] ?? null) && $datos['huespedes'] !== []
            ? $datos['huespedes']
            : (is_array($datos['acompanantes'] ?? null) ? $datos['acompanantes'] : []);

        $huespedesFiltrados = array_values(array_filter(
            $huespedes,
            fn (mixed $item): bool => is_array($item)
                && isset($item['nombre'])
                && is_string($item['nombre'])
                && trim($item['nombre']) !== '',
        ));

        if ($huespedesFiltrados !== []) {
            $this->reservas->crearHuespedes($detalle, $huespedesFiltrados);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Pago
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Extrae los parámetros de pago del payload y registra el cobro inicial.
     *
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

        if ($this->esPagoPorStripe($datos)) {
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

    /** @param array<string, mixed> $datos */
    private function esPagoPorStripe(array $datos): bool
    {
        $tipoPago = $this->resolverTipoPago->resolver($datos);

        if (($datos['canal_pago_reserva'] ?? null) === 'stripe') {
            return true;
        }

        return ($datos['origen_pago_reserva'] ?? null) === 'publico'
            && $tipoPago !== TipoPagoReserva::SIN_PAGO
            && ! is_numeric($datos['metodo_pago_reserva'] ?? $datos['metodo_pago_abono'] ?? null);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Utilidades y helpers de precio
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $datos
     */
    private function obtenerPrecioPrincipal(TipoReserva $tipo, array $datos, DateTimeImmutable $checkIn, ?DateTimeImmutable $checkOut): float
    {
        return match ($tipo) {
            TipoReserva::HABITACION => $this->precioHabitacion($this->leerDato->enteroRequerido($datos, 'habitacion_id'), $checkIn, $checkOut),
            TipoReserva::RESTAURANTE => $this->tarifas->espacio($this->leerDato->enteroRequerido($datos, 'espacio_id')),
            TipoReserva::SERVICIO => $this->tarifas->servicio($this->leerDato->enteroRequerido($datos, 'servicio_id')),
            TipoReserva::PAQUETE => (is_numeric($datos['habitacion_id'] ?? null) ? $this->precioHabitacion((int) $datos['habitacion_id'], $checkIn, $checkOut) : 0.0)
                + (is_numeric($datos['espacio_id'] ?? null) ? $this->tarifas->espacio((int) $datos['espacio_id']) : 0.0)
                + (is_numeric($datos['servicio_id'] ?? null) ? $this->tarifas->servicio((int) $datos['servicio_id']) : 0.0),
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
}
