<?php

declare(strict_types=1);

namespace App\BusinessLogic\Activos;

use App\Enums\Activos\EstadoMantenimiento;
use App\Notifications\Activos\NotificadorActivos;
use App\Repository\Models\Activos\ActivoMantenimiento;
use App\Repository\Models\Activos\ActivoMantenimientoNotificacion;
use App\Repository\Models\User;
use Illuminate\Support\Collection;

class ProcesadorNotificacionesMantenimiento
{
    public function __construct(private readonly NotificadorActivos $notificador) {}

    public function procesarFuturos(int $dias, string $tipo): int
    {
        $enviados = 0;
        $fechaObjetivo = today()->addDays($dias)->toDateString();

        $mantenimientos = ActivoMantenimiento::query()
            ->with(['activo', 'realizadoPor'])
            ->where('estado', EstadoMantenimiento::Programado)
            ->whereDate('fecha_programada', $fechaObjetivo)
            ->get();

        foreach ($mantenimientos as $mantenimiento) {
            if ($this->yaFueNotificado($mantenimiento->id, $tipo)) {
                continue;
            }

            $destinatarios = $this->obtenerDestinatarios($mantenimiento);

            $this->notificador->mantenimientoProximo($mantenimiento, $dias, $destinatarios);
            $this->registrarEnvio($mantenimiento->id, $tipo, $destinatarios);
            $enviados++;
        }

        return $enviados;
    }

    public function procesarAtrasados(int $dias, string $tipo): int
    {
        $enviados = 0;
        $fechaObjetivo = today()->subDays($dias)->toDateString();

        $mantenimientos = ActivoMantenimiento::query()
            ->with(['activo', 'realizadoPor'])
            ->where('estado', EstadoMantenimiento::Programado)
            ->whereDate('fecha_programada', $fechaObjetivo)
            ->get();

        foreach ($mantenimientos as $mantenimiento) {
            if ($this->yaFueNotificado($mantenimiento->id, $tipo)) {
                continue;
            }

            $destinatarios = $this->obtenerDestinatarios($mantenimiento);

            $this->notificador->mantenimientoAtrasado($mantenimiento, $dias, $destinatarios);
            $this->registrarEnvio($mantenimiento->id, $tipo, $destinatarios);
            $enviados++;
        }

        return $enviados;
    }

    public function procesarAtrasadosCriticos(int $diasMinimos, string $tipo): int
    {
        $enviados = 0;
        $fechaLimite = today()->subDays($diasMinimos)->toDateString();

        $mantenimientos = ActivoMantenimiento::query()
            ->with(['activo', 'realizadoPor'])
            ->where('estado', EstadoMantenimiento::Programado)
            ->whereDate('fecha_programada', '<=', $fechaLimite)
            ->get();

        foreach ($mantenimientos as $mantenimiento) {
            if ($this->yaFueNotificado($mantenimiento->id, $tipo)) {
                continue;
            }

            $destinatarios = $this->obtenerDestinatarios($mantenimiento);
            $diasReales = (int) today()->diffInDays($mantenimiento->fecha_programada);

            $this->notificador->mantenimientoAtrasado($mantenimiento, $diasReales, $destinatarios);
            $this->registrarEnvio($mantenimiento->id, $tipo, $destinatarios);
            $enviados++;
        }

        return $enviados;
    }

    public function procesarProlongados(int $diasMinimos, string $tipo): int
    {
        $enviados = 0;
        $fechaLimite = today()->subDays($diasMinimos)->toDateString();

        $mantenimientos = ActivoMantenimiento::query()
            ->with(['activo', 'realizadoPor'])
            ->where('estado', EstadoMantenimiento::EnProceso)
            ->whereDate('fecha_programada', '<=', $fechaLimite)
            ->get();

        foreach ($mantenimientos as $mantenimiento) {
            if ($this->yaFueNotificado($mantenimiento->id, $tipo)) {
                continue;
            }

            $destinatarios = $this->obtenerDestinatarios($mantenimiento);
            $diasReales = (int) today()->diffInDays($mantenimiento->fecha_programada);

            $this->notificador->mantenimientoProlongado($mantenimiento, $diasReales, $destinatarios);
            $this->registrarEnvio($mantenimiento->id, $tipo, $destinatarios);
            $enviados++;
        }

        return $enviados;
    }

    private function yaFueNotificado(int $mantenimientoId, string $tipo): bool
    {
        return ActivoMantenimientoNotificacion::where('mantenimiento_id', $mantenimientoId)
            ->where('tipo', $tipo)
            ->exists();
    }

    /**
     * @return Collection<int, User>
     */
    private function obtenerDestinatarios(ActivoMantenimiento $mantenimiento): Collection
    {
        if ($mantenimiento->realizado_por_id !== null && $mantenimiento->realizadoPor !== null) {
            return collect([$mantenimiento->realizadoPor]);
        }

        return User::all();
    }

    /**
     * @param  Collection<int, User>  $destinatarios
     */
    private function registrarEnvio(int $mantenimientoId, string $tipo, Collection $destinatarios): void
    {
        foreach ($destinatarios as $destinatario) {
            ActivoMantenimientoNotificacion::create([
                'mantenimiento_id' => $mantenimientoId,
                'tipo' => $tipo,
                'canal' => 'database',
                'enviado_a' => $destinatario->id,
                'metadata' => [
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);
        }
    }
}
