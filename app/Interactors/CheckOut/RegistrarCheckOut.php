<?php

declare(strict_types=1);

namespace App\Interactors\CheckOut;

use App\BusinessLogic\CheckOut\ValidarRequisitosCheckOut;
use App\Enums\Estancias\EstadoCuentaEstancia;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Events\Reservas\CheckOutRegistrado;
use App\Interactors\Reservas\CambiarEstadoReserva;
use App\Repository\Models\Estancias\CuentaEstancia;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\Reserva;
use Illuminate\Support\Facades\DB;

final class RegistrarCheckOut
{
    public function __construct(
        private readonly CambiarEstadoReserva $cambiarEstado,
        private readonly ValidarRequisitosCheckOut $validarCheckOut = new ValidarRequisitosCheckOut,
    ) {}

    /** @param array<string, mixed> $datos */
    public function ejecutar(Reserva $reserva, ?int $usuarioId = null, array $datos = []): Estancia
    {
        return DB::transaction(function () use ($reserva, $usuarioId, $datos): Estancia {
            $estancia = Estancia::query()
                ->with('cuenta')
                ->whereBelongsTo($reserva)
                ->lockForUpdate()
                ->firstOrFail();

            $this->validarCheckOut->validar($estancia, $datos);

            $this->cambiarEstado->ejecutar($reserva, EstadoReserva::CHECKED_OUT, $usuarioId, 'Check-out registrado');

            if ($reserva->habitacion !== null) {
                $reserva->habitacion->update(['estado' => EstadoEspacio::Sucio]);
            }

            if ($reserva->espacio !== null) {
                $reserva->espacio->update(['estado' => EstadoEspacio::Disponible]);
            }

            $cuentasAbiertas = CuentaEstancia::query()
                ->where('reserva_id', $reserva->id)
                ->where('estado', EstadoCuentaEstancia::ABIERTA)
                ->get();

            foreach ($cuentasAbiertas as $cuenta) {
                $cuenta->update([
                    'estado' => EstadoCuentaEstancia::CERRADA,
                    'cerrada_at' => now(),
                    'cerrada_por' => $usuarioId,
                ]);
            }

            $estancia->update([
                'estado' => EstadoEstancia::FINALIZADA,
                'check_out_at' => now(),
                'usuario_check_out_id' => $usuarioId,
                'observaciones_salida' => $datos['observaciones'] ?? null,
            ]);

            CheckOutRegistrado::dispatch($estancia);

            return $estancia->refresh();
        });
    }
}
