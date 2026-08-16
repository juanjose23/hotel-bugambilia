<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas\Cobros;

use App\Enums\Cuentas\EstadoPago;
use App\Interactors\Cuentas\Gestion\RecalcularCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final class AnularPagoCuenta
{
    public function __construct(
        private readonly RecalcularCuenta $recalcularCuenta,
        private readonly CuentaRepositorioInterface $cuentas,
    ) {}

    /**
     * Anula un pago aplicado previamente en una cuenta, registrando la razón de auditoría
     * y recalculando el saldo pendiente de la cuenta.
     *
     * @param  PagoCuenta  $pago  Pago que se desea anular
     * @param  string  $motivo  Motivo de anulación para auditoría
     * @param  int|null  $usuarioId  Usuario supervisor que autoriza la anulación
     */
    public function ejecutar(PagoCuenta $pago, string $motivo, ?int $usuarioId = null): Cuenta
    {
        if ($pago->estado === EstadoPago::ANULADO) {
            throw new DomainException('El pago ya se encuentra anulado.');
        }

        if (trim($motivo) === '') {
            throw new DomainException('Debe ingresar un motivo para anular el pago.');
        }

        return DB::transaction(function () use ($pago, $motivo, $usuarioId): Cuenta {
            $cuenta = $pago->cuenta;

            $this->cuentas->actualizarPago($pago, [
                'estado' => EstadoPago::ANULADO,
                'observaciones' => trim($pago->observaciones." [ANULADO por: {$motivo}]"),
                'usuario_id' => $usuarioId ?? $pago->usuario_id,
            ]);

            return $this->recalcularCuenta->ejecutar($cuenta, $usuarioId);
        });
    }
}
