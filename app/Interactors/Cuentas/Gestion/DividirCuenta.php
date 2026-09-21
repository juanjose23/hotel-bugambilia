<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas\Gestion;

use App\BusinessLogic\Cuentas\Calculos\CalcularDivisionEquitativaCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\CuentaDetalle;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
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
        private CalcularDivisionEquitativaCuenta $calcularDivision,
        private CuentaRepositorioInterface $cuentaRepositorio,
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
        return $this->calcularDivision->calcular((float) $cuenta->saldo, $partes);
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
            $detallesMover = $this->cuentaRepositorio->obtenerDetallesActivosPorIds($cuentaOrigen, $detallesIds);

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
            $detallesIdsMover = $detallesMover->map(fn (CuentaDetalle $d): int => (int) $d->id)->all();
            $this->cuentaRepositorio->reasignarDetallesACuenta($detallesIdsMover, (int) $nuevaCuenta->id);

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
