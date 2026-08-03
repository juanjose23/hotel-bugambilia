<?php

declare(strict_types=1);

namespace App\Interactors\Reservas;

use App\BusinessLogic\Reservas\AplicarPromocionReserva;
use App\BusinessLogic\Reservas\CalcularPeriodoReserva;
use App\BusinessLogic\Reservas\CalcularResumenRestauranteLogica;
use App\BusinessLogic\Reservas\CalcularUnidadesReserva;
use App\BusinessLogic\Reservas\ValidarDisponibilidadHabitacion;
use App\BusinessLogic\Reservas\ValidarFechasReserva;
use App\BusinessLogic\Reservas\ValidarSeleccionAdicionales;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Events\Reservas\ReservaCreada;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Queries\Reservas\DisponibilidadRecursoQuery;
use App\Repository\Queries\Reservas\ObtenerPromocionReservaQuery;
use App\Repository\Queries\Reservas\ObtenerTarifasReservaQuery;
use App\Repository\Queries\Reservas\ReservaDisponibilidadQuery;
use DateMalformedStringException;
use DateTimeImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class CrearReserva
{
    public function __construct(
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
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<int, mixed>  $serviciosAdicionales
     * @param  array<int, mixed>  $espaciosAdicionales
     *
     * @throws Throwable
     */
    public function ejecutar(array $datos, array $serviciosAdicionales = [], array $espaciosAdicionales = []): Reserva
    {
        return DB::transaction(callback: function () use ($datos, $serviciosAdicionales, $espaciosAdicionales): Reserva {
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

            $horaReservaStr = is_string($datos['hora_reserva'] ?? null) ? trim((string) $datos['hora_reserva']) : null;

            // Delegamos reglas de negocio de fechas
            $this->validarFechas->validar($checkIn, $horaReservaStr);

            if ($tipo === TipoReserva::RESTAURANTE && $entidadPrincipalId > 0) {
                if ($this->disponibilidad->existeConflictoEspacio($entidadPrincipalId, $checkIn, $horaReservaStr)) {
                    throw new DomainException("La mesa/espacio seleccionado ya cuenta con una reservación activa para la fecha {$checkIn->format('Y-m-d')} y la hora indicada.");
                }
            }

            $precioPrincipal = $this->obtenerPrecioPrincipal($tipo, $datos, $checkIn, $checkOut);
            $servicios = $this->validarAdicionales->resolverServicios(
                $serviciosAdicionales,
                $tipo === TipoReserva::SERVICIO ? $entidadPrincipalId : null,
            );
            $espacios = $this->validarAdicionales->resolverEspacios(
                $espaciosAdicionales,
                $tipo === TipoReserva::RESTAURANTE ? $entidadPrincipalId : null,
            );

            $recursoPrincipal = $this->reservas->resolverRecurso($tipo, $entidadPrincipalId);
            [$inicio, $fin] = $this->calcularPeriodo->calcular($checkIn, $checkOut, $datos, $recursoPrincipal->duracion_minutos);

            $esPorHora = $tipo === TipoReserva::RESTAURANTE && $this->tarifas->espacioEsPorHora($entidadPrincipalId);
            $unidades = $this->calcularUnidades->calcular($tipo, $checkIn, $checkOut, $esPorHora, $inicio, $fin);

            $resumenRestaurante = null;
            if ($tipo === TipoReserva::RESTAURANTE) {
                $itemsPreorden = is_array($datos['items_preorden'] ?? null) ? $datos['items_preorden'] : [];
                $resumenRestaurante = $this->calcularResumenRestauranteLogica->ejecutar($entidadPrincipalId, $datos, $espaciosAdicionales, $itemsPreorden);
                $totalResumen = $resumenRestaurante['total'] ?? $resumenRestaurante['subtotal'] ?? 0.0;
                $subtotal = is_numeric($totalResumen) ? (float) $totalResumen : 0.0;
            } else {
                $subtotalServicios = array_sum(array_map(
                    fn (array $servicio): float => $servicio['precio'] * $servicio['cantidad'],
                    $servicios,
                ));
                $subtotalEspacios = array_sum(array_map(
                    fn (array $esp): float => $esp['precio'] * $esp['cantidad'],
                    $espacios,
                ));
                $subtotal = round(($precioPrincipal * $unidades) + $subtotalServicios + $subtotalEspacios, 2);
            }

            $promocionId = is_numeric($datos['promocion_id'] ?? null) ? (int) $datos['promocion_id'] : null;
            $promocion = $promocionId !== null ? $this->promociones->vigente($promocionId) : null;
            $totales = $this->aplicarPromocion->calcular(
                $subtotal,
                $promocion?->descuento_porcentaje !== null ? (float) $promocion->descuento_porcentaje : null,
                $promocion?->descuento_monto !== null ? (float) $promocion->descuento_monto : null,
            );

            $rawPreorden = is_array($datos['items_preorden'] ?? null) ? $datos['items_preorden'] : [];
            $platosPreordenados = [];

            foreach ($rawPreorden as $item) {
                if (! is_array($item) || ! is_numeric($item['plato_id'] ?? null)) {
                    continue;
                }

                $platoId = (int) $item['plato_id'];
                $precioVal = $item['precio_unitario'] ?? null;
                $precio = is_numeric($precioVal) ? (float) $precioVal : 0.0;
                $cantidad = max(1, is_numeric($item['cantidad'] ?? null) ? (int) $item['cantidad'] : 1);
                $platoModel = Plato::find($platoId);
                $nombrePlato = $platoModel !== null ? $platoModel->nombre : "Platillo #{$platoId}";

                $platosPreordenados[] = [
                    'plato_id' => $platoId,
                    'nombre' => $nombrePlato,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => round($precio * $cantidad, 2),
                    'observaciones' => is_string($item['observaciones'] ?? null) ? trim($item['observaciones']) : null,
                ];
            }

            /** @var array<string, mixed> $metaDatos */
            $metaDatos = [
                'platos_preordenados' => $platosPreordenados,
                'resumen_restaurante' => $resumenRestaurante,
            ];

            $clienteIdVal = $datos['cliente_id'] ?? null;
            $clienteId = is_numeric($clienteIdVal) && (int) $clienteIdVal > 0 ? (int) $clienteIdVal : null;

            $reserva = $this->reservas->crear([
                'codigo_reserva' => $this->generarCodigo->ejecutar(),
                'cliente_id' => $clienteId,
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
                'duracion_unidades' => $unidades,
                'moneda_id' => $datos['moneda_id'] ?? null,
                'subtotal' => $totales['subtotal'],
                'descuento_monto' => $totales['descuento'],
                'impuestos_monto' => 0,
                'total' => $totales['total'],
                'monto_pagado' => 0,
                'saldo_pendiente' => $totales['total'],
                'estado' => EstadoReserva::CONFIRMADA,
                'observaciones' => $datos['observaciones'] ?? null,
                'acompanantes' => $datos['acompanantes'] ?? null,
                'meta_datos' => $metaDatos,
            ]);

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

            $huespedes = is_array($datos['huespedes'] ?? null) ? $datos['huespedes'] : [];
            $huespedesFiltrados = array_values(array_filter(
                $huespedes,
                fn (mixed $item): bool => is_array($item) && isset($item['nombre']) && is_string($item['nombre']) && trim($item['nombre']) !== '',
            ));

            if ($huespedesFiltrados !== []) {
                $this->reservas->crearHuespedes($detallePrincipal, $huespedesFiltrados);
            }

            foreach ($servicios as $servicio) {
                $recursoServicio = $this->reservas->resolverRecurso(TipoReserva::SERVICIO, $servicio['servicio_id']);
                $this->disponibilidadRecursos->bloquear($recursoServicio->id);

                if ($recursoServicio->control_disponibilidad !== ControlDisponibilidad::SIN_BLOQUEO
                    && $this->disponibilidadRecursos->existeConflicto($recursoServicio->id, $inicio, $fin)) {
                    throw new InvalidArgumentException("El servicio {$recursoServicio->nombre} no está disponible en el horario especificado.");
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

                $horasVal = is_numeric($resumenRestaurante['horas'] ?? null) ? (int) $resumenRestaurante['horas'] : 1;
                $mult = ($tipo === TipoReserva::RESTAURANTE && $this->tarifas->espacioEsPorHora($espacio['espacio_id'])) ? $horasVal : 1;
                $subtotalEspacioDetalle = round($espacio['precio'] * $espacio['cantidad'] * $mult, 2);

                $this->reservas->crearDetalle($reserva, $recursoEspacio, [
                    'parent_id' => $detallePrincipal->id,
                    'estado' => EstadoReservaDetalle::CONFIRMADO,
                    'fecha_inicio' => $inicio,
                    'fecha_fin' => $fin,
                    'cantidad' => $espacio['cantidad'],
                    'precio_unitario' => $espacio['precio'],
                    'subtotal' => $subtotalEspacioDetalle,
                ]);
            }

            $metodoPagoValor = $datos['metodo_pago_reserva'] ?? $datos['metodo_pago_abono'] ?? null;
            $metodoPago = is_numeric($metodoPagoValor) ? MetodoPago::tryFrom((int) $metodoPagoValor) : null;
            $monedaId = is_numeric($datos['moneda_id'] ?? null) ? (int) $datos['moneda_id'] : null;
            $referenciaPago = is_string($datos['referencia_pago_reserva'] ?? null)
                ? trim($datos['referencia_pago_reserva'])
                : (is_string($datos['referencia_abono'] ?? null) ? trim($datos['referencia_abono']) : null);

            $reserva = $this->registrarCobroInicial->ejecutar(
                reserva: $reserva,
                tipoPago: $this->resolverTipoPago($datos),
                monedaId: $monedaId,
                metodoPago: $metodoPago,
                referencia: $referenciaPago !== '' ? $referenciaPago : null,
                usuarioId: auth()->id() !== null ? (int) auth()->id() : null,
                montoSolicitado: is_numeric($datos['monto_pago_reserva'] ?? null) ? (float) $datos['monto_pago_reserva'] : null,
                cargosFacturacionIds: array_map(
                    function (mixed $id): int|string {
                        if (is_int($id)) {
                            return $id;
                        }
                        if (is_numeric($id)) {
                            return (int) $id;
                        }

                        return is_string($id) ? $id : '';
                    },
                    is_array($datos['cargos_facturacion_ids'] ?? null) ? $datos['cargos_facturacion_ids'] : []
                ),
            );

            $reservaCargada = $reserva->load('detalles.reservable', 'detalles.huespedes', 'historialEstados');

            ReservaCreada::dispatch($reservaCargada);

            return $reservaCargada;
        });
    }

    /** @param array<string, mixed> $datos */
    private function resolverTipoPago(array $datos): TipoPagoReserva
    {
        $tipoPago = $datos['tipo_pago_reserva'] ?? null;

        if (is_string($tipoPago) && TipoPagoReserva::tryFrom($tipoPago) !== null) {
            return TipoPagoReserva::from($tipoPago);
        }

        return $datos['registrar_abono'] ?? false
            ? TipoPagoReserva::ABONO_50
            : TipoPagoReserva::SIN_PAGO;
    }

    /**
     * @param  array<string, mixed>  $datos
     *
     * @throws DateMalformedStringException
     */
    private function obtenerPrecioPrincipal(TipoReserva $tipo, array $datos, DateTimeImmutable $checkIn, ?DateTimeImmutable $checkOut): float
    {
        return match ($tipo) {
            TipoReserva::HABITACION => $this->precioHabitacion($this->enteroRequerido($datos, 'habitacion_id'), $checkIn, $checkOut),
            TipoReserva::RESTAURANTE => $this->tarifas->espacio($this->enteroRequerido($datos, 'espacio_id')),
            TipoReserva::SERVICIO => $this->tarifas->servicio($this->enteroRequerido($datos, 'servicio_id')),
            TipoReserva::PAQUETE => throw new InvalidArgumentException('Las reservas de paquete todavía no tienen una regla de tarifa configurada.'),
        };
    }

    /**
     * @throws DateMalformedStringException
     */
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

        throw new InvalidArgumentException("El campo $campo no es válido.");
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
            TipoReserva::PAQUETE => $this->enteroOpcional($datos, 'paquete_id', 0),
        };
    }
}
