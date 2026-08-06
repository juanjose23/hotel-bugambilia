<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cuentas;

use App\Enums\Cuentas\BaseCalculo;
use App\Enums\Cuentas\ModoCargo;
use App\Enums\Cuentas\TipoCargo;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Cuentas\Gestion\RecalcularCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\CuentaCargo;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final class AplicarDescuentoCuenta
{
    public function __construct(
        private readonly RestauranteRepositorioInterface $repositorio,
        private readonly CuentaRepositorioInterface $cuentas,
        private readonly RecalcularCuenta $recalcularCuenta,
    ) {}

    public function ejecutar(
        int $pedidoId,
        float $descuentoPorcentaje = 0.0,
        float $descuentoMonto = 0.0,
        ?string $motivo = null,
        ?int $usuarioId = null
    ): Pedido {
        if ($descuentoPorcentaje < 0 || $descuentoPorcentaje > 100) {
            throw new DomainException('El porcentaje de descuento debe estar entre 0% y 100%.');
        }

        if ($descuentoMonto < 0) {
            throw new DomainException('El monto de descuento no puede ser negativo.');
        }

        return DB::transaction(function () use ($pedidoId, $descuentoPorcentaje, $descuentoMonto, $motivo, $usuarioId): Pedido {
            $pedido = $this->repositorio->obtenerPedidoPorId($pedidoId);

            if (! $pedido instanceof Pedido) {
                throw new DomainException("Pedido #{$pedidoId} no encontrado.");
            }

            $cuenta = $pedido->cuenta;

            if ($cuenta === null) {
                throw new DomainException('Para aplicar un descuento el pedido debe estar asociado a una cuenta.');
            }

            $base = (float) $cuenta->subtotal > 0
                ? (float) $cuenta->subtotal
                : (float) $pedido->subtotal;

            $descuento = $descuentoPorcentaje > 0
                ? round($base * ($descuentoPorcentaje / 100), 2)
                : $descuentoMonto;

            $descuento = min($descuento, $base);

            if ($descuento <= 0) {
                $this->anularDescuentoManual($cuenta, $usuarioId);
            } else {
                $this->aplicarDescuentoManual($cuenta, $descuento, $motivo, $usuarioId);
            }

            // Primera pasada actualiza el descuento en la cabecera; la segunda
            // recalcula los cargos porcentuales (IVA/Servicio) sobre el subtotal neto.
            $this->recalcularCuenta->ejecutar($cuenta, $usuarioId);
            $this->recalcularCuenta->ejecutar($cuenta, $usuarioId);

            return $pedido->refresh();
        });
    }

    private function aplicarDescuentoManual(Cuenta $cuenta, float $descuento, ?string $motivo, ?int $usuarioId): void
    {
        $cargo = $this->descuentoManual($cuenta);

        if ($cargo instanceof CuentaCargo) {
            $this->cuentas->actualizarCuentaCargo($cargo, [
                'monto' => $descuento,
                'valor' => $descuento,
                'observaciones' => $motivo,
                'aplicado_por' => $usuarioId,
                'estado' => EstadoGeneral::Activo->value,
            ]);

            return;
        }

        $this->cuentas->crearCuentaCargo($cuenta, [
            'cargo_id' => null,
            'tipo' => TipoCargo::Descuento->value,
            'codigo' => 'DESC-MANUAL-'.$cuenta->id,
            'nombre' => 'Descuento manual',
            'modo_calculo' => ModoCargo::Manual->value,
            'valor' => $descuento,
            'base_calculo' => BaseCalculo::BaseManual->value,
            'base_monto' => 0.0,
            'monto' => $descuento,
            'observaciones' => $motivo,
            'aplicado_por' => $usuarioId,
            'estado' => EstadoGeneral::Activo->value,
        ]);
    }

    private function anularDescuentoManual(Cuenta $cuenta, ?int $usuarioId): void
    {
        $cargo = $this->descuentoManual($cuenta);

        if ($cargo instanceof CuentaCargo) {
            $this->cuentas->actualizarCuentaCargo($cargo, [
                'estado' => EstadoGeneral::Inactivo->value,
                'anulado_por' => $usuarioId,
            ]);
        }
    }

    private function descuentoManual(Cuenta $cuenta): ?CuentaCargo
    {
        return $this->cuentas->descuentoManualDeCuenta($cuenta);
    }
}
