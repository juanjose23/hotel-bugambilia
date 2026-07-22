<?php

declare(strict_types=1);

namespace App\Repository\Queries\Usuarios;

use App\Repository\Models\Personas\Persona;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ObtenerPersonasDisponibles
{
    public function __construct(
        private readonly Persona $persona,
    ) {}

    /** @return array<int|string, string> */
    public function ejecutar(?int $currentPersonaId = null): array
    {
        $excludedIds = $this->loadExcludedPersonaIds($currentPersonaId);

        $personas = $this->loadPersonasExcluding($excludedIds);

        return $this->formatOptions($personas);
    }

    /**
     * @return array<int, int>
     */
    private function loadExcludedPersonaIds(?int $currentPersonaId): array
    {
        $query = User::query()->whereNotNull('persona_id');

        if ($currentPersonaId !== null) {
            $query->where('persona_id', '!=', $currentPersonaId);
        }

        return $query->pluck('persona_id')
            ->map(fn (mixed $id): int => is_numeric($id) ? (int) $id : 0)
            ->all();
    }

    /** @param  array<int, int>  $excludedIds
     * @return Collection<int, Persona>
     */
    private function loadPersonasExcluding(array $excludedIds): Collection
    {
        return $this->persona->query()
            ->select(['id', 'primer_nombre', 'segundo_nombre'])
            ->whereHas('colaborador')
            ->with([
                'colaborador:id,persona_id,codigo',
                'personaNatural:id,persona_id,primer_apellido,segundo_apellido',
            ])
            ->whereNotIn('id', $excludedIds)
            ->get();
    }

    /** @param  Collection<int, Persona>  $personas
     * @return array<int|string, string>
     */
    private function formatOptions(Collection $personas): array
    {
        $result = [];

        foreach ($personas as $p) {
            $colaboradorCodigo = $p->colaborador->codigo ?? '';
            $primerApellido = $p->personaNatural->primer_apellido ?? '';
            $segundoApellido = $p->personaNatural->segundo_apellido ?? '';

            $partes = array_filter([
                $p->primer_nombre,
                $p->segundo_nombre ?? '',
                $primerApellido,
                $segundoApellido,
            ]);

            $nombreCompleto = implode(' ', $partes);

            $result[$p->id] = filled($colaboradorCodigo)
                ? "{$colaboradorCodigo} - {$nombreCompleto}"
                : $nombreCompleto;
        }

        return $result;
    }
}
