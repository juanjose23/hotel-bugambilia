<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Ejecucion;

use App\BusinessLogic\Limpieza\Exceptions\OperacionLimpiezaNoPermitida;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Models\User;
use App\Repository\Queries\Limpieza\Ejecucion\ResolverTurnoParaLimpiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final readonly class AsignarSolicitudLimpieza
{
    public function __construct(
        private ResolverTurnoParaLimpiable $resolverTurno,
    ) {}

    /**
     * Crea o actualiza la ejecución de limpieza asociada a una solicitud
     * y la deja lista para que el colaborador la reclame.
     *
     * @throws OperacionLimpiezaNoPermitida Si el usuario no tiene permisos.
     * @throws ModelNotFoundException Si la solicitud no existe.
     */
    public function execute(User $usuario, int $solicitudId): LimpiezaEjecucion
    {
        if (! $this->puedeAsignar($usuario)) {
            throw new OperacionLimpiezaNoPermitida('Solo personal autorizado puede asignar solicitudes de limpieza.');
        }

        return DB::transaction(function () use ($usuario, $solicitudId): LimpiezaEjecucion {
            $solicitud = SolicitudLimpieza::query()
                ->with(['limpiable.ubicacion', 'ejecuciones'])
                ->findOrFail($solicitudId);

            $colaborador = $usuario->persona?->colaborador;

            $ejecucion = $this->asignarEjecucion($solicitud, $colaborador);

            $solicitud->update([
                'personal_id' => $colaborador instanceof Colaborador ? $usuario->id : $solicitud->personal_id,
                'estado' => EstadoLimpieza::Pendiente,
            ]);

            return $ejecucion;
        });
    }

    private function asignarEjecucion(SolicitudLimpieza $solicitud, ?Colaborador $colaborador): LimpiezaEjecucion
    {
        $ejecucion = $solicitud->ejecuciones()->first();

        if ($ejecucion !== null) {
            if ($colaborador instanceof Colaborador && $ejecucion->colaborador_id === null) {
                $ejecucion->update([
                    'colaborador_id' => $colaborador->id,
                ]);
            }

            return $ejecucion;
        }

        $limpiable = $solicitud->limpiable;

        if (! $limpiable instanceof Model) {
            throw new OperacionLimpiezaNoPermitida('La solicitud no tiene un área asociada.');
        }

        $turno = $this->resolverTurno->execute($limpiable);

        return LimpiezaEjecucion::create([
            'solicitud_id' => $solicitud->id,
            'limpiable_type' => $solicitud->limpiable_type,
            'limpiable_id' => $solicitud->limpiable_id,
            'turno_id' => $turno?->id,
            'colaborador_id' => $colaborador?->id,
            'fecha' => now()->toDateString(),
            'estado' => EstadoLimpieza::Pendiente,
        ]);
    }

    private function puedeAsignar(User $usuario): bool
    {
        return $usuario->can('page_GestionMesas')
            || $usuario->can('page_TableroLimpieza')
            || $usuario->hasRole('super_admin');
    }
}
