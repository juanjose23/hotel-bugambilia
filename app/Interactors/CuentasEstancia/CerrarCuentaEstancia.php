<?php

declare(strict_types=1);

namespace App\Interactors\CuentasEstancia;

use App\BusinessLogic\CuentasEstancia\ValidarCuentaEstancia;
use App\Enums\Estancias\EstadoCuentaEstancia;
use App\Events\Estancias\CuentaEstanciaCerrada;
use App\Repository\Models\Estancias\CuentaEstancia;
use Illuminate\Support\Facades\DB;

final class CerrarCuentaEstancia
{
    public function __construct(
        private readonly ValidarCuentaEstancia $validarCuenta,
    ) {}

    public function ejecutar(CuentaEstancia $cuenta, ?int $usuarioId = null): CuentaEstancia
    {
        $this->validarCuenta->puedeCerrar($cuenta);

        return DB::transaction(function () use ($cuenta, $usuarioId): CuentaEstancia {
            $cuenta->update([
                'estado' => EstadoCuentaEstancia::CERRADA,
                'cerrada_at' => now(),
                'cerrada_por' => $usuarioId,
            ]);

            CuentaEstanciaCerrada::dispatch($cuenta);

            return $cuenta->refresh();
        });
    }
}
