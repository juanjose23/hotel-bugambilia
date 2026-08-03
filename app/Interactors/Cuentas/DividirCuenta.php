<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Cuentas\Cuenta;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Interactor para dividir el saldo pendiente de una cuenta:
 * 1. En N partes/monto fraccionado igual.
 * 2. Moviendo ítems específicos a una nueva sub-cuenta independiente.
 */
final readonly class DividirCuenta
{
    public function __construct(
        private AbrirCuenta $abrirCuenta,
        private RecalcularCuenta $recalcularCuenta,
    ) {}

    /**
     * Alias para división fraccionada en N partes.
     *
     * @return array<int, array{parte: int, subtotal: float, monto_total: float}>
     */
    public function ejecutar(Cuenta $cuenta, int $partes): array
    {
        return $this->ejecutarFraccionado($cuenta, $partes);
    }

    /**
     * Calcula una división equitativa en N partes del saldo de la cuenta.
     *
     * @return array<int, array{parte: int, subtotal: float, monto_total: float}>
     */
    public function ejecutarFraccionado(Cuenta $cuenta, int $partes): array
    {
        if ($partes < 2) {
            throw new DomainException('La cantidad de separaciones debe ser al menos 2 partes.');
        }

        $saldo = (float) $cuenta->saldo;
        if ($saldo <= 0) {
            throw new DomainException('La cuenta no posee saldo pendiente para dividir.');
        }

        $montoBasePorParte = round($saldo / $partes, 2);
        $diferenciaCentavos = round($saldo - ($montoBasePorParte * $partes), 2);

        $resultado = [];
        for ($i = 1; $i <= $partes; $i++) {
            // Ajustar la última parte con cualquier sobrante por redondeo de centavos
            $montoParte = ($i === $partes) ? round($montoBasePorParte + $diferenciaCentavos, 2) : $montoBasePorParte;

            $resultado[] = [
                'parte' => $i,
                'subtotal' => round($montoParte, 2),
                'monto_total' => round($montoParte, 2),
            ];
        }

        return $resultado;
    }

    /**
     * Mueve detalles de consumos específicos a una nueva sub-cuenta independiente.
     *
     * @param  Cuenta  $cuentaOrigen  Cuenta principal
     * @param  array<int, int>  $detallesIds  IDs de los detalles a mover
     * @param  int|null  $usuarioId  Usuario supervisor que ejecuta la división
     * @return array{cuenta_origen: Cuenta, cuenta_nueva: Cuenta}
     */
    public function ejecutarPorItems(Cuenta $cuentaOrigen, array $detallesIds, ?int $usuarioId = null): array
    {
        if (! $cuentaOrigen->estaAbierta()) {
            throw new DomainException('Sólo se pueden separar consumos de una cuenta en estado abierta.');
        }

        if ($detallesIds === []) {
            throw new DomainException('Debe seleccionar al menos un detalle de consumo para dividir la cuenta.');
        }

        return DB::transaction(function () use ($cuentaOrigen, $detallesIds, $usuarioId): array {
            $detallesMover = $cuentaOrigen->detalles()
                ->whereIn('id', $detallesIds)
                ->where('estado', EstadoGeneral::Activo->value)
                ->get();

            if ($detallesMover->isEmpty()) {
                throw new DomainException('No se encontraron ítems válidos para transferir a la nueva cuenta.');
            }

            // Crear nueva sub-cuenta independiente
            $nuevaCuenta = $this->abrirCuenta->ejecutar(
                tipo: $cuentaOrigen->tipo_cuenta,
                reserva: $cuentaOrigen->reserva,
                estancia: $cuentaOrigen->estancia,
                cliente: $cuentaOrigen->cliente,
                monedaId: $cuentaOrigen->moneda_id,
                usuarioId: $usuarioId,
            );

            // Reasignar los detalles a la nueva cuenta
            foreach ($detallesMover as $detalle) {
                $detalle->update([
                    'cuenta_id' => $nuevaCuenta->id,
                ]);
            }

            // Recalcular ambas cuentas
            $origenRecalculada = $this->recalcularCuenta->ejecutar($cuentaOrigen, $usuarioId);
            $nuevaRecalculada = $this->recalcularCuenta->ejecutar($nuevaCuenta, $usuarioId);

            return [
                'cuenta_origen' => $origenRecalculada,
                'cuenta_nueva' => $nuevaRecalculada,
            ];
        });
    }
}
