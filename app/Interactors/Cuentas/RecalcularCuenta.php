<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas;

use App\BusinessLogic\Cuentas\CalcularMontoCargo;
use App\Enums\Cuentas\TipoCargo;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;

/**
 * Recalcula y cachea los totales consolidados en la cabecera de la Cuenta
 * a partir de sus detalles (consumos) y cargos aplicados.
 *
 * Fórmulas:
 *   subtotal       = SUM(cuenta_detalles.subtotal WHERE estado = activo)
 *   descuento_total = SUM(cuenta_cargos.monto WHERE tipo = Descuento AND estado = activo)
 *   impuesto_total  = SUM(cuenta_cargos.monto WHERE tipo = Impuesto AND estado = activo)
 *   servicio_total  = SUM(cuenta_cargos.monto WHERE tipo = Servicio AND estado = activo)
 *   propina_total   = SUM(cuenta_cargos.monto WHERE tipo = Propina AND estado = activo)
 *   recargo_total   = SUM(cuenta_cargos.monto WHERE tipo = Recargo AND estado = activo)
 *   total           = subtotal - descuento_total + impuesto_total + servicio_total + propina_total + recargo_total
 *   total_pagado    = SUM(pagos.monto WHERE estado = APLICADO)
 *   saldo           = total - total_pagado
 */
final class RecalcularCuenta
{
    public function __construct(
        private readonly CuentaRepositorioInterface $cuentas,
        private readonly CalcularMontoCargo $calcularMontoCargo,
    ) {}

    public function ejecutar(Cuenta $cuenta, ?int $usuarioId = null): Cuenta
    {
        $cuenta = $this->cuentas->refrescar($cuenta);

        $subtotal = $this->cuentas->subtotalDetallesActivos($cuenta);
        $descuentoTotal = $this->cuentas->sumaCargosActivos($cuenta, TipoCargo::Descuento);
        $cuenta->setAttribute('descuento_total', $descuentoTotal);

        $this->sincronizarCargosObligatorios($cuenta, $subtotal, $usuarioId);

        $descuentoTotal = $this->cuentas->sumaCargosActivos($cuenta, TipoCargo::Descuento);
        $impuestoTotal = $this->cuentas->sumaCargosActivos($cuenta, TipoCargo::Impuesto);
        $servicioTotal = $this->cuentas->sumaCargosActivos($cuenta, TipoCargo::Servicio);
        $propinaTotal = $this->cuentas->sumaCargosActivos($cuenta, TipoCargo::Propina);
        $recargoTotal = $this->cuentas->sumaCargosActivos($cuenta, TipoCargo::Recargo);

        $total = round(
            $subtotal
            - $descuentoTotal
            + $impuestoTotal
            + $servicioTotal
            + $propinaTotal
            + $recargoTotal,
            2
        );

        $totalPagado = $this->cuentas->sumaPagosAplicados($cuenta);

        $saldo = max(0, round($total - $totalPagado, 2));

        return $this->cuentas->actualizar($cuenta, [
            'subtotal' => $subtotal,
            'descuento_total' => $descuentoTotal,
            'impuesto_total' => $impuestoTotal,
            'cargo_servicio_total' => $servicioTotal,
            'propina_total' => $propinaTotal,
            'recargo_total' => $recargoTotal,
            'total' => $total,
            'total_pagado' => $totalPagado,
            'saldo' => $saldo,
            'actualizado_por' => $usuarioId ?? $cuenta->actualizado_por,
        ]);
    }

    private function sincronizarCargosObligatorios(Cuenta $cuenta, float $subtotal, ?int $usuarioId): void
    {
        $cargosObligatorios = $this->cuentas->cargosFacturacionObligatorios();

        foreach ($cargosObligatorios as $cargo) {
            $valor = (float) $cargo->valor;
            $calculo = $this->calcularMontoCargo->calcular($cargo, $cuenta, $subtotal);
            $baseMonto = $calculo['base'];
            $monto = $calculo['monto'];

            $cuentaCargo = $this->cuentas->cuentaCargoVigente($cuenta, $cargo->id);

            if ($cuentaCargo !== null) {
                $this->cuentas->actualizarCuentaCargo($cuentaCargo, [
                    'base_monto' => $baseMonto,
                    'monto' => $monto,
                    'valor' => $valor,
                ]);
            } else {
                $this->cuentas->crearCuentaCargo($cuenta, [
                    'moneda_id' => $cuenta->moneda_id,
                    'cargo_id' => $cargo->id,
                    'tipo' => $cargo->tipo->value,
                    'codigo' => $cargo->codigo,
                    'nombre' => $cargo->nombre,
                    'modo_calculo' => $cargo->modo_calculo->value,
                    'valor' => $valor,
                    'base_calculo' => $cargo->base_calculo->value,
                    'base_monto' => $baseMonto,
                    'monto' => $monto,
                    'aplicado_por' => $usuarioId,
                    'estado' => EstadoGeneral::Activo->value,
                ]);
            }
        }
    }
}
