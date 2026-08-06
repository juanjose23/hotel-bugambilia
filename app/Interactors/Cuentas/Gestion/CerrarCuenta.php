<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas\Gestion;

use App\BusinessLogic\Cuentas\ValidarCuenta;
use App\Enums\Cuentas\EstadoCuenta;
use App\Events\Cuentas\CuentaCerrada;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use Illuminate\Support\Facades\DB;

/**
 * Cierra definitivamente una Cuenta con saldo liquidado.
 * Reemplaza: CerrarCuentaEstancia.
 */
final class CerrarCuenta
{
    public function __construct(
        private readonly ValidarCuenta $validarCuenta,
        private readonly CuentaRepositorioInterface $cuentas,
    ) {}

    public function ejecutar(Cuenta $cuenta, ?int $usuarioId = null): Cuenta
    {
        $this->validarCuenta->puedeCerrarse($cuenta);

        return DB::transaction(function () use ($cuenta, $usuarioId): Cuenta {
            $cuenta = $this->cuentas->actualizar($cuenta, [
                'estado' => EstadoCuenta::CERRADA,
                'cerrada_at' => now(),
                'cerrada_por' => $usuarioId,
            ]);

            CuentaCerrada::dispatch($cuenta);

            return $cuenta;
        });
    }
}
