<?php

declare(strict_types=1);

namespace App\Interactors\CheckOut;

use App\BusinessLogic\CheckOut\ValidarRequisitosCheckOut;
use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Events\Reservas\CheckOutRegistrado;
use App\Interactors\Reservas\Gestion\CambiarEstadoReserva;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Persistencia\Habitaciones\HabitacionRepositorioInterface;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use Illuminate\Support\Facades\DB;

final class RegistrarCheckOut
{
    public function __construct(
        private readonly CambiarEstadoReserva $cambiarEstado,
        private readonly ValidarRequisitosCheckOut $validarCheckOut,
        private readonly ReservaRepositorioInterface $reservas,
        private readonly CuentaRepositorioInterface $cuentas,
        private readonly HabitacionRepositorioInterface $habitaciones,
        private readonly RestauranteRepositorioInterface $restaurante,
    ) {}

    /** @param array<string, mixed> $datos */
    public function ejecutar(Reserva $reserva, ?int $usuarioId = null, array $datos = []): Estancia
    {
        return DB::transaction(function () use ($reserva, $usuarioId, $datos): Estancia {
            $estancia = $this->reservas->estanciaActivaDeReserva($reserva);

            $this->validarCheckOut->validar($estancia, $datos);

            $this->cambiarEstado->ejecutar($reserva, EstadoReserva::CHECKED_OUT, $usuarioId, 'Check-out registrado');

            if ($reserva->habitacion !== null) {
                $this->habitaciones->actualizarEstado($reserva->habitacion, EstadoEspacio::Sucio);
            }

            if ($reserva->espacio !== null) {
                $this->restaurante->actualizarEspacio($reserva->espacio, ['estado' => EstadoEspacio::Disponible]);
            }

            $cuentasAbiertas = $this->cuentas->cuentasAbiertasDeReserva($reserva->id);

            foreach ($cuentasAbiertas as $cuenta) {
                $this->cuentas->actualizar($cuenta, [
                    'estado' => EstadoCuenta::CERRADA,
                    'cerrada_at' => now(),
                    'cerrada_por' => $usuarioId,
                ]);
            }

            $this->reservas->actualizarEstancia($estancia, [
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
