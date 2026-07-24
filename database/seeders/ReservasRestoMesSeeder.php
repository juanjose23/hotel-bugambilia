<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class ReservasRestoMesSeeder extends Seeder
{
    public function run(): void
    {
        $habitaciones = Habitacion::activas()->get();
        $espacios = Espacio::activosWeb()->get();
        $cliente = User::first();

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

                Reserva::create([
                    'codigo_reserva' => 'RES-HAB-'.strtoupper(Str::random(5)),
                    'cliente_id' => $cliente?->id,
                    'nombre_cliente' => $nombre,
                    'telefono_cliente' => '+505 88'.rand(10, 99).' '.rand(1000, 9999),
                    'email_cliente' => Str::slug($nombre).'@ejemplo.com',
                    'tipo_reserva' => TipoReserva::HABITACION,
                    'habitacion_id' => $habitacion->id,
                    'fecha_check_in' => $checkIn->format('Y-m-d'),
                    'fecha_check_out' => $checkOut->format('Y-m-d'),
                    'adultos' => rand(1, 3),
                    'ninos' => rand(0, 2),
                    'estado' => $idx % 2 === 0 ? EstadoReserva::CONFIRMADA : EstadoReserva::PENDIENTE,
                    'subtotal' => 2800.00,
                    'descuento' => 0.00,
                    'total' => 2800.00,
                    'notas' => 'Reserva demo para estadía en Hotel Bugambilias.',
                ]);
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

                Reserva::create([
                    'codigo_reserva' => 'RES-ESP-'.strtoupper(Str::random(5)),
                    'cliente_id' => $cliente?->id,
                    'nombre_cliente' => $nombre,
                    'telefono_cliente' => '+505 87'.rand(10, 99).' '.rand(1000, 9999),
                    'email_cliente' => Str::slug($nombre).'@ejemplo.com',
                    'tipo_reserva' => TipoReserva::RESTAURANTE,
                    'espacio_id' => $espacio->id,
                    'fecha_check_in' => $fechaReserva->format('Y-m-d'),
                    'fecha_check_out' => $fechaReserva->format('Y-m-d'),
                    'hora_reserva' => '13:00',
                    'adultos' => rand(2, 6),
                    'ninos' => rand(0, 2),
                    'estado' => EstadoReserva::CONFIRMADA,
                    'subtotal' => 1500.00,
                    'descuento' => 0.00,
                    'total' => 1500.00,
                    'notas' => 'Reserva demo de espacio / ambiente en Hotel Bugambilias.',
                ]);
            }
        }
    }
}
