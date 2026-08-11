<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Gestion;

use App\BusinessLogic\Reservas\CalcularResumenRestauranteLogica;
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
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Queries\Cuentas\ObtenerCuentaReservaQuery;
use App\Repository\Queries\Reservas\CalcularVistaPreviaFinancieraReservaQuery;
use App\Repository\Queries\Reservas\DisponibilidadRecursoQuery;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerDatosPedidoFormQuery;
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
        private readonly ObtenerDatosPedidoFormQuery $datosPedidoForm,
        private readonly ObtenerCuentaReservaQuery $obtenerCuentaReserva,
        private readonly SincronizarCuentaReserva $sincronizarCuentaReserva,
        private readonly CalcularResumenRestauranteLogica $calcularResumenRestauranteLogica,
        private readonly AbrirCuenta $abrirCuenta,
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
            $tipoRaw = $datosRecalculo['tipo_reserva'] ?? TipoReserva::HABITACION;
            $tipoStr = is_string($tipoRaw) ? $tipoRaw : ($tipoRaw instanceof TipoReserva ? $tipoRaw->value : TipoReserva::HABITACION->value);
            $tipo = TipoReserva::from($tipoStr);
            $entidadPrincipalId = $this->idEntidadPrincipal($tipo, $datosRecalculo);
            $espaciosSolicitados = $this->arrayDato($datos, 'espacios_adicionales');

            if ($tipo === TipoReserva::RESTAURANTE) {
                $espaciosSolicitados = $this->calcularResumenRestauranteLogica->completarEspaciosSugeridos(
                    $entidadPrincipalId,
                    $datosRecalculo,
                    $espaciosSolicitados,
                    $this->arrayDato($datosRecalculo, 'items_preorden'),
                );
                $datosRecalculo['espacios_adicionales'] = $espaciosSolicitados;
            }

            $servicios = $this->validarAdicionales->resolverServicios(
                $this->arrayDato($datos, 'servicios_adicionales'),
            );
            $espacios = $this->validarAdicionales->resolverEspacios(
                $espaciosSolicitados,
            );
            $habitaciones = $this->validarAdicionales->resolverHabitaciones(
                $this->arrayDato($datos, 'habitaciones_adicionales'),
                $tipo === TipoReserva::HABITACION ? $entidadPrincipalId : null,
            );

            $principal = $this->reservas->detallePrincipalDe($reserva);
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

            $this->validarFechas->validar($checkIn, $horaReserva);
            $recursoPrincipal = $this->reservas->resolverRecurso($tipo, $entidadPrincipalId);
            [$inicio, $fin] = $this->periodo($tipo, $checkIn, $checkOut, $datosRecalculo, $recursoPrincipal->duracion_minutos);

            $this->validarDisponibilidadAdicionales($reserva, $inicio, $fin, $servicios, $espacios, $habitaciones);
            $this->validarDisponibilidadPrincipal($reserva, $recursoPrincipal->id, $inicio, $fin);

            $resumen = $this->calcularVistaPrevia->ejecutar($datosRecalculo);

            $principal = $this->actualizarDetallePrincipal($tipo, $principal, $recursoPrincipal->id, $inicio, $fin, $datosRecalculo, $resumen);
            $this->reservas->reemplazarAdicionales($reserva, $principal, $servicios, $espacios, $habitaciones);
            $total = (float) $resumen['total'];
            $totalPagado = (float) $reserva->total_pagado;
            $saldo = round(max(0.0, $total - $totalPagado), 2);

            $reserva = $this->reservas->actualizar($reserva, [
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
                'adultos' => $this->enteroOpcional($datosRecalculo, 'adultos', 1),
                'ninos' => $this->enteroOpcional($datosRecalculo, 'ninos', 0),
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
                'meta_datos' => $this->metaDatosActualizados($reserva, $datosRecalculo),
            ]);

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
                    cliente: $reserva->cliente?->persona,
                    monedaId: $reserva->moneda_id,
                    usuarioId: is_numeric($datos['usuario_id'] ?? null) ? (int) $datos['usuario_id'] : null,
                );
            }

            return $this->sincronizarCuentaReserva->ejecutar(
                reserva: $reserva,
                cuenta: $cuenta,
                usuarioId: is_numeric($datos['usuario_id'] ?? null) ? (int) $datos['usuario_id'] : null,
            );
        });
    }

    /**
     * @param  array<int, array{servicio_id: int, cantidad: int, precio: float}>  $servicios
     * @param  array<int, array{espacio_id: int, cantidad: int, precio: float}>  $espacios
     * @param  array<int, array{habitacion_id: int, cantidad: int, precio: float}>  $habitaciones
     */
    private function validarDisponibilidadAdicionales(
        Reserva $reserva,
        DateTimeImmutable $inicio,
        DateTimeImmutable $fin,
        array $servicios,
        array $espacios,
        array $habitaciones = [],
    ): void {
        foreach ($habitaciones as $hab) {
            $recurso = $this->reservas->resolverRecurso(TipoReserva::HABITACION, $hab['habitacion_id']);
            $this->disponibilidadRecursos->bloquear($recurso->id);

            if ($recurso->control_disponibilidad !== ControlDisponibilidad::SIN_BLOQUEO
                && $this->disponibilidadRecursos->existeConflicto($recurso->id, $inicio, $fin, $reserva->id)) {
                throw new InvalidArgumentException("La habitación adicional {$recurso->nombre} no está disponible en las fechas indicadas.");
            }
        }

        foreach ($servicios as $servicio) {
            $recurso = $this->reservas->resolverRecurso(TipoReserva::SERVICIO, $servicio['servicio_id']);
            $this->disponibilidadRecursos->bloquear($recurso->id);

            if ($recurso->control_disponibilidad !== ControlDisponibilidad::SIN_BLOQUEO
                && $this->disponibilidadRecursos->existeConflicto($recurso->id, $inicio, $fin, $reserva->id)) {
                throw new InvalidArgumentException("El servicio {$recurso->nombre} no está disponible en el horario especificado.");
            }
        }

        foreach ($espacios as $espacio) {
            $recurso = $this->reservas->resolverRecurso(TipoReserva::RESTAURANTE, $espacio['espacio_id']);
            $this->disponibilidadRecursos->bloquear($recurso->id);

            if ($recurso->control_disponibilidad !== ControlDisponibilidad::SIN_BLOQUEO
                && $this->disponibilidadRecursos->existeConflicto($recurso->id, $inicio, $fin, $reserva->id)) {
                throw new InvalidArgumentException("El espacio {$recurso->nombre} no está disponible en el periodo solicitado.");
            }
        }
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
            'duracion_horas' => $datos['duracion_horas'] ?? $this->duracionHorasActual($reserva),
            'adultos' => $datos['adultos'] ?? $reserva->adultos,
            'ninos' => $datos['ninos'] ?? $reserva->ninos,
            'servicios_adicionales' => $this->arrayDato($datos, 'servicios_adicionales'),
            'espacios_adicionales' => $this->arrayDato($datos, 'espacios_adicionales'),
            'habitaciones_adicionales' => $this->arrayDato($datos, 'habitaciones_adicionales'),
            'items_preorden' => $this->arrayDato($datos, 'items_preorden', $this->itemsPreordenActuales($reserva)),
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
                'adultos' => $this->enteroOpcional($datos, 'adultos', 1),
                'ninos' => $this->enteroOpcional($datos, 'ninos', 0),
                'precio_unitario' => round($resumen['subtotal'] / max(1, $unidades), 2),
                'subtotal' => round($resumen['subtotal'], 2),
            ]);

        }

        return $this->reservas->actualizarDetalle($principal, [
            'reservable_id' => $reservableId,
            'estado' => EstadoReservaDetalle::CONFIRMADO,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'adultos' => $this->enteroOpcional($datos, 'adultos', 1),
            'ninos' => $this->enteroOpcional($datos, 'ninos', 0),
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

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<int, mixed>  $default
     * @return array<int, mixed>
     */
    private function arrayDato(array $datos, string $campo, array $default = []): array
    {
        $valor = $datos[$campo] ?? null;

        return is_array($valor) ? array_values($valor) : $default;
    }

    private function validarDisponibilidadPrincipal(Reserva $reserva, int $recursoId, DateTimeImmutable $inicio, DateTimeImmutable $fin): void
    {
        $this->disponibilidadRecursos->bloquear($recursoId);

        if ($this->disponibilidadRecursos->existeConflicto($recursoId, $inicio, $fin, $reserva->id)) {
            throw new InvalidArgumentException('El recurso principal seleccionado no está disponible en el periodo indicado.');
        }
    }

    /** @param array<string, mixed> $datos */
    private function idEntidadPrincipal(TipoReserva $tipo, array $datos): int
    {
        $campo = match ($tipo) {
            TipoReserva::HABITACION => 'habitacion_id',
            TipoReserva::RESTAURANTE => 'espacio_id',
            TipoReserva::SERVICIO => 'servicio_id',
            TipoReserva::PAQUETE => 'paquete_id',
        };

        $valor = $datos[$campo] ?? null;
        if (! is_numeric($valor) || (int) $valor <= 0) {
            throw new InvalidArgumentException("El campo {$campo} no es válido.");
        }

        return (int) $valor;
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array{0: DateTimeImmutable, 1: DateTimeImmutable}
     */
    private function periodo(TipoReserva $tipo, DateTimeImmutable $checkIn, ?DateTimeImmutable $checkOut, array $datos, ?int $duracionMinutos): array
    {
        $hora = is_string($datos['hora_reserva'] ?? null) ? trim((string) $datos['hora_reserva']) : '00:00';
        $inicio = new DateTimeImmutable($checkIn->format('Y-m-d').' '.($hora !== '' ? $hora : '00:00'));

        if ($tipo === TipoReserva::HABITACION) {
            $salida = $checkOut ?? $checkIn->modify('+1 day');

            return [$inicio, new DateTimeImmutable($salida->format('Y-m-d').' 00:00')];
        }

        if (is_numeric($datos['duracion_horas'] ?? null)) {
            return [$inicio, $inicio->modify('+'.max(1, (int) $datos['duracion_horas']).' hours')];
        }

        return [$inicio, $inicio->modify('+'.max(1, $duracionMinutos ?? 60).' minutes')];
    }

    /** @param array<string, mixed> $datos */
    private function enteroOpcional(array $datos, string $campo, int $predeterminado): int
    {
        return is_numeric($datos[$campo] ?? null) ? (int) $datos[$campo] : $predeterminado;
    }

    private function tipoPagoActualizado(float $totalPagado, float $saldo): TipoPagoReserva
    {
        if ($totalPagado <= 0) {
            return TipoPagoReserva::SIN_PAGO;
        }

        return $saldo <= 0 ? TipoPagoReserva::PAGO_COMPLETO : TipoPagoReserva::ABONO_50;
    }

    private function duracionHorasActual(Reserva $reserva): ?int
    {
        $principal = $reserva->detalles()->whereNull('parent_id')->first();

        if ($principal === null || $principal->fecha_fin === null) {
            return null;
        }

        return max(1, (int) ceil(($principal->fecha_fin->getTimestamp() - $principal->fecha_inicio->getTimestamp()) / 3600));
    }

    /** @return array<int, mixed> */
    private function itemsPreordenActuales(Reserva $reserva): array
    {
        $meta = is_array($reserva->meta_datos) ? $reserva->meta_datos : [];
        $items = $meta['platos_preordenados'] ?? [];

        return is_array($items) ? array_values($items) : [];
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function metaDatosActualizados(Reserva $reserva, array $datos): array
    {
        $meta = is_array($reserva->meta_datos) ? $reserva->meta_datos : [];
        $items = [];

        foreach ($this->arrayDato($datos, 'items_preorden') as $item) {
            if (! is_array($item) || ! is_numeric($item['plato_id'] ?? null)) {
                continue;
            }

            $platoId = (int) $item['plato_id'];
            $precioValor = $item['precio_unitario'] ?? null;
            $precio = is_numeric($precioValor) && (float) $precioValor > 0
                ? (float) $precioValor
                : ($this->datosPedidoForm->precioActualDePlato($platoId) ?? 0.0);
            $cantidad = max(1, is_numeric($item['cantidad'] ?? null) ? (int) $item['cantidad'] : 1);
            $plato = Plato::query()->find($platoId);

            $items[] = [
                'plato_id' => $platoId,
                'nombre' => $plato->nombre ?? "Platillo #{$platoId}",
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
                'subtotal' => round($precio * $cantidad, 2),
                'observaciones' => is_string($item['observaciones'] ?? null) ? trim((string) $item['observaciones']) : null,
            ];
        }

        $meta['platos_preordenados'] = $items;

        return $meta;
    }
}
