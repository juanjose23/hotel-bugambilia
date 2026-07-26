<?php

declare(strict_types=1);

namespace App\Interactors\CheckIn;

use App\BusinessLogic\CheckIn\ValidarRequisitosCheckIn;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Events\Reservas\CheckInRegistrado;
use App\Interactors\Cuentas\AbrirCuenta;
use App\Interactors\Reservas\CambiarEstadoReserva;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

final class RegistrarCheckIn
{
    public function __construct(
        private readonly CambiarEstadoReserva $cambiarEstado,
        private readonly ValidarRequisitosCheckIn $validarRequisitos,
        private readonly AbrirCuenta $abrirCuenta,
        private readonly ReservaRepositorioInterface $reservas,
    ) {}

    /** @param array<string, mixed> $datos */
    public function ejecutar(Reserva $reserva, ?int $usuarioId = null, array $datos = []): Estancia
    {
        return DB::transaction(function () use ($reserva, $usuarioId, $datos): Estancia {
            $this->actualizarDatosCliente($reserva, $datos);

            $huespedes = $datos['huespedes_nuevos'] ?? [];
            if (is_array($huespedes) && $huespedes !== []) {
                $detallePrincipal = $reserva->detalles()->whereNull('parent_id')->firstOrFail();
                $this->reservas->crearHuespedes($detallePrincipal, array_values($huespedes));
                $reserva->unsetRelation('detalles');
            }

            // Validar requisitos de negocio previos al check-in
            $this->validarRequisitos->validar($reserva);

            $this->cambiarEstado->ejecutar($reserva, EstadoReserva::CHECKED_IN, $usuarioId, 'Check-in registrado');

            $this->actualizarEstadoHabitacion($reserva);

            $estancia = Estancia::query()->create([
                'reserva_id' => $reserva->id,
                'habitacion_id' => $reserva->habitacion_id,
                'usuario_check_in_id' => $usuarioId,
                'check_in_at' => now(),
                'cantidad_llaves' => $this->cantidadLlaves($datos['cantidad_llaves'] ?? 1),
                'estado' => EstadoEstancia::ACTIVA,
                'observaciones_entrada' => $datos['observaciones'] ?? null,
            ]);

            $abrirCuenta = (bool) ($datos['abrir_cuenta'] ?? $reserva->solicita_cuenta ?? false);
            if ($abrirCuenta) {
                $limite = is_numeric($datos['limite_cuenta'] ?? null)
                    ? (float) $datos['limite_cuenta']
                    : (is_numeric($reserva->limite_cuenta_solicitado) ? (float) $reserva->limite_cuenta_solicitado : null);
                $this->abrirCuenta->ejecutar(
                    tipo: TipoCuenta::ESTANCIA,
                    estancia: $estancia,
                    reserva: $reserva,
                    limite: $limite,
                    usuarioId: $usuarioId,
                );
            }

            CheckInRegistrado::dispatch($estancia);

            return $estancia->load('cuenta');
        });
    }

    /** @param array<string, mixed> $datos */
    private function actualizarDatosCliente(Reserva $reserva, array $datos): void
    {
        $actualizaciones = [];

        $nombre = $datos['nombre_cliente'] ?? null;
        if (is_string($nombre) && trim($nombre) !== '') {
            $actualizaciones['nombre_cliente'] = trim($nombre);
        }

        $telefono = $datos['telefono_cliente'] ?? null;
        if (is_string($telefono) && trim($telefono) !== '') {
            $actualizaciones['telefono_cliente'] = trim($telefono);
        }

        $email = $datos['email_cliente'] ?? null;
        if (is_string($email) && trim($email) !== '') {
            $actualizaciones['email_cliente'] = trim($email);
        }

        if ($actualizaciones !== []) {
            $reserva->update($actualizaciones);
        }
    }

    private function actualizarEstadoHabitacion(Reserva $reserva): void
    {
        if ($reserva->habitacion !== null) {
            $habitacion = $reserva->habitacion;
            $estadoAnterior = $habitacion->estado;

            if (in_array($estadoAnterior, [EstadoEspacio::Limpieza, EstadoEspacio::Mantenimiento, EstadoEspacio::Sucio], true)) {
                Notification::make()
                    ->title("Habitación en {$estadoAnterior->getLabel()}")
                    ->body("La habitación {$habitacion->nombre} se encuentra en {$estadoAnterior->getLabel()}. Se registrará en Ocupada y se notificará al personal de alistamiento.")
                    ->warning()
                    ->persistent()
                    ->send();
            }

            $habitacion->update(['estado' => EstadoEspacio::Ocupado]);
        }

        if ($reserva->espacio !== null) {
            $reserva->espacio->update(['estado' => EstadoEspacio::Ocupado]);
        }
    }

    private function cantidadLlaves(mixed $valor): int
    {
        return is_numeric($valor) ? max(1, (int) $valor) : 1;
    }
}
