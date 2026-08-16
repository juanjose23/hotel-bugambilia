<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Operaciones;

use App\Notifications\Reservas\NotificadorHuesped;
use App\Notifications\Reservas\NotificadorReservas;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Queries\Reservas\ObtenerDestinatariosRecordatorioReservaQuery;
use App\Repository\Queries\Reservas\ObtenerReservasProximasQuery;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;

final readonly class EnviarRecordatoriosReservas
{
    public function __construct(
        private ObtenerReservasProximasQuery $reservas,
        private ObtenerDestinatariosRecordatorioReservaQuery $destinatarios,
        private NotificadorReservas $notificador,
        private NotificadorHuesped $notificadorHuesped,
    ) {}

    public function ejecutar(): int
    {
        if (! config('hotel.reservas.recordatorio_habilitado', true)) {
            return 0;
        }

        $configAnticipacion = config('hotel.reservas.anticipacion_minutos', 30);
        $anticipacion = max(0, is_numeric($configAnticipacion) ? (int) $configAnticipacion : 30);

        $configTolerancia = config('hotel.reservas.tolerancia_minutos', 5);
        $tolerancia = max(1, is_numeric($configTolerancia) ? (int) $configTolerancia : 5);
        $desde = now()->addMinutes($anticipacion)->startOfMinute();
        $hasta = $desde->copy()->addMinutes($tolerancia);
        $enviados = 0;

        foreach ($this->reservas->ejecutar($desde, $hasta) as $reserva) {
            $inicio = $reserva->detalles->first()?->fecha_inicio;
            if (! $inicio instanceof CarbonInterface) {
                continue;
            }

            $clave = "reservas:recordatorio:$reserva->id:$inicio->timestamp:$anticipacion";
            if (! Cache::add($clave, true, $inicio->copy()->addDay())) {
                continue;
            }

            $usuarios = $this->destinatarios->ejecutar($reserva);
            if ($usuarios->isEmpty() && ! $this->huespedTieneEmail($reserva)) {
                Cache::forget($clave);

                continue;
            }

            if ($usuarios->isNotEmpty()) {
                $this->notificador->recordatorio($reserva, $inicio, $usuarios);
                $enviados += $usuarios->count();
            }

            $this->notificadorHuesped->recordatorio($reserva, $inicio);
            $enviados++;
        }

        return $enviados;
    }

    private function huespedTieneEmail(Reserva $reserva): bool
    {
        return is_string($reserva->email_cliente) && trim($reserva->email_cliente) !== '';
    }
}
