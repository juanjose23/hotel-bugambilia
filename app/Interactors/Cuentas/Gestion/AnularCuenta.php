<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas\Gestion;

use App\Enums\Cuentas\EstadoCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;

final class AnularCuenta
{
    public function __construct(
        private readonly CuentaRepositorioInterface $cuentas,
    ) {}

    public function ejecutar(Cuenta $cuenta, string $motivo, ?int $usuarioId = null): Cuenta
    {
        return $this->cuentas->actualizar($cuenta, [
            'estado' => EstadoCuenta::ANULADA,
            'cerrada_at' => now(),
            'cerrada_por' => $usuarioId,
            'notas' => trim(($cuenta->notas ?? '')." | Cuenta Anulada: {$motivo}"),
        ]);
    }
}
