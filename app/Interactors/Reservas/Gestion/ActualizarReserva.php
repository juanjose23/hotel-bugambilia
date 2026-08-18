<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Gestion;

use App\BusinessLogic\Reservas\CalcularPeriodoReserva;
use App\BusinessLogic\Reservas\CalcularResumenRestauranteLogica;
use App\BusinessLogic\Reservas\ConstruirBitacoraReserva;
use App\BusinessLogic\Reservas\LeerDatoReserva;
use App\BusinessLogic\Reservas\ResolverIdEntidadPrincipal;
use App\BusinessLogic\Reservas\ValidarDisponibilidadRecursoLote;
use App\BusinessLogic\Reservas\ValidarFechasReserva;
use App\BusinessLogic\Reservas\ValidarSeleccionAdicionales;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\Cuentas\Gestion\AbrirCuenta;
use App\Interactors\Reservas\Operaciones\SincronizarCuentaReserva;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Queries\Cuentas\ObtenerCuentaReservaQuery;
use App\Repository\Queries\Reservas\CalcularVistaPreviaFinancieraReservaQuery;
use App\Repository\Queries\Reservas\DisponibilidadRecursoQuery;
use DateMalformedStringException;
use DateTimeImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class ActualizarReserva
{
    public function __construct(
        private readonly ReservaRepositorioInterface $reservas,
        private readonly ValidarSeleccionAdicionales $validarAdicionales,
        private readonly CalcularVistaPreviaFinancieraReservaQuery $calcularVistaPrevia,
        private readonly DisponibilidadRecursoQuery $disponibilidadRecursos,
        private readonly ValidarFechasReserva $validarFechas,
        private readonly ObtenerCuentaReservaQuery $obtenerCuentaReserva,
        private readonly SincronizarCuentaReserva $sincronizarCuentaReserva,
        private readonly CalcularResumenRestauranteLogica $calcularResumenRestauranteLogica,
        private readonly AbrirCuenta $abrirCuenta,
        private readonly CalcularPeriodoReserva $calcularPeriodo,
        private readonly ResolverIdEntidadPrincipal $resolverIdEntidad,
        private readonly LeerDatoReserva $leerDato,
        private readonly ConstruirBitacoraReserva $construirBitacoraReserva,
        private readonly ValidarDisponibilidadRecursoLote $validarLote,
    ) {}

    /** @param array<string, mixed> $datos */
    public function ejecutar(Reserva $reserva, array $datos): Reserva
    {
        if (in_array($reserva->estado, [EstadoReserva::PARCIALMENTE_CHECKED_OUT, EstadoReserva::CHECKED_OUT, EstadoReserva::CANCELADA, EstadoReserva::NO_SHOW], true)) {
            throw new DomainException('No se pueden modificar los recursos de una reserva que ya fue completada o cancelada.');
        }

        return DB::transaction(function () use ($reserva, $datos): Reserva {
            $datosRecalculo = $this->datosParaRecalculo($reserva, $datos);
            $datosRecalculo['reserva_id'] = $reserva->id;

            [$tipo, $entidadPrincipalId, $espaciosSolicitados] = $this->resolverTipoYEntidad($datosRecalculo, $datos);

            [$servicios, $espacios, $habitaciones] = $this->resolverAdicionales(
                $tipo, $entidadPrincipalId, $datos, $espaciosSolicitados,
            );

            $principal = $this->reservas->detallePrincipalDe($reserva);
            [$checkIn, $checkOut, $horaReserva] = $this->parsearFechas($datosRecalculo);

            $this->validarFechas->validar($checkIn, $horaReserva);
            $recursoPrincipal = $this->reservas->resolverRecurso($tipo, $entidadPrincipalId);
            [$inicio, $fin] = $this->calcularPeriodo->calcular($checkIn, $checkOut, $datosRecalculo, $recursoPrincipal->duracion_minutos);

            $this->validarLote->ejecutar($habitaciones, $servicios, $espacios, $inicio, $fin, $reserva->id);
            $this->validarDisponibilidadPrincipal($reserva, $recursoPrincipal, $inicio, $fin);

            $resumen = $this->calcularVistaPrevia->ejecutar($datosRecalculo);
            $principal = $this->actualizarDetallePrincipal($tipo, $principal, $recursoPrincipal->id, $inicio, $fin, $datosRecalculo, $resumen);
            $this->reservas->reemplazarAdicionales($reserva, $principal, $servicios, $espacios, $habitaciones);

            $total = (float) $resumen['total'];
            $totalPagado = (float) $reserva->total_pagado;
            $saldo = round(max(0.0, $total - $totalPagado), 2);

            $reserva = $this->reservas->actualizar($reserva, $this->construirAtributosActualizacion(
                $datos, $datosRecalculo, $tipo, $entidadPrincipalId,
                $checkIn, $checkOut, $horaReserva, $resumen, $totalPagado, $saldo,
            ));

            $entradaBitacora = $this->construirBitacoraReserva->paraActualizacion($reserva, $datosRecalculo);
            $reserva->actualizarOCrearEntradaBitacora($entradaBitacora['tipo'], $entradaBitacora['datos']);

            return $this->sincronizarCuentaPostActualizacion($reserva, $tipo, $datos);
        });
    }

    /**
     * Resuelve el tipo de reserva, entidad principal y espacios solicitados.
     *
     * @param  array<string, mixed>  $datosRecalculo
     * @param  array<string, mixed>  $datos
     * @return array{0: TipoReserva, 1: int, 2: array<int, mixed>}
     */
    private function resolverTipoYEntidad(array $datosRecalculo, array $datos): array
    {
        $tipoRaw = $datosRecalculo['tipo_reserva'] ?? TipoReserva::HABITACION;
        $tipoStr = is_string($tipoRaw) ? $tipoRaw : ($tipoRaw instanceof TipoReserva ? $tipoRaw->value : TipoReserva::HABITACION->value);
        $tipo = TipoReserva::from($tipoStr);
        $entidadPrincipalId = $this->resolverIdEntidad->resolver($tipo, $datosRecalculo);
        $espaciosSolicitados = $this->leerDato->arreglo($datos, 'espacios_adicionales');

        if ($tipo === TipoReserva::RESTAURANTE) {
            $espaciosSolicitados = $this->calcularResumenRestauranteLogica->completarEspaciosSugeridos(
                $entidadPrincipalId, $datosRecalculo, $espaciosSolicitados,
                $this->leerDato->arreglo($datosRecalculo, 'items_preorden'),
            );
            $datosRecalculo['espacios_adicionales'] = $espaciosSolicitados;
        }

        return [$tipo, $entidadPrincipalId, $espaciosSolicitados];
    }

    /**
     * Resuelve servicios, espacios y habitaciones adicionales.
     *
     * @param  array<string, mixed>  $datos
     * @param  array<int, mixed>  $espaciosSolicitados
     * @return array{0: array<int, array{servicio_id: int, cantidad: int, precio: float}>, 1: array<int, array{espacio_id: int, cantidad: int, precio: float}>, 2: array<int, array{habitacion_id: int, cantidad: int, precio: float}>}
     */
    private function resolverAdicionales(
        TipoReserva $tipo,
        int $entidadPrincipalId,
        array $datos,
        array $espaciosSolicitados,
    ): array {
        $servicios = $this->validarAdicionales->resolverServicios(
            $this->leerDato->arreglo($datos, 'servicios_adicionales'),
        );
        $espacios = $this->validarAdicionales->resolverEspacios($espaciosSolicitados);
        $habitaciones = $this->validarAdicionales->resolverHabitaciones(
            $this->leerDato->arreglo($datos, 'habitaciones_adicionales'),
            $tipo === TipoReserva::HABITACION ? $entidadPrincipalId : null,
        );

        return [$servicios, $espacios, $habitaciones];
    }

    /**
     * Parsea las fechas del payload de recálculo.
     *
     * @param  array<string, mixed>  $datosRecalculo
     * @return array{0: DateTimeImmutable, 1: DateTimeImmutable|null, 2: string|null}
     */
    private function parsearFechas(array $datosRecalculo): array
    {
        $checkInStr = is_string($datosRecalculo['fecha_check_in'] ?? null)
            ? (string) $datosRecalculo['fecha_check_in']
            : ($datosRecalculo['fecha_check_in'] instanceof DateTimeImmutable ? $datosRecalculo['fecha_check_in']->format('Y-m-d') : 'today');
        $checkIn = new DateTimeImmutable($checkInStr);
        $checkOut = is_string($datosRecalculo['fecha_check_out'] ?? null)
            ? new DateTimeImmutable((string) $datosRecalculo['fecha_check_out'])
            : null;
        $horaReserva = is_string($datosRecalculo['hora_reserva'] ?? null)
            ? trim((string) $datosRecalculo['hora_reserva'])
            : null;

        return [$checkIn, $checkOut, $horaReserva];
    }

    /**
     * Construye el arreglo de atributos para actualizar la reserva.
     *
     * @param  array<string, mixed>  $datos
     * @param  array<string, mixed>  $datosRecalculo
     * @param  array{subtotal: float, descuento: float, total: float}  $resumen
     * @return array<string, mixed>
     */
    private function construirAtributosActualizacion(
        array $datos,
        array $datosRecalculo,
        TipoReserva $tipo,
        int $entidadPrincipalId,
        DateTimeImmutable $checkIn,
        ?DateTimeImmutable $checkOut,
        ?string $horaReserva,
        array $resumen,
        float $totalPagado,
        float $saldo,
    ): array {
        $total = (float) $resumen['total'];

        return [
            'cliente_id' => is_numeric($datos['cliente_id'] ?? null) ? (int) $datos['cliente_id'] : null,
            'nombre_cliente' => $datos['nombre_cliente'],
            'telefono_cliente' => $datos['telefono_cliente'] ?? null,
            'email_cliente' => $datos['email_cliente'] ?? null,
            'tipo_reserva' => $tipo,
            'habitacion_id' => $tipo === TipoReserva::HABITACION ? $entidadPrincipalId : null,
            'espacio_id' => $tipo === TipoReserva::RESTAURANTE ? $entidadPrincipalId : null,
            'servicio_id' => $tipo === TipoReserva::SERVICIO ? $entidadPrincipalId : null,
            'promocion_id' => is_numeric($datosRecalculo['promocion_id'] ?? null) ? (int) $datosRecalculo['promocion_id'] : null,
            'fecha_check_in' => $checkIn->format('Y-m-d'),
            'fecha_check_out' => $checkOut?->format('Y-m-d'),
            'hora_reserva' => $horaReserva,
            'adultos' => $this->leerDato->enteroOpcional($datosRecalculo, 'adultos', 1),
            'ninos' => $this->leerDato->enteroOpcional($datosRecalculo, 'ninos', 0),
            'solicita_cuenta' => (bool) ($datos['solicita_cuenta'] ?? false),
            'limite_cuenta_solicitado' => is_numeric($datos['limite_cuenta_solicitado'] ?? null)
                ? (float) $datos['limite_cuenta_solicitado']
                : null,
            'notas' => $datos['notas'] ?? null,
            'acompanantes' => $datos['acompanantes'] ?? null,
            'subtotal' => $resumen['subtotal'],
            'descuento' => $resumen['descuento'],
            'total' => $total,
            'tipo_pago' => $this->tipoPagoActualizado($totalPagado, $saldo),
            'saldo' => $saldo,
        ];
    }

    /**
     * Sincroniza la cuenta después de la actualización (abrir si no existe o no permite cargos).
     *
     * @param  array<string, mixed>  $datos
     */
    private function sincronizarCuentaPostActualizacion(Reserva $reserva, TipoReserva $tipo, array $datos): Reserva
    {
        $cuenta = $this->obtenerCuentaReserva->ejecutar((int) $reserva->id);

        if ($cuenta === null || ! $cuenta->estado->permiteNuevosCargos()) {
            $tipoCuenta = match ($tipo) {
                TipoReserva::HABITACION => TipoCuenta::ESTANCIA,
                TipoReserva::RESTAURANTE => TipoCuenta::RESTAURANTE_DIRECTO,
                TipoReserva::SERVICIO, TipoReserva::PAQUETE => TipoCuenta::SERVICIO,
            };

            $cuenta = $this->abrirCuenta->ejecutar(
                tipo: $tipoCuenta,
                reserva: $reserva,
                cliente: $reserva->cliente,
                monedaId: $reserva->moneda_id,
                usuarioId: is_numeric($datos['usuario_id'] ?? null) ? (int) $datos['usuario_id'] : null,
            );
        }

        return $this->sincronizarCuentaReserva->ejecutar(
            reserva: $reserva,
            cuenta: $cuenta,
            usuarioId: is_numeric($datos['usuario_id'] ?? null) ? (int) $datos['usuario_id'] : null,
        );
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function datosParaRecalculo(Reserva $reserva, array $datos): array
    {
        return [
            'tipo_reserva' => is_string($datos['tipo_reserva'] ?? null)
                ? $datos['tipo_reserva']
                : $reserva->tipo_reserva->value,
            'habitacion_id' => $datos['habitacion_id'] ?? $reserva->habitacion_id,
            'espacio_id' => $datos['espacio_id'] ?? $reserva->espacio_id,
            'servicio_id' => $datos['servicio_id'] ?? $reserva->servicio_id,
            'fecha_check_in' => $datos['fecha_check_in'] ?? $reserva->fecha_check_in?->format('Y-m-d'),
            'fecha_check_out' => $datos['fecha_check_out'] ?? $reserva->fecha_check_out?->format('Y-m-d'),
            'hora_reserva' => $datos['hora_reserva'] ?? $reserva->hora_reserva,
            'duracion_horas' => $datos['duracion_horas'] ?? $this->reservas->duracionHorasActual($reserva),
            'adultos' => $datos['adultos'] ?? $reserva->adultos,
            'ninos' => $datos['ninos'] ?? $reserva->ninos,
            'servicios_adicionales' => $this->leerDato->arreglo($datos, 'servicios_adicionales'),
            'espacios_adicionales' => $this->leerDato->arreglo($datos, 'espacios_adicionales'),
            'habitaciones_adicionales' => $this->leerDato->arreglo($datos, 'habitaciones_adicionales'),
            'items_preorden' => $this->leerDato->arreglo($datos, 'items_preorden', $this->itemsPreordenActuales($reserva)),
            'promocion_id' => $datos['promocion_id'] ?? $reserva->promocion_id,
            'cargos_facturacion_ids' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param  array{tarifa_base: float, subtotal: float}  $resumen
     */
    private function actualizarDetallePrincipal(
        TipoReserva $tipo,
        ReservaDetalle $principal,
        int $reservableId,
        DateTimeImmutable $inicio,
        DateTimeImmutable $fin,
        array $datos,
        array $resumen,
    ): ReservaDetalle {
        $unidades = $this->unidadesRecalculo($tipo, $datos, $principal);

        if ($tipo === TipoReserva::RESTAURANTE) {
            return $this->reservas->actualizarDetalle($principal, [
                'reservable_id' => $reservableId,
                'estado' => EstadoReservaDetalle::CONFIRMADO,
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
                'adultos' => $this->leerDato->enteroOpcional($datos, 'adultos', 1),
                'ninos' => $this->leerDato->enteroOpcional($datos, 'ninos', 0),
                'precio_unitario' => round($resumen['subtotal'] / max(1, $unidades), 2),
                'subtotal' => round($resumen['subtotal'], 2),
            ]);

        }

        return $this->reservas->actualizarDetalle($principal, [
            'reservable_id' => $reservableId,
            'estado' => EstadoReservaDetalle::CONFIRMADO,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'adultos' => $this->leerDato->enteroOpcional($datos, 'adultos', 1),
            'ninos' => $this->leerDato->enteroOpcional($datos, 'ninos', 0),
            'precio_unitario' => round($resumen['tarifa_base'], 2),
            'subtotal' => round($resumen['tarifa_base'] * $unidades, 2),
        ]);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function unidadesRecalculo(TipoReserva $tipo, array $datos, ReservaDetalle $principal): int
    {
        if ($tipo === TipoReserva::RESTAURANTE) {
            $horas = is_numeric($datos['duracion_horas'] ?? null) ? (int) $datos['duracion_horas'] : null;
            if ($horas !== null && $horas > 0) {
                return $horas;
            }
        }

        if ($tipo === TipoReserva::HABITACION) {
            $inicio = $datos['fecha_check_in'] ?? null;
            $fin = $datos['fecha_check_out'] ?? null;

            if (is_string($inicio) && is_string($fin)) {
                try {
                    $dias = (new DateTimeImmutable($inicio))->diff(new DateTimeImmutable($fin))->days;

                    if ($dias > 0) {
                        return $dias;
                    }
                } catch (DateMalformedStringException) {
                    // Se recalcula con las unidades persistidas en el detalle.
                }
            }
        }

        $precioUnitario = (float) $principal->precio_unitario;

        if ($precioUnitario > 0) {
            return max(1, (int) round((float) $principal->subtotal / $precioUnitario));
        }

        return 1;
    }

    private function validarDisponibilidadPrincipal(Reserva $reserva, RecursoReservable $recurso, DateTimeImmutable $inicio, DateTimeImmutable $fin): void
    {
        $this->disponibilidadRecursos->bloquear($recurso->id);

        if ($recurso->control_disponibilidad !== ControlDisponibilidad::SIN_BLOQUEO
            && $this->disponibilidadRecursos->existeConflicto($recurso->id, $inicio, $fin, $reserva->id)) {
            throw new InvalidArgumentException('El recurso principal seleccionado no está disponible en el periodo indicado.');
        }
    }

    private function tipoPagoActualizado(float $totalPagado, float $saldo): TipoPagoReserva
    {
        if ($totalPagado <= 0) {
            return TipoPagoReserva::SIN_PAGO;
        }

        return $saldo <= 0 ? TipoPagoReserva::PAGO_COMPLETO : TipoPagoReserva::ABONO_50;
    }

    /** @return array<int, mixed> */
    private function itemsPreordenActuales(Reserva $reserva): array
    {
        $datos = $reserva->ultimaEntradaBitacora('preorden');
        $items = $datos['items'] ?? [];

        return is_array($items) ? array_values($items) : [];
    }
}
