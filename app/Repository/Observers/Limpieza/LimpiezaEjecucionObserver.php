<?php

declare(strict_types=1);

namespace App\Repository\Observers\Limpieza;

use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\SolicitudLimpieza;

class LimpiezaEjecucionObserver
{
    public function saved(LimpiezaEjecucion $ejecucion): void
    {
        if ($ejecucion->solicitud_id === null) {
            return;
        }

        $solicitud = $ejecucion->solicitud ?: SolicitudLimpieza::find($ejecucion->solicitud_id);
        if (! $solicitud instanceof SolicitudLimpieza) {
            return;
        }

        $updates = [];

        if ($solicitud->estado !== $ejecucion->estado) {
            $updates['estado'] = $ejecucion->estado;
        }

        if ($ejecucion->colaborador_id !== null) {
            $colaborador = Colaborador::query()
                ->where('id', $ejecucion->colaborador_id)
                ->with('persona.user')
                ->first();

            $userId = $colaborador?->persona?->user?->id;

            if ($userId !== null && $solicitud->personal_id !== $userId) {
                $updates['personal_id'] = $userId;
            }
        }

        if (! empty($updates)) {
            $solicitud->updateQuietly($updates);
        }
    }
}
