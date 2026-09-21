<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas\Gestion;

use App\BusinessLogic\Cuentas\CalcularMontoCargo;
use App\BusinessLogic\Cuentas\Calculos\CalcularTotalesCuenta;
use App\Enums\Cuentas\TipoCargo;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;

/**
 * Recalcula y cachea los totales consolidados en la cabecera de la Cuenta
 * a partir de sus detalles (consumos) y cargos aplicados.
 */
final readonly class RecalcularCuenta
{
    public function __construct(
        private CuentaRepositorioInterface $cuentas,
        private CalcularMontoCargo $calcularMontoCargo,
        private CalcularTotalesCuenta $calcularTotales,
    ) {}

    public function ejecutar(Cuenta $cuenta, ?int $usuarioId = null): Cuenta
    {
        $cuenta = $this->cuentas->refrescar($cuenta);

        $subtotal = $this->cuentas->subtotalDetallesActivos($cuenta);
        $rawSubtotal = $cuenta->getRawOriginal('subtotal');
        if ($subtotal === 0.0 && is_numeric($rawSubtotal) && (float) $rawSubtotal > 0 && $this->cuentas->detallesActivos($cuenta)->isEmpty()) {
            $subtotal = (float) $rawSubtotal;
        }
        $descuentoTotal = $this->cuentas->sumaCargosActivos($cuenta, TipoCargo::Descuento);
        $cuenta->setAttribute('descuento_total', $descuentoTotal);

        $this->sincronizarCargosObligatorios($cuenta, $subtotal, $usuarioId);

        $cargosPorTipo = $this->cuentas->cargosPorTipoActivos($cuenta);

        $descuentoTotal = $this->aFloat($cargosPorTipo->get((string) TipoCargo::Descuento->value));
        $impuestoTotal = $this->aFloat($cargosPorTipo->get((string) TipoCargo::Impuesto->value));
        $servicioTotal = $this->aFloat($cargosPorTipo->get((string) TipoCargo::Servicio->value));
        $propinaTotal = $this->aFloat($cargosPorTipo->get((string) TipoCargo::Propina->value));
        $recargoTotal = $this->aFloat($cargosPorTipo->get((string) TipoCargo::Recargo->value));
        $totalPagado = $this->cuentas->sumaPagosAplicados($cuenta);

        $resultado = $this->calcularTotales->calcular(
            subtotal: $subtotal,
            descuentoTotal: $descuentoTotal,
            impuestoTotal: $impuestoTotal,
            servicioTotal: $servicioTotal,
            propinaTotal: $propinaTotal,
            recargoTotal: $recargoTotal,
            totalPagado: $totalPagado,
        );

        return $this->cuentas->actualizar($cuenta, [
            'subtotal' => $resultado->subtotal,
            'descuento_total' => $resultado->descuentoTotal,
            'impuesto_total' => $resultado->impuestoTotal,
            'cargo_servicio_total' => $resultado->servicioTotal,
            'propina_total' => $resultado->propinaTotal,
            'recargo_total' => $resultado->recargoTotal,
            'total' => $resultado->total,
            'total_pagado' => $resultado->totalPagado,
            'saldo' => $resultado->saldo,
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
        $cargosVigentes = $this->cuentas->cargosFacturacionVigentesConCargoId($cuenta)->keyBy('cargo_id');

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

        foreach ($actualizaciones as $cuentaCargoId => $datos) {
            $cuentaCargo = $cargosVigentes->first(fn ($c) => $c->id === $cuentaCargoId);
            if ($cuentaCargo !== null) {
                $this->cuentas->actualizarCuentaCargo($cuentaCargo, $datos);
            }
        }

        if ($nuevos !== []) {
            $this->cuentas->insertarCuentaCargos($cuenta, $nuevos);
        }
    }
}
