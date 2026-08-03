<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas;

use App\Enums\Cuentas\BaseCalculo;
use App\Enums\Cuentas\ModoCargo;
use App\Enums\Cuentas\TipoCargo;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final class AplicarPropinaCuenta
{
    public function __construct(
        private readonly CuentaRepositorioInterface $cuentas,
        private readonly RecalcularCuenta $recalcularCuenta,
    ) {}

    /**
     * Aplica o actualiza la propina voluntaria en una cuenta activa.
     *
     * @param  Cuenta  $cuenta  Cuenta activa sobre la cual aplicar propina
     * @param  float  $porcentajeOMonto  Monto fijo o porcentaje (ej. 10.0 para 10%)
     * @param  bool  $esPorcentaje  true si es porcentaje, false si es monto fijo
     * @param  int|null  $usuarioId  Usuario que aplica la propina
     */
    public function ejecutar(
        Cuenta $cuenta,
        float $porcentajeOMonto,
        bool $esPorcentaje = true,
        ?int $usuarioId = null,
    ): Cuenta {
        if (! $cuenta->estaAbierta()) {
            throw new DomainException('Sólo se puede modificar la propina en una cuenta abierta.');
        }

        if ($porcentajeOMonto < 0.0) {
            throw new DomainException('El valor de la propina no puede ser negativo.');
        }

        return DB::transaction(function () use ($cuenta, $porcentajeOMonto, $esPorcentaje, $usuarioId): Cuenta {
            $subtotal = (float) $cuenta->subtotal;
            $modo = $esPorcentaje ? ModoCargo::Porcentaje : ModoCargo::MontoFijo;
            $montoCalculado = $esPorcentaje
                ? round($subtotal * ($porcentajeOMonto / 100), 2)
                : round($porcentajeOMonto, 2);

            $propinaExistente = $cuenta->cargos()
                ->where('tipo', TipoCargo::Propina->value)
                ->where('estado', EstadoGeneral::Activo->value)
                ->first();

            if ($porcentajeOMonto === 0.0) {
                if ($propinaExistente !== null) {
                    $this->cuentas->actualizarCuentaCargo($propinaExistente, [
                        'estado' => EstadoGeneral::Inactivo->value,
                        'monto' => 0.0,
                    ]);
                }
            } else {
                if ($propinaExistente !== null) {
                    $this->cuentas->actualizarCuentaCargo($propinaExistente, [
                        'modo_calculo' => $modo->value,
                        'valor' => $porcentajeOMonto,
                        'base_monto' => $subtotal,
                        'monto' => $montoCalculado,
                        'aplicado_por' => $usuarioId,
                        'estado' => EstadoGeneral::Activo->value,
                    ]);
                } else {
                    $this->cuentas->crearCuentaCargo($cuenta, [
                        'moneda_id' => $cuenta->moneda_id,
                        'tipo' => TipoCargo::Propina->value,
                        'codigo' => 'PROPINA-VOLUNTARIA',
                        'nombre' => 'Propina Voluntaria '.($esPorcentaje ? "({$porcentajeOMonto}%)" : ''),
                        'modo_calculo' => $modo->value,
                        'valor' => $porcentajeOMonto,
                        'base_calculo' => BaseCalculo::SubtotalBruto->value,
                        'base_monto' => $subtotal,
                        'monto' => $montoCalculado,
                        'aplicado_por' => $usuarioId,
                        'estado' => EstadoGeneral::Activo->value,
                        'observaciones' => 'Propina registrada voluntariamente por el cliente',
                    ]);
                }
            }

            return $this->recalcularCuenta->ejecutar($cuenta, $usuarioId);
        });
    }
}
