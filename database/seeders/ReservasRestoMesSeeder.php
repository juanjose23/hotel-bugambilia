<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Cuentas\MetodoPago;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\Reservas\Gestion\CambiarEstadoReserva;
use App\Interactors\Reservas\Gestion\CrearReserva;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Throwable;

final class ReservasRestoMesSeeder extends Seeder
{
    public function __construct(
        private readonly CrearReserva $crearReserva,
        private readonly CambiarEstadoReserva $cambiarEstadoReserva,
    ) {}

    public function run(): void
    {
        $habitaciones = Habitacion::activas()->get();
        $espacios = Espacio::activosWeb()
            ->where('tipo', TipoEspacio::MESA)
            ->get();
        $cliente = User::query()->first();

        $nombresDemo = [
            'Ana María Rodríguez',
            'Carlos Alberto Mendoza',
            'Sofía Isabel Torrez',
            'Gabriel Eduardo Silva',
            'Valeria Lucía Gutiérrez',
            'Fernando José Castillo',
        ];

        $hoy = Carbon::today();
        $finDeMes = Carbon::now()->endOfMonth();

        // 1. Crear 8 Reservaciones de Habitaciones distribuidas en lo que resta del mes
        if ($habitaciones->isNotEmpty()) {
            foreach ($habitaciones->take(5) as $idx => $habitacion) {
                $checkIn = $hoy->copy()->addDays($idx * 2 + 1);
                if ($checkIn->greaterThan($finDeMes)) {
                    break;
                }
                $checkOut = $checkIn->copy()->addDays(rand(1, 3));
                $nombre = $nombresDemo[$idx % count($nombresDemo)];

                if (Reserva::query()
                    ->where('tipo_reserva', TipoReserva::HABITACION)
                    ->where('habitacion_id', $habitacion->id)
                    ->whereDate('fecha_check_in', $checkIn->format('Y-m-d'))
                    ->exists()) {
                    continue;
                }

                try {
                    $reserva = $this->crearReserva->ejecutar([
                        'cliente_id' => $cliente?->id,
                        'nombre_cliente' => $nombre,
                        'telefono_cliente' => '+505 88'.rand(10, 99).' '.rand(1000, 9999),
                        'email_cliente' => Str::slug($nombre).'@ejemplo.com',
                        'tipo_reserva' => TipoReserva::HABITACION->value,
                        'habitacion_id' => $habitacion->id,
                        'fecha_check_in' => $checkIn->format('Y-m-d'),
                        'fecha_check_out' => $checkOut->format('Y-m-d'),
                        'adultos' => rand(1, 3),
                        'ninos' => rand(0, 2),
                        'tipo_pago_reserva' => TipoPagoReserva::SIN_PAGO->value,
                        'notas' => 'Reserva demo para estadía en Hotel Bugambilias.',
                    ]);
                } catch (Throwable $e) {
                    $this->command->warn("Reserva demo de habitación omitida: {$e->getMessage()}");

                    continue;
                }

                if ($idx % 2 === 0) {
                    $this->cambiarEstadoReserva->ejecutar($reserva, EstadoReserva::CONFIRMADA, null, 'Reserva demo confirmada');
                }
            }
        }

        // 2. Crear 5 Reservaciones de Espacios y Ambientes
        if ($espacios->isNotEmpty()) {
            foreach ($espacios->take(4) as $idx => $espacio) {
                $fechaReserva = $hoy->copy()->addDays($idx * 3 + 2);
                if ($fechaReserva->greaterThan($finDeMes)) {
                    break;
                }
                $nombre = $nombresDemo[($idx + 2) % count($nombresDemo)];

                if (Reserva::query()
                    ->where('tipo_reserva', TipoReserva::RESTAURANTE)
                    ->where('espacio_id', $espacio->id)
                    ->whereDate('fecha_check_in', $fechaReserva->format('Y-m-d'))
                    ->where('hora_reserva', '13:00')
                    ->exists()) {
                    continue;
                }

                $tipoPago = match ($idx) {
                    0 => TipoPagoReserva::ABONO_50,
                    1 => TipoPagoReserva::SIN_PAGO,
                    default => TipoPagoReserva::SIN_PAGO,
                };
                $adultos = match ($idx) {
                    0 => 2,
                    1 => 5,
                    2 => 6,
                    default => 3,
                };

                try {
                    $reserva = $this->crearReserva->ejecutar([
                        'cliente_id' => $cliente?->id,
                        'nombre_cliente' => $nombre,
                        'telefono_cliente' => '+505 87'.rand(10, 99).' '.rand(1000, 9999),
                        'email_cliente' => Str::slug($nombre).'@ejemplo.com',
                        'tipo_reserva' => TipoReserva::RESTAURANTE->value,
                        'espacio_id' => $espacio->id,
                        'fecha_check_in' => $fechaReserva->format('Y-m-d'),
                        'fecha_check_out' => $fechaReserva->format('Y-m-d'),
                        'hora_reserva' => '13:00',
                        'duracion_horas' => 1,
                        'adultos' => $adultos,
                        'ninos' => $idx === 2 ? 1 : 0,
                        'tipo_pago_reserva' => $tipoPago->value,
                        'metodo_pago_reserva' => $tipoPago === TipoPagoReserva::SIN_PAGO ? null : MetodoPago::EFECTIVO->value,
                        'referencia_pago_reserva' => $tipoPago === TipoPagoReserva::SIN_PAGO ? null : 'ABONO-DEMO-REST-'.$idx,
                        'notas' => $idx === 0
                            ? 'Reserva demo de restaurante con cobro inicial del 50%.'
                            : 'Reserva demo de restaurante con validación de mesas por horario.',
                    ]);
                } catch (Throwable $e) {
                    $this->command->warn("Reserva demo de restaurante omitida: {$e->getMessage()}");

                    continue;
                }

                if ($reserva->refresh()->estado !== EstadoReserva::CONFIRMADA) {
                    $this->cambiarEstadoReserva->ejecutar($reserva, EstadoReserva::CONFIRMADA, null, 'Reserva demo confirmada');
                }
            }
        }
    }
}
