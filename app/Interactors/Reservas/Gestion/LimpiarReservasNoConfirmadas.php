<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Gestion;

use App\Enums\Reservas\EstadoReserva;
use App\Notifications\Reservas\NotificadorReservas;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Queries\Reservas\ObtenerDestinatariosRecordatorioReservaQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final readonly class LimpiarReservasNoConfirmadas
{
    public function __construct(
        private CancelarReserva $cancelarReserva,
        private ObtenerDestinatariosRecordatorioReservaQuery $obtenerDestinatarios,
        private NotificadorReservas $notificador,
    ) {}

    public function ejecutar(): int
    {
        $hoy = now()->startOfDay();

        /** @var Collection<int, Reserva> $pendientesExpiradas */
        $pendientesExpiradas = Reserva::query()
            ->where('estado', EstadoReserva::PENDIENTE)
            ->where(function ($query) use ($hoy): void {
                $query->whereDate('fecha_check_in', '<=', $hoy)
                    ->orWhereHas('detalles', fn ($q) => $q->where('hold_expires_at', '<=', now()))
                    ->orWhere('created_at', '<=', now()->subHours(24));
            })
            ->get();

        $procesadas = 0;

        foreach ($pendientesExpiradas as $reserva) {
            DB::transaction(function () use ($reserva): void {
                $this->cancelarReserva->ejecutar($reserva, null, 'Cancelación automática por tiempo de confirmación expirado');

                $usuarios = $this->obtenerDestinatarios->ejecutar($reserva);
                if ($usuarios->isNotEmpty()) {
                    $this->notificador->reservaNoConfirmadaExpirada($reserva, $usuarios);
                }
            });

            $procesadas++;
        }

        return $procesadas;
    }
}
