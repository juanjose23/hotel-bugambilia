<?php

declare(strict_types=1);

namespace App\UseCases\Activos\Mutations\Mantenimiento;

use App\Enums\Activos\EstadoMantenimiento;
use App\Models\Activos\ActivoMantenimiento;
use App\Models\Activos\ActivoMantenimientoNotificacion;
use App\Models\User;
use App\Services\Activos\NotificadorActivos;
use Illuminate\Support\Collection;

class NotificarMantenimientos
{
    public function __construct(private readonly NotificadorActivos $notificador) {}

    public function execute(): int
    {
        $notificacionesEnviadas = 0;

        // 1. Notificaciones del futuro (Próximos a vencer)
        $notificacionesEnviadas += $this->procesarFuturos(7, 'proximo_7_dias');
        $notificacionesEnviadas += $this->procesarFuturos(3, 'proximo_3_dias');
        $notificacionesEnviadas += $this->procesarFuturos(1, 'proximo_1_dia');
        $notificacionesEnviadas += $this->procesarFuturos(0, 'hoy');

        // 2. Notificaciones de retraso (Atrasados y Críticos)
        $notificacionesEnviadas += $this->procesarAtrasados(1, 'vencido');
        $notificacionesEnviadas += $this->procesarAtrasadosCriticos(7, 'critico');

        // 3. Notificaciones de mantenimientos prolongados en proceso
        $notificacionesEnviadas += $this->procesarProlongados(15, 'prolongado');

        return $notificacionesEnviadas;
    }

    private function procesarFuturos(int $dias, string $tipo): int
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

    private function procesarAtrasados(int $dias, string $tipo): int
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

    private function procesarAtrasadosCriticos(int $diasMinimos, string $tipo): int
    {
        $enviados = 0;
        $fechaLimite = today()->subDays($diasMinimos)->toDateString();

        // Mantenimientos programados que tienen fecha_programada vieja
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

    private function procesarProlongados(int $diasMinimos, string $tipo): int
    {
        $enviados = 0;
        $fechaLimite = today()->subDays($diasMinimos)->toDateString();

        // Mantenimientos en proceso que llevan mucho tiempo
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

    /** @return Collection<int, User> */
    private function obtenerDestinatarios(ActivoMantenimiento $mantenimiento): Collection
    {
        if ($mantenimiento->realizado_por_id !== null && $mantenimiento->realizadoPor !== null) {
            return collect([$mantenimiento->realizadoPor]);
        }

        return User::all();
    }

    /** @param Collection<int, User> $destinatarios */
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
