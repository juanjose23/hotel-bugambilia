<?php

declare(strict_types=1);

namespace App\UseCases\Espacios\Mutations;

use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Models\Espacios\Espacio;
use Illuminate\Support\Facades\DB;

class GenerarCodigoSubEspacio
{
    /**
     * Genera el siguiente código secuencial para un sub-espacio según su tipo.
     * Formato: {PREFIJO}-{NNNN} (ej. MESA-0012, SALON-0005).
     */
    public function execute(TipoEspacio $tipo): string
    {
        $prefijo = $this->resolvePrefijo($tipo);

        return DB::transaction(function () use ($prefijo) {
            $ultimo = Espacio::withTrashed()
                ->where('codigo', 'like', $prefijo.'-%')
                ->orderByRaw("CAST(SUBSTRING(codigo, LENGTH('{$prefijo}') + 2) AS INTEGER) DESC")
                ->lockForUpdate()
                ->first();

            $numero = 1;

            if ($ultimo && preg_match('/^'.$prefijo.'-(\d+)$/', $ultimo->codigo, $matches)) {
                $numero = (int) $matches[1] + 1;
            } else {
                $max = Espacio::withTrashed()
                    ->where('codigo', 'like', $prefijo.'-%')
                    ->max(DB::raw("CAST(SUBSTRING(codigo, LENGTH('{$prefijo}') + 2) AS INTEGER)"));
                $numero = (int) ($max ?? 0) + 1;
            }

            return $prefijo.'-'.str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
        });
    }

    private function resolvePrefijo(TipoEspacio $tipo): string
    {
        return match ($tipo) {
            TipoEspacio::RESTAURANTE => 'REST',
            TipoEspacio::MESA => 'MESA',
            TipoEspacio::GYM => 'GYM',
            TipoEspacio::SALON => 'SALON',
            TipoEspacio::SPA => 'SPA',
            TipoEspacio::PISCINA => 'PISC',
            TipoEspacio::CANCHA => 'CANCH',
            TipoEspacio::OTRO => 'ESP',
        };
    }
}
