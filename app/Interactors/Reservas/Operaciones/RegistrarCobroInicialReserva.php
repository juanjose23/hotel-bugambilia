<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Operaciones;

use App\BusinessLogic\Cuentas\CalcularMontoCargo;
use App\BusinessLogic\Monedas\ConvertirMoneda;
use App\Enums\Cuentas\BaseCalculo;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Cuentas\ModoCargo;
use App\Enums\Cuentas\TipoCargo;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Cuentas\Cobros\RegistrarPagoCuenta;
use App\Interactors\Cuentas\Gestion\AbrirCuenta;
use App\Interactors\Cuentas\Gestion\RecalcularCuenta;
use App\Interactors\Cuentas\Gestion\RegistrarDetalleCuenta;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use DomainException;

final class RegistrarCobroInicialReserva
{
    public function __construct(
        private readonly AbrirCuenta $abrirCuenta,
        private readonly RegistrarDetalleCuenta $registrarDetalle,
        private readonly RegistrarPagoCuenta $registrarPago,
        private readonly RecalcularCuenta $recalcularCuenta,
        private readonly CalcularMontoCargo $calcularMontoCargo,
        private readonly ConvertirMoneda $convertirMoneda,
        private readonly CuentaRepositorioInterface $cuentas,
        private readonly ReservaRepositorioInterface $reservas,
    ) {}

    /**
     * @param  array<int, int|string>  $cargosFacturacionIds
     */
    public function ejecutar(
        Reserva $reserva,
        TipoPagoReserva $tipoPago,
        ?int $monedaId,
        ?MetodoPago $metodoPago,
        ?string $referencia,
        ?int $usuarioId,
        ?float $montoSolicitado = null,
        array $cargosFacturacionIds = [],
    ): Reserva {
        $monedaId ??= $this->cuentas->monedaPredeterminada()?->id;

        if ($monedaId === null) {
            throw new DomainException('Debe existir una moneda configurada para registrar la reserva.');
        }

        $subtotalReserva = $this->convertirMoneda->desdeBase((float) $reserva->subtotal, $monedaId);
        $descuentoReserva = $this->convertirMoneda->desdeBase((float) $reserva->descuento, $monedaId);
        $reserva->load('cliente.persona');
        $cuenta = $this->abrirCuenta->ejecutar(
            tipo: $this->tipoCuenta($reserva->tipo_reserva),
            reserva: $reserva,
            cliente: $reserva->cliente,
            monedaId: $monedaId,
            usuarioId: $usuarioId,
        );

        $this->registrarDetalle->ejecutar(
            cuenta: $cuenta,
            concepto: "Reserva {$reserva->codigo_reserva}",
            precioUnitario: $subtotalReserva,
            origen: $reserva,
            espacioId: $this->enteroOpcional($reserva->espacio_id),
            creadorId: $usuarioId,
            descripcion: $reserva->tipo_reserva->getLabel(),
            metadatos: ['tipo' => 'reserva', 'codigo_reserva' => $reserva->codigo_reserva],
        );

        $cuenta->refresh();
        if ($descuentoReserva > 0) {
            $this->cuentas->crearCuentaCargo($cuenta, [
                'moneda_id' => $cuenta->moneda_id,
                'cargo_id' => null,
                'tipo' => TipoCargo::Descuento->value,
                'codigo' => "PROMO-RES-{$reserva->id}",
                'nombre' => 'Descuento de reserva',
                'modo_calculo' => ModoCargo::MontoFijo->value,
                'valor' => $descuentoReserva,
                'base_calculo' => BaseCalculo::SubtotalBruto->value,
                'base_monto' => $subtotalReserva,
                'monto' => $descuentoReserva,
                'aplicado_por' => $usuarioId,
                'estado' => EstadoGeneral::Activo->value,
                'observaciones' => 'Descuento aplicado al crear la reserva',
            ]);
            $cuenta = $this->recalcularCuenta->ejecutar($cuenta, $usuarioId);
        }

        $idsSeleccionados = array_map('intval', $cargosFacturacionIds);
        $cargosVigentesIds = $this->cuentas->cargosFacturacionVigentesIds($cuenta)->flip();

        $nuevosCargos = [];
        foreach ($this->cuentas->cargosFacturacionActivos() as $cargo) {
            if (! $cargo->obligatorio && ! in_array($cargo->id, $idsSeleccionados, true)) {
                continue;
            }

            if ($cargosVigentesIds->has($cargo->id)) {
                continue;
            }

            $calculo = $this->calcularMontoCargo->calcular($cargo, $cuenta, (float) $cuenta->subtotal);
            $nuevosCargos[] = [
                'cuenta_id' => $cuenta->id,
                'moneda_id' => $cuenta->moneda_id,
                'cargo_id' => $cargo->id,
                'tipo' => $cargo->tipo->value,
                'codigo' => $cargo->codigo,
                'nombre' => $cargo->nombre,
                'modo_calculo' => $cargo->modo_calculo->value,
                'valor' => $cargo->valor,
                'base_calculo' => $cargo->base_calculo->value,
                'base_monto' => $calculo['base'],
                'monto' => $calculo['monto'],
                'aplicado_por' => $usuarioId,
                'estado' => EstadoGeneral::Activo->value,
                'observaciones' => 'Cargo seleccionado al crear la reserva',
            ];
        }

        // INSERT masivo: una sola query para todos los cargos nuevos
        if ($nuevosCargos !== []) {
            $this->cuentas->insertarCuentaCargos($cuenta, $nuevosCargos);
        }

        $cuenta = $this->recalcularCuenta->ejecutar($cuenta, $usuarioId);
        $totalCuenta = (float) $cuenta->total;
        $montoPago = $tipoPago === TipoPagoReserva::SIN_PAGO
            ? 0.0
            : round($montoSolicitado ?? $tipoPago->monto($totalCuenta), 2);

        if ($montoPago < 0) {
            throw new DomainException('El monto recibido no puede ser negativo.');
        }

        if ($montoPago > $totalCuenta) {
            throw new DomainException('El monto recibido no puede ser mayor que el total pendiente de la reserva.');
        }

        if ($tipoPago === TipoPagoReserva::PAGO_COMPLETO && abs($montoPago - $totalCuenta) > 0.009) {
            throw new DomainException('Para registrar un pago completo debe ingresar exactamente el total pendiente de la reserva.');
        }

        if ($montoPago > 0 && abs($montoPago - $totalCuenta) <= 0.009) {
            $tipoPago = TipoPagoReserva::PAGO_COMPLETO;
        }

        if ($montoPago > 0) {
            if ($metodoPago === null) {
                throw new DomainException('Seleccione una forma de pago para registrar el cobro de la reserva.');
            }

            $this->registrarPago->ejecutar(
                cuenta: $cuenta,
                metodoPago: $metodoPago,
                monto: $montoPago,
                referenciaTransaccion: $referencia,
                observaciones: $tipoPago === TipoPagoReserva::ABONO_50
                    ? "Abono inicial de la reserva {$reserva->codigo_reserva}"
                    : "Pago completo de la reserva {$reserva->codigo_reserva}",
                monedaId: $monedaId,
                usuarioId: $usuarioId,
            );
        }

        $cuenta->refresh();

        return $this->reservas->actualizar($reserva, [
            'moneda_id' => $monedaId,
            'tipo_pago' => $tipoPago,
            'subtotal' => $cuenta->subtotal,
            'total' => $cuenta->total,
            'total_pagado' => $cuenta->total_pagado,
            'saldo' => $cuenta->saldo,
            'estado' => $tipoPago === TipoPagoReserva::SIN_PAGO
                ? EstadoReserva::PENDIENTE
                : EstadoReserva::CONFIRMADA,
        ]);
    }

    private function tipoCuenta(TipoReserva $tipoReserva): TipoCuenta
    {
        return match ($tipoReserva) {
            TipoReserva::HABITACION => TipoCuenta::ESTANCIA,
            TipoReserva::RESTAURANTE => TipoCuenta::RESTAURANTE_DIRECTO,
            TipoReserva::SERVICIO, TipoReserva::PAQUETE => TipoCuenta::SERVICIO,
        };
    }

    private function enteroOpcional(mixed $valor): ?int
    {
        if (is_int($valor)) {
            return $valor > 0 ? $valor : null;
        }

        if (is_string($valor) && ctype_digit($valor)) {
            $entero = (int) $valor;

            return $entero > 0 ? $entero : null;
        }

        return null;
    }
}
