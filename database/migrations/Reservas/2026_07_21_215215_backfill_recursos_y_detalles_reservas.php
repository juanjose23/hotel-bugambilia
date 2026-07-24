<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $this->sincronizarRecursos('habitaciones', 1, 1, 'capacidad_personas');
            $this->sincronizarRecursos('espacios', 2, 2, 'capacidad_personas');
            $this->sincronizarRecursos('servicios', 3, 4, null);

            DB::table('reservas')->orderBy('id')->chunkById(100, function ($reservas): void {
                foreach ($reservas as $reserva) {
                    if (DB::table('reserva_detalles')->where('reserva_id', $reserva->id)->exists()) {
                        continue;
                    }

                    [$tabla, $entidadId] = match ($reserva->tipo_reserva) {
                        'habitacion' => ['habitaciones', $reserva->habitacion_id],
                        'restaurante' => ['espacios', $reserva->espacio_id],
                        'servicio' => ['servicios', $reserva->servicio_id],
                        default => [null, null],
                    };

                    if ($tabla === null || $entidadId === null) {
                        continue;
                    }

                    $recursoId = DB::table($tabla)->where('id', $entidadId)->value('reservable_id');
                    if (! is_numeric($recursoId)) {
                        continue;
                    }

                    $servicios = DB::table('reserva_servicios')->where('reserva_id', $reserva->id)->get();
                    $totalServicios = $servicios->sum(fn ($servicio): float => (float) $servicio->precio * (int) $servicio->cantidad);
                    $inicio = $reserva->fecha_check_in.' '.($reserva->hora_reserva ?: '00:00:00');

                    // Línea 44: Asegurar que strtotime devuelva un entero válido para date()
                    $timestamp = strtotime($inicio.' +1 hour');
                    $fin = $reserva->fecha_check_out
                        ? $reserva->fecha_check_out.' 00:00:00'
                        : date('Y-m-d H:i:s', $timestamp !== false ? $timestamp : time());

                    $ahora = now();
                    $principal = max(0, (float) $reserva->total - $totalServicios);
                    $estadoReserva = $this->normalizarEstadoReserva($reserva->estado);

                    $detalleId = DB::table('reserva_detalles')->insertGetId([
                        'reserva_id' => $reserva->id,
                        'reservable_id' => (int) $recursoId,
                        'estado' => $this->estadoDetalle($estadoReserva),
                        'fecha_inicio' => $inicio,
                        'fecha_fin' => $fin,
                        'cantidad' => 1,
                        'adultos' => (int) $reserva->adultos,
                        'ninos' => (int) $reserva->ninos,
                        'precio_unitario' => $principal,
                        'descuento' => 0,
                        'impuestos' => 0,
                        'subtotal' => $principal,
                        'notas' => $reserva->notas,
                        'created_at' => $reserva->created_at ?? $ahora,
                        'updated_at' => $reserva->updated_at ?? $ahora,
                    ]);

                    $this->migrarServicios((int) $reserva->id, $detalleId, $inicio, $fin, $estadoReserva, $servicios);
                    $this->migrarHuespedes($detalleId, $reserva->acompanantes, $ahora);
                    DB::table('reserva_estado_historial')->insert([
                        'reserva_id' => $reserva->id,
                        'estado_anterior' => null,
                        'estado_nuevo' => $estadoReserva,
                        'motivo' => 'Estado inicial migrado desde el esquema anterior',
                        'created_at' => $reserva->created_at ?? $ahora,
                    ]);
                }
            });
        });
    }

    public function down(): void
    {
        // Backfill intencionalmente irreversible. Requiere una migración correctiva.
    }

    private function sincronizarRecursos(string $tabla, int $tipo, int $control, ?string $campoCapacidad): void
    {
        DB::table($tabla)->whereNull('reservable_id')->orderBy('id')->chunkById(100, function ($entidades) use ($tabla, $tipo, $control, $campoCapacidad): void {
            foreach ($entidades as $entidad) {
                $recursoId = DB::table('recursos_reservables')->insertGetId([
                    'tipo' => $tipo,
                    'nombre' => $entidad->nombre,
                    'capacidad' => $campoCapacidad !== null ? ($entidad->{$campoCapacidad} ?? null) : null,
                    'control_disponibilidad' => $control,
                    'estado' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::table($tabla)->where('id', $entidad->id)->update(['reservable_id' => $recursoId]);
            }
        });
    }

    private function estadoDetalle(int $estadoReserva): int
    {
        return in_array($estadoReserva, [1, 2, 3, 4, 5], true) ? $estadoReserva : 1;
    }

    private function normalizarEstadoReserva(mixed $estado): int
    {
        if (is_numeric($estado)) {
            return $this->estadoDetalle((int) $estado);
        }

        // Línea 115: Forzar casteo seguro a string
        return match (mb_strtolower(trim(is_scalar($estado) ? (string) $estado : ''))) {
            'confirmada', 'confirmado' => 2,
            'check_in', 'check-in', 'en uso' => 3,
            'check_out', 'check-out', 'completada', 'completado' => 4,
            'cancelada', 'cancelado' => 5,
            default => 1,
        };
    }

    private function migrarServicios(int $reservaId, int $parentId, string $inicio, string $fin, int $estadoReserva, mixed $servicios): void
    {
        // Línea 126: Asegurar que $servicios sea iterable para PHPStan
        if (! is_iterable($servicios)) {
            return;
        }

        foreach ($servicios as $servicio) {
            // Líneas 127 a 144: Validar objeto y propiedades para evitar errores de tipo mixed
            if (! is_object($servicio)) {
                continue;
            }

            $servicioId = $servicio->servicio_id ?? null;
            if ($servicioId === null) {
                continue;
            }

            $recursoId = DB::table('servicios')->where('id', $servicioId)->value('reservable_id');
            if (! is_numeric($recursoId)) {
                continue;
            }

            $cantidad = (int) ($servicio->cantidad ?? 0);
            $precio = (float) ($servicio->precio ?? 0.0);

            DB::table('reserva_detalles')->insert([
                'reserva_id' => $reservaId,
                'reservable_id' => (int) $recursoId,
                'parent_id' => $parentId,
                'estado' => $this->estadoDetalle($estadoReserva),
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
                'cantidad' => $cantidad,
                'adultos' => 0,
                'ninos' => 0,
                'precio_unitario' => $precio,
                'descuento' => 0,
                'impuestos' => 0,
                'subtotal' => $precio * $cantidad,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function migrarHuespedes(int $detalleId, mixed $acompanantes, mixed $ahora): void
    {
        if (! is_string($acompanantes) || $acompanantes === '') {
            return;
        }
        $huespedes = json_decode($acompanantes, true);
        if (! is_array($huespedes)) {
            return;
        }
        foreach ($huespedes as $huesped) {
            if (! is_array($huesped) || ! is_string($huesped['nombre'] ?? null)) {
                continue;
            }
            DB::table('reserva_huespedes')->insert([
                'reserva_detalle_id' => $detalleId,
                'nombre' => $huesped['nombre'],
                'identificacion' => is_string($huesped['identificacion'] ?? null) ? $huesped['identificacion'] : null,
                'tipo_huesped' => match ($huesped['tipo'] ?? 'adulto') {
                    'nino' => 2,
                    'infante' => 3,
                    default => 1,
                },
                'es_titular' => false,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);
        }
    }
};
