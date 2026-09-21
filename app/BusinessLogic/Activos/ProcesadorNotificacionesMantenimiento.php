<?php

declare(strict_types=1);

namespace App\BusinessLogic\Activos;

use App\Notifications\Activos\NotificadorActivos;
use App\Repository\Models\Activos\ActivoMantenimiento;
use App\Repository\Models\User;
use App\Repository\Persistencia\Activos\ActivoMantenimientoRepositorioInterface;
use Illuminate\Support\Collection;

final readonly class ProcesadorNotificacionesMantenimiento
{
    public function __construct(
        private NotificadorActivos $notificador,
        private ActivoMantenimientoRepositorioInterface $mantenimientoRepositorio,
    ) {}

    public function procesarFuturos(int $dias, string $tipo): int
    {
        $enviados = 0;
        $fechaObjetivo = today()->addDays($dias)->toDateString();

        $mantenimientos = $this->mantenimientoRepositorio->obtenerProgramadosPorFecha($fechaObjetivo);

        foreach ($mantenimientos as $mantenimiento) {
            if ($this->mantenimientoRepositorio->yaFueNotificado($mantenimiento->id, $tipo)) {
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

        $mantenimientos = $this->mantenimientoRepositorio->obtenerProgramadosPorFecha($fechaObjetivo);

        foreach ($mantenimientos as $mantenimiento) {
            if ($this->mantenimientoRepositorio->yaFueNotificado($mantenimiento->id, $tipo)) {
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

        $mantenimientos = $this->mantenimientoRepositorio->obtenerProgramadosAtrasados($fechaLimite);

        foreach ($mantenimientos as $mantenimiento) {
            if ($this->mantenimientoRepositorio->yaFueNotificado($mantenimiento->id, $tipo)) {
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

        $mantenimientos = $this->mantenimientoRepositorio->obtenerEnProcesoAtrasados($fechaLimite);

        foreach ($mantenimientos as $mantenimiento) {
            if ($this->mantenimientoRepositorio->yaFueNotificado($mantenimiento->id, $tipo)) {
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

    /**
     * @return Collection<int, User>
     */
    private function obtenerDestinatarios(ActivoMantenimiento $mantenimiento): Collection
    {
        if ($mantenimiento->realizado_por_id !== null && $mantenimiento->realizadoPor !== null) {
            return collect([$mantenimiento->realizadoPor]);
        }

        return $this->mantenimientoRepositorio->obtenerTodosUsuarios();
    }

    /**
     * @param  Collection<int, User>  $destinatarios
     */
    private function registrarEnvio(int $mantenimientoId, string $tipo, Collection $destinatarios): void
    {
        foreach ($destinatarios as $destinatario) {
            $this->mantenimientoRepositorio->registrarNotificacion($mantenimientoId, $tipo, $destinatario->id);
        }
    }
}
