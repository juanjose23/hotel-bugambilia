<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas;

use App\BusinessLogic\Cuentas\ValidarCuenta;
use App\Enums\Cuentas\EstadoCuenta;
use App\Events\Cuentas\CuentaCerrada;
use App\Repository\Models\Cuentas\Cuenta;
use Illuminate\Support\Facades\DB;

/**
 * Cierra definitivamente una Cuenta con saldo liquidado.
 * Reemplaza: CerrarCuentaEstancia.
 */
final class CerrarCuenta
{
    public function __construct(
        private readonly ValidarCuenta $validarCuenta,
    ) {}

    public function ejecutar(Cuenta $cuenta, ?int $usuarioId = null): Cuenta
    {
        $this->validarCuenta->puedeCerrarse($cuenta);

        return DB::transaction(function () use ($cuenta, $usuarioId): Cuenta {
            $cuenta->update([
                'estado' => EstadoCuenta::CERRADA,
                'cerrada_at' => now(),
                'cerrada_por' => $usuarioId,
            ]);

            CuentaCerrada::dispatch($cuenta);

            return $cuenta->refresh();
        });
    }
}
