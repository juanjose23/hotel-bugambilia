<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas\Gestion;

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

        $cargosPorTipo = $cuenta->cargos()
            ->where('estado', EstadoGeneral::Activo->value)
            ->selectRaw('tipo, SUM(monto) as total_monto')
            ->groupBy('tipo')
            ->pluck('total_monto', 'tipo');

        $descuentoTotal = $this->aFloat($cargosPorTipo->get(TipoCargo::Descuento->value));
        $impuestoTotal = $this->aFloat($cargosPorTipo->get(TipoCargo::Impuesto->value));
        $servicioTotal = $this->aFloat($cargosPorTipo->get(TipoCargo::Servicio->value));
        $propinaTotal = $this->aFloat($cargosPorTipo->get(TipoCargo::Propina->value));
        $recargoTotal = $this->aFloat($cargosPorTipo->get(TipoCargo::Recargo->value));

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

    private function aFloat(mixed $valor): float
    {
        if (is_numeric($valor)) {
            return (float) $valor;
        }

        return 0.0;
    }

    private function sincronizarCargosObligatorios(Cuenta $cuenta, float $subtotal, ?int $usuarioId): void
    {
        $cargosObligatorios = $this->cuentas->cargosFacturacionObligatorios();
        $cargosVigentes = $cuenta->cargos()
            ->where('estado', EstadoGeneral::Activo->value)
            ->whereNotNull('cargo_id')
            ->get()
            ->keyBy('cargo_id');

        $actualizaciones = [];   // [id => datos] para UPDATE masivo
        $nuevos = [];   // filas para INSERT masivo

        foreach ($cargosObligatorios as $cargo) {
            $calculo = $this->calcularMontoCargo->calcular($cargo, $cuenta, $subtotal);
            $baseMonto = $calculo['base'];
            $monto = $calculo['monto'];
            $valor = (float) $cargo->valor;

            $cuentaCargo = $cargosVigentes->get($cargo->id);

            if ($cuentaCargo !== null) {
                $actualizaciones[$cuentaCargo->id] = [
                    'base_monto' => $baseMonto,
                    'monto' => $monto,
                    'valor' => $valor,
                ];
            } else {
                $nuevos[] = [
                    'cuenta_id' => $cuenta->id,
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
                ];
            }
        }

        // UPDATE masivo: una query por cada cargo existente (WhereIn no disponible en la interfaz; actualizar directamente)
        foreach ($actualizaciones as $cuentaCargoId => $datos) {
            $cuentaCargo = $cargosVigentes->first(fn ($c) => $c->id === $cuentaCargoId);
            if ($cuentaCargo !== null) {
                $this->cuentas->actualizarCuentaCargo($cuentaCargo, $datos);
            }
        }

        // INSERT masivo: una sola query para todos los nuevos cargos
        if ($nuevos !== []) {
            $cuenta->cargos()->insert($nuevos);
        }
    }
}
