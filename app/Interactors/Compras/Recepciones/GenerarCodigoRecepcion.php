<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Recepciones;

use App\Repository\Models\Compras\RecepcionCompra;
use Illuminate\Support\Facades\DB;

final class GenerarCodigoRecepcion
{
    public function ejecutar(): string
    {
        $year = now()->year;

        return DB::transaction(function () use ($year): string {
            $ultimo = RecepcionCompra::withTrashed()
                ->where('codigo', 'like', "REC-{$year}-%")
                ->orderBy('codigo', 'desc')
                ->lockForUpdate()
                ->get()
                ->first(fn ($rec) => (bool) preg_match('/\-(\d+)$/', $rec->codigo));

            $ultimo = $ultimo?->codigo;
            $secuencia = 0;

            if ($ultimo && preg_match('/\-(\d+)$/', $ultimo, $coincidencias)) {
                $secuencia = (int) $coincidencias[1];
            }

            return "REC-{$year}-".str_pad((string) ($secuencia + 1), 3, '0', STR_PAD_LEFT);
        });
    }
}
