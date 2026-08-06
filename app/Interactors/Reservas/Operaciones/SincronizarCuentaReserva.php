<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Operaciones;

use App\BusinessLogic\Monedas\ConvertirMoneda;
use App\Enums\Cuentas\BaseCalculo;
use App\Enums\Cuentas\ModoCargo;
use App\Enums\Cuentas\TipoCargo;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Cuentas\Gestion\RecalcularCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;

final class SincronizarCuentaReserva
{
    public function __construct(
        private readonly CuentaRepositorioInterface $cuentas,
        private readonly ReservaRepositorioInterface $reservas,
        private readonly RecalcularCuenta $recalcularCuenta,
        private readonly ConvertirMoneda $convertirMoneda,
    ) {}

    public function ejecutar(Reserva $reserva, Cuenta $cuenta, ?int $usuarioId = null): Reserva
    {
        $subtotal = $this->convertirMoneda->desdeBase((float) $reserva->subtotal, (int) $cuenta->moneda_id);
        $descuento = $this->convertirMoneda->desdeBase((float) $reserva->descuento, (int) $cuenta->moneda_id);
        $detalle = $this->cuentas->detalleActivoConOrigen($cuenta, $reserva->getMorphClass(), (int) $reserva->id);

        $datosDetalle = [
            'moneda_id' => $cuenta->moneda_id,
            'origen_type' => $reserva->getMorphClass(),
            'origen_id' => $reserva->id,
            'espacio_id' => $reserva->espacio_id,
            'concepto' => "Reserva {$reserva->codigo_reserva}",
            'descripcion' => $reserva->tipo_reserva->getLabel(),
            'cantidad' => 1,
            'precio_unitario' => $subtotal,
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'estado' => EstadoGeneral::Activo->value,
            'metadatos' => ['tipo' => 'reserva', 'codigo_reserva' => $reserva->codigo_reserva],
            'creador_id' => $usuarioId,
        ];

        if ($detalle === null) {
            $this->cuentas->crearDetalle($cuenta, $datosDetalle);
        } else {
            $this->cuentas->actualizarDetalle($detalle, $datosDetalle);
        }

        $this->sincronizarDescuento($reserva, $cuenta, $descuento, $subtotal, $usuarioId);

        $cuenta = $this->recalcularCuenta->ejecutar($cuenta, $usuarioId);

        return $this->reservas->actualizar($reserva, [
            'subtotal' => $cuenta->subtotal,
            'total' => $cuenta->total,
            'total_pagado' => $cuenta->total_pagado,
            'saldo' => $cuenta->saldo,
        ]);
    }

    private function sincronizarDescuento(Reserva $reserva, Cuenta $cuenta, float $descuento, float $subtotal, ?int $usuarioId): void
    {
        $codigo = "PROMO-RES-{$reserva->id}";
        $cargo = $this->cuentas->cuentaCargoPorCodigo($cuenta, $codigo);

        if ($descuento <= 0) {
            if ($cargo !== null) {
                $this->cuentas->actualizarCuentaCargo($cargo, ['estado' => EstadoGeneral::Inactivo->value]);
            }

            return;
        }

        $datos = [
            'moneda_id' => $cuenta->moneda_id,
            'cargo_id' => null,
            'tipo' => TipoCargo::Descuento->value,
            'codigo' => $codigo,
            'nombre' => 'Descuento de reserva',
            'modo_calculo' => ModoCargo::MontoFijo->value,
            'valor' => $descuento,
            'base_calculo' => BaseCalculo::SubtotalBruto->value,
            'base_monto' => $subtotal,
            'monto' => $descuento,
            'aplicado_por' => $usuarioId,
            'estado' => EstadoGeneral::Activo->value,
            'observaciones' => 'Descuento sincronizado al editar la reserva',
        ];

        if ($cargo === null) {
            $this->cuentas->crearCuentaCargo($cuenta, $datos);

            return;
        }

        $this->cuentas->actualizarCuentaCargo($cargo, $datos);
    }
}
