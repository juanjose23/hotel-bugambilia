<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Procesos;

use App\BusinessLogic\Limpieza\ResolverDestinatarios;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Notifications\Limpieza\NotificadorLimpieza;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Queries\Limpieza\ObtenerUsuariosPorPersonaIds;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

final class EnviarRecordatorios
{
    public function __construct(
        private readonly NotificadorLimpieza $notificador,
        private readonly ResolverDestinatarios $resolverDestinatarios,
        private readonly ObtenerUsuariosPorPersonaIds $obtenerUsuariosPorPersonaIds,
    ) {}

    /**
     * @return array{procesadas: int, enviadas: int, avisos: array<int, string>}
     */
    public function ejecutar(): array
    {
        $ahora = Carbon::now();
        $horaActual = $ahora->toTimeString();

        $ejecuciones = $this->obtenerEjecucionesPendientes($ahora, $horaActual);

        $personaIds = $ejecuciones->pluck('colaborador.persona.id')
            ->merge($ejecuciones->pluck('turno.lider.persona.id'))
            ->merge($ejecuciones->pluck('turno.apoyo.persona.id'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $usuarios = $this->obtenerUsuariosPorPersonaIds->ejecutar($personaIds);

        $procesadas = 0;
        $enviadas = 0;
        $avisos = [];

        foreach ($ejecuciones as $ejecucion) {
            $procesadas++;

            $nombreLimpiable = $this->nombreLimpiable($ejecucion);

            $destinatarios = $this->resolverDestinatarios->paraEjecucion($ejecucion, $usuarios);

            if ($destinatarios->isEmpty()) {
                $avisos[] = "La ejecución #{$ejecucion->id} (Ubicación: {$nombreLimpiable}) no tiene usuarios destinatarios con cuentas vinculadas.";

                continue;
            }

            $this->notificador->recordatorioPendiente($ejecucion, $destinatarios);

            $ejecucion->update([
                'recordatorio_enviado_at' => $ahora,
            ]);

            $emails = implode(', ', array_filter($destinatarios->pluck('email')->all(), 'is_string'));
            Log::info("Recordatorio de limpieza enviado para la ejecución #{$ejecucion->id} (Ubicación: {$nombreLimpiable}) a las {$ahora->toDateTimeString()}. Destinatarios notificados: {$emails}");
            $enviadas++;
        }

        return [
            'procesadas' => $procesadas,
            'enviadas' => $enviadas,
            'avisos' => $avisos,
        ];
    }

    /**
     * @return Collection<int, LimpiezaEjecucion>
     */
    private function obtenerEjecucionesPendientes(Carbon $ahora, string $horaActual): Collection
    {
        return LimpiezaEjecucion::query()
            ->whereDate('fecha', '<=', $ahora->toDateString())
            ->where('estado', EstadoLimpieza::Pendiente)
            ->whereNull('recordatorio_enviado_at')
            ->whereHas('horario', function (Builder $query) use ($horaActual) {
                $query->where('hora_estimada', '<=', $horaActual);
            })
            ->with([
                'turno.lider.persona',
                'turno.apoyo.persona',
                'colaborador.persona',
                'horario',
                'limpiable',
            ])
            ->get();
    }

    private function nombreLimpiable(LimpiezaEjecucion $ejecucion): string
    {
        $limpiable = $ejecucion->limpiable;
        if (! $limpiable) {
            return 'Área sin nombre';
        }

        $nombre = $limpiable->getAttribute('nombre');

        return is_string($nombre) ? $nombre : 'Área sin nombre';
    }
}
