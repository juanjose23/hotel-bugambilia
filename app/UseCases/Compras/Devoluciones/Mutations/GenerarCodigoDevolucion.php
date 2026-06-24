<?php

namespace App\UseCases\Compras\Devoluciones\Mutations;

use App\Models\Compras\DevolucionCompra;
use Illuminate\Support\Facades\DB;

class GenerarCodigoDevolucion
{
    /**
     * Genera un código correlativo único para la devolución (DEV-YYYY-NNN).
     * Garantiza concurrencia bloqueando la consulta por actualización.
     */
    public function execute(): string
    {
        $year = now()->year;

        return DB::transaction(function () use ($year) {
            $latest = DevolucionCompra::withTrashed()
                ->where('codigo', 'like', "DEV-{$year}-%")
                ->orderBy('codigo', 'desc')
                ->lockForUpdate()
                ->get()
                ->first(function ($dev) {
                    return (bool) preg_match('/-(\d+)$/', $dev->codigo);
                });

            $max = $latest?->codigo;

            $last = 0;

            if ($max && preg_match('/-(\d+)$/', $max, $matches)) {
                $last = (int) $matches[1];
            }

            return "DEV-{$year}-".str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT);
        });
    }
}
