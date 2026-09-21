<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Operaciones;

use App\BusinessLogic\CheckIn\NormalizarDatosClienteCheckIn;
use App\BusinessLogic\CheckIn\ValidarRequisitosCheckIn;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Events\Reservas\CheckInRegistrado;
use App\Interactors\Cuentas\Gestion\AbrirCuenta;
use App\Interactors\Reservas\Gestion\CambiarEstadoReserva;
use App\Interactors\Usuarios\Clientes\ActualizarCliente;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Persistencia\Habitaciones\HabitacionRepositorioInterface;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use Illuminate\Support\Facades\DB;

final class RegistrarCheckIn
{
    public function __construct(
        private readonly CambiarEstadoReserva $cambiarEstado,
        private readonly ValidarRequisitosCheckIn $validarRequisitos,
        private readonly AbrirCuenta $abrirCuenta,
        private readonly ActualizarCliente $actualizarCliente,
        private readonly ReservaRepositorioInterface $reservas,
        private readonly HabitacionRepositorioInterface $habitaciones,
        private readonly RestauranteRepositorioInterface $restaurante,
        private readonly NormalizarDatosClienteCheckIn $normalizarDatos,
    ) {}

    /** @param array<string, mixed> $datos */
    public function ejecutar(Reserva $reserva, ?int $usuarioId = null, array $datos = []): Estancia
    {
        return DB::transaction(function () use ($reserva, $usuarioId, $datos): Estancia {
            $this->actualizarDatosCliente($reserva, $datos);

            $huespedes = $datos['huespedes_nuevos'] ?? [];
            $detallePrincipal = $this->reservas->detallePrincipalDe($reserva);
            if (is_array($huespedes) && $huespedes !== []) {
                $this->reservas->crearHuespedes($detallePrincipal, array_values($huespedes));
                $reserva->unsetRelation('detalles');
            }

            // Validar requisitos de negocio previos al check-in
            $this->validarRequisitos->validar($reserva, $detallePrincipal);

            $this->cambiarEstado->ejecutar($reserva, EstadoReserva::CHECKED_IN, $usuarioId, 'Check-in registrado');

            $this->actualizarEstadoHabitacion($reserva);

            $estancia = $this->reservas->crearEstancia([
                'reserva_id' => $reserva->id,
                'habitacion_id' => $reserva->habitacion_id,
                'usuario_check_in_id' => $usuarioId,
                'check_in_at' => now(),
                'cantidad_llaves' => $this->normalizarDatos->normalizarCantidadLlaves($datos['cantidad_llaves'] ?? 1),
                'estado' => EstadoEstancia::ACTIVA,
                'observaciones_entrada' => $datos['observaciones'] ?? null,
            ]);

            if ($reserva->solicita_cuenta || ($datos['crear_cuenta_estancia'] ?? false)) {
                $cuentaSolicitada = $reserva->cuentas()->where('tipo_cuenta', TipoCuenta::ESTANCIA)->first();
                $this->abrirCuenta->ejecutar(
                    tipo: TipoCuenta::ESTANCIA,
                    cuentaExistente: $cuentaSolicitada,
                    reserva: $reserva,
                    estancia: $estancia,
                    cliente: $reserva->cliente,
                    limite: $reserva->limite_cuenta_solicitado !== null ? (float) $reserva->limite_cuenta_solicitado : null,
                    monedaId: $reserva->moneda_id,
                    usuarioId: $usuarioId,
                );
            }

            CheckInRegistrado::dispatch($estancia);

            return $estancia->refresh();
        });
    }

    /** @param array<string, mixed> $datos */
    private function actualizarDatosCliente(Reserva $reserva, array $datos): void
    {
        $datosParaActualizar = $this->normalizarDatos->extraerDatosCliente($datos);

        if (! empty($datosParaActualizar) && $reserva->cliente !== null) {
            $this->actualizarCliente->ejecutar($reserva->cliente, $datosParaActualizar);
        }
    }

    private function actualizarEstadoHabitacion(Reserva $reserva): void
    {
        $habitacion = $reserva->habitacion;
        if ($habitacion === null) {
            return;
        }

        $this->habitaciones->actualizarEstado(
            $habitacion,
            EstadoEspacio::Ocupado,
        );

        if ($reserva->espacio !== null) {
            $this->restaurante->actualizarEspacio($reserva->espacio, ['estado' => EstadoEspacio::Ocupado]);
        }
    }
}
