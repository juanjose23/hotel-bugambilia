<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Mesas;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use Illuminate\Support\Facades\DB;

final class SepararMesas
{
    public function __construct(
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}

    /**
     * Separa una mesa secundaria de su grupo o desvincula todas las mesas unidas de una mesa principal.
     */
    public function ejecutar(int $mesaId): void
    {
        DB::transaction(function () use ($mesaId): void {
            $mesa = $this->repositorio->obtenerEspacioPorId($mesaId);

            if (! $mesa instanceof Espacio) {
                return;
            }

            $meta = $this->normalizarMetaDatos($mesa->meta_datos);

            if (! empty($meta['mesas_unidas']) && is_array($meta['mesas_unidas'])) {
                foreach ($meta['mesas_unidas'] as $secundariaId) {
                    if (! is_numeric($secundariaId)) {
                        continue;
                    }
                    $secundaria = $this->repositorio->obtenerEspacioPorId((int) $secundariaId);

                    if ($secundaria instanceof Espacio) {
                        $metaSec = $this->normalizarMetaDatos($secundaria->meta_datos);
                        unset($metaSec['mesa_principal_id'], $metaSec['mesa_principal_nombre']);

                        $this->repositorio->actualizarEspacio($secundaria, [
                            'estado' => EstadoEspacio::Disponible,
                            'meta_datos' => $metaSec,
                        ]);
                    }
                }

                unset($meta['mesas_unidas']);
                $this->repositorio->actualizarEspacio($mesa, ['meta_datos' => $meta]);
            }

            $principalIdRaw = $meta['mesa_principal_id'] ?? null;
            $principalId = is_numeric($principalIdRaw) ? (int) $principalIdRaw : null;

            if ($principalId !== null) {
                $principal = $this->repositorio->obtenerEspacioPorId($principalId);

                if ($principal instanceof Espacio) {
                    $metaPrinc = $this->normalizarMetaDatos($principal->meta_datos);
                    if (isset($metaPrinc['mesas_unidas']) && is_array($metaPrinc['mesas_unidas'])) {
                        $metaPrinc['mesas_unidas'] = array_values(array_filter(
                            $metaPrinc['mesas_unidas'],
                            fn ($id): bool => is_numeric($id) ? (int) $id !== $mesaId : true
                        ));
                        $this->repositorio->actualizarEspacio($principal, ['meta_datos' => $metaPrinc]);
                    }
                }

                unset($meta['mesa_principal_id'], $meta['mesa_principal_nombre']);
                $this->repositorio->actualizarEspacio($mesa, [
                    'estado' => EstadoEspacio::Disponible,
                    'meta_datos' => $meta,
                ]);
            }
        });
    }

    /**
     * @return array<array-key, mixed>
     */
    private function normalizarMetaDatos(mixed $metaDatos): array
    {
        if (is_array($metaDatos)) {
            return $metaDatos;
        }

        if (is_string($metaDatos) && $metaDatos !== '') {
            $decoded = json_decode($metaDatos, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
