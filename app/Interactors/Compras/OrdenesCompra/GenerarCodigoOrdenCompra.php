<?php

declare(strict_types=1);

namespace App\Interactors\Compras\OrdenesCompra;

use App\Repository\Models\Compras\OrdenCompra;
use Illuminate\Support\Facades\DB;

final class GenerarCodigoOrdenCompra
{
    public function ejecutar(): string
    {
        $year = now()->year;

        return DB::transaction(function () use ($year): string {
            $latest = OrdenCompra::withTrashed()
                ->where('codigo', 'like', "OC-{$year}-%")
                ->orderBy('codigo', 'desc')
                ->lockForUpdate()
                ->get()
                ->first(fn ($order) => (bool) preg_match('/\-(\d+)$/', $order->codigo));

            $last = 0;

            if ($latest?->codigo && preg_match('/\-(\d+)$/', $latest->codigo, $matches)) {
                $last = (int) $matches[1];
            }

            return "OC-{$year}-".str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT);
        });
    }
}
