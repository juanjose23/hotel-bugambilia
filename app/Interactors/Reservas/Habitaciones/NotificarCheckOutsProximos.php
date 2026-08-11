<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Habitaciones;

use App\Enums\Estancias\EstadoEstancia;
use App\Notifications\Reservas\NotificadorReservas;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Queries\Reservas\ObtenerDestinatariosRecordatorioReservaQuery;
use Illuminate\Database\Eloquent\Collection;

final readonly class NotificarCheckOutsProximos
{
    public function __construct(
        private ObtenerDestinatariosRecordatorioReservaQuery $obtenerDestinatarios,
        private NotificadorReservas $notificador,
    ) {}

    public function ejecutar(): int
    {
        $limiteHorizonte = now()->addHours(2);

        /** @var Collection<int, Estancia> $estanciasProximas */
        $estanciasProximas = Estancia::query()
            ->with(['reserva', 'habitacion'])
            ->where('estado', EstadoEstancia::ACTIVA)
            ->whereNull('fecha_check_out_real')
            ->where('fecha_salida_programada', '<=', $limiteHorizonte)
            ->get();

        $notificados = 0;

        foreach ($estanciasProximas as $estancia) {
            if ($estancia->reserva === null) {
                continue;
            }

            $usuarios = $this->obtenerDestinatarios->ejecutar($estancia->reserva);
            if ($usuarios->isNotEmpty()) {
                $this->notificador->checkOutProximoExpirar($estancia, $usuarios);
                $notificados++;
            }
        }

        return $notificados;
    }
}
