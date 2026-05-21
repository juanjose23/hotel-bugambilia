<?php

namespace App\UseCases\Compras\Recepciones\Mutations;

use App\Models\Compras\RecepcionCompra;
use Illuminate\Support\Facades\DB;

class GenerarCodigoRecepcion
{
    public function execute(): string
    {
        $year = now()->year;

        return DB::transaction(function () use ($year) {
            $latest = RecepcionCompra::withTrashed()
                ->where('codigo', 'like', "REC-{$year}-%")
                ->orderBy('codigo', 'desc')
                ->lockForUpdate()
                ->get()
                ->first(function ($rec) {
                    return preg_match('/-(\d+)$/', $rec->codigo);
                });

            $max = $latest?->codigo;

            $last = 0;

            if ($max && preg_match('/-(\d+)$/', $max, $matches)) {
                $last = (int) $matches[1];
            }

            return "REC-{$year}-".str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT);
        });
    }
}
