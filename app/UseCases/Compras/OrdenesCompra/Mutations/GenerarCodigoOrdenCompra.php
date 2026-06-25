<?php

namespace App\UseCases\Compras\OrdenesCompra\Mutations;

use App\Models\Compras\OrdenCompra;
use Illuminate\Support\Facades\DB;

class GenerarCodigoOrdenCompra
{
    public function execute(): string
    {
        $year = now()->year;

        return DB::transaction(function () use ($year) {
            $latest = OrdenCompra::withTrashed()
                ->where('codigo', 'like', "OC-{$year}-%")
                ->orderBy('codigo', 'desc')
                ->lockForUpdate()
                ->get()
                ->first(function ($order) {
                    return (bool) preg_match('/-(\d+)$/', $order->codigo);
                });

            $max = $latest?->codigo;

            $last = 0;

            if ($max && preg_match('/-(\d+)$/', $max, $matches)) {
                $last = (int) $matches[1];
            }

            return "OC-{$year}-".str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT);
        });
    }
}
