<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Repository\Models\Espacios\Espacio;
use Illuminate\Support\Facades\DB;

final class SepararMesas
{
    /**
     * Separa una mesa secundaria de su grupo o desvincula todas las mesas unidas de una mesa principal.
     */
    public function ejecutar(int $mesaId): void
    {
        DB::transaction(function () use ($mesaId): void {
            $mesa = Espacio::query()->findOrFail($mesaId);
            $meta = $mesa->meta_datos ?? [];

            // Si es una mesa principal con secundarias
            if (! empty($meta['mesas_unidas'])) {
                foreach ($meta['mesas_unidas'] as $secundariaId) {
                    $secundaria = Espacio::query()->find($secundariaId);
                    if ($secundaria instanceof Espacio) {
                        $metaSec = $secundaria->meta_datos ?? [];
                        unset($metaSec['mesa_principal_id'], $metaSec['mesa_principal_nombre']);
                        $secundaria->update([
                            'estado' => EstadoEspacio::Disponible,
                            'meta_datos' => $metaSec,
                        ]);
                    }
                }

                unset($meta['mesas_unidas']);
                $mesa->update(['meta_datos' => $meta]);
            }

            // Si es una mesa secundaria unida a una principal
            if (! empty($meta['mesa_principal_id'])) {
                $principalId = (int) $meta['mesa_principal_id'];
                $principal = Espacio::query()->find($principalId);

                if ($principal) {
                    $metaPrinc = $principal->meta_datos ?? [];
                    if (isset($metaPrinc['mesas_unidas'])) {
                        $metaPrinc['mesas_unidas'] = array_values(array_filter(
                            $metaPrinc['mesas_unidas'],
                            fn ($id) => (int) $id !== $mesaId
                        ));
                        $principal->update(['meta_datos' => $metaPrinc]);
                    }
                }

                unset($meta['mesa_principal_id'], $meta['mesa_principal_nombre']);
                $mesa->update([
                    'estado' => EstadoEspacio::Disponible,
                    'meta_datos' => $meta,
                ]);
            }
        });
    }
}
