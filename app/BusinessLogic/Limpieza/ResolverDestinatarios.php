<?php

declare(strict_types=1);

namespace App\BusinessLogic\Limpieza;

use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\Turno;
use App\Repository\Models\User;
use Illuminate\Support\Collection;

final class ResolverDestinatarios
{
    /**
     * @param  Collection<int, User>  $usuarios
     * @return Collection<int, User>
     */
    public function paraTurno(Turno $turno, Collection $usuarios): Collection
    {
        $destinatarios = collect();

        $this->agregar($destinatarios, $turno->lider?->persona?->id, $usuarios);
        $this->agregar($destinatarios, $turno->apoyo?->persona?->id, $usuarios);

        return $destinatarios->filter()->unique('id');
    }

    /**
     * @param  Collection<int, User>  $usuarios
     * @return Collection<int, User>
     */
    public function paraEjecucion(LimpiezaEjecucion $ejecucion, Collection $usuarios): Collection
    {
        $destinatarios = collect();

        $this->agregar($destinatarios, $ejecucion->colaborador?->persona?->id, $usuarios);

        $turno = $ejecucion->turno;
        if ($turno !== null) {
            $this->agregar($destinatarios, $turno->lider?->persona?->id, $usuarios);
            $this->agregar($destinatarios, $turno->apoyo?->persona?->id, $usuarios);
        }

        return $destinatarios->filter()->unique('id');
    }

    /**
     * @param  Collection<int, User>  $destinatarios
     * @param  Collection<int, User>  $usuarios
     */
    private function agregar(Collection $destinatarios, ?int $personaId, Collection $usuarios): void
    {
        if ($personaId === null) {
            return;
        }

        $usuario = $usuarios->firstWhere('persona_id', $personaId);

        if ($usuario !== null) {
            $destinatarios->push($usuario);
        }
    }
}
