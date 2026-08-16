<?php

declare(strict_types=1);

namespace App\Actions\Reservas;

use App\Repository\Models\Shared\Secuencia;
use Illuminate\Support\Facades\DB;

final class GenerarCodigoReserva
{
    public function ejecutar(): string
    {
        return DB::transaction(function (): string {
            $anio = (int) now()->format('Y');

            /** @var Secuencia|null $secuencia */
            $secuencia = Secuencia::query()
                ->where('tipo', 'reserva')
                ->where('anio', $anio)
                ->lockForUpdate()
                ->first();

            if ($secuencia === null) {
                /** @var Secuencia $secuencia */
                $secuencia = Secuencia::query()->create([
                    'tipo' => 'reserva',
                    'anio' => $anio,
                    'ultimo_numero' => 0,
                ]);
            }

            $siguiente = $secuencia->ultimo_numero + 1;

            $secuencia->update([
                'ultimo_numero' => $siguiente,
            ]);

            return sprintf(
                'HTB-%d-%06d',
                $anio,
                $siguiente,
            );
        });
    }
}
