<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('reservas')->whereNotNull('acompanantes')->orderBy('id')->each(function (object $reserva): void {
            $lista = json_decode((string) $reserva->acompanantes, true, 512, JSON_THROW_ON_ERROR);
            if ($lista === []) {
                return;
            }
            if (! is_array($lista) || ! array_is_list($lista)) {
                throw new RuntimeException('Acompanantes historicos sin formato de lista.');
            }
            $detalles = DB::table('reserva_detalles')->where('reserva_id', $reserva->id)->whereNull('deleted_at')->pluck('id');
            $habitaciones = DB::table('reserva_detalles as d')->join('recursos_reservables as rr', 'rr.id', '=', 'd.reservable_id')
                ->where('d.reserva_id', $reserva->id)->whereNull('d.deleted_at')->where('rr.tipo', 1)->pluck('d.id');
            $destinos = $habitaciones->isNotEmpty() ? $habitaciones : $detalles;
            foreach ($lista as $persona) {
                if (! is_array($persona) || ! is_string($persona['nombre'] ?? null) || trim($persona['nombre']) === '') {
                    throw new RuntimeException('Acompanante historico sin identidad revisable.');
                }
                $tipo = match ($persona['tipo'] ?? 'adulto') {
                    'nino', 'niño' => 2, 'bebe', 'infante' => 3, default => 1
                };
                $existentes = DB::table('reserva_huespedes')->whereIn('reserva_detalle_id', $detalles)->where('nombre', $persona['nombre'])
                    ->where('tipo_huesped', $tipo)->where('identificacion', $persona['identificacion'] ?? null)->count();
                if ($existentes === 1) {
                    continue;
                }
                if ($existentes > 1 || $destinos->count() !== 1) {
                    throw new RuntimeException('Acompanantes con destino ambiguo: revisar sin asignar la primera habitacion.');
                }
                DB::table('reserva_huespedes')->insert([
                    'reserva_detalle_id' => $destinos->first(), 'nombre' => $persona['nombre'],
                    'identificacion' => $persona['identificacion'] ?? null, 'tipo_huesped' => $tipo,
                    'es_titular' => false, 'created_at' => $reserva->created_at ?? now(), 'updated_at' => now(),
                ]);
            }
        });
        Schema::table('reservas', fn (Blueprint $table) => $table->renameColumn('acompanantes', 'acompanantes_legacy'));
    }

    public function down(): void
    {
        Schema::table('reservas', fn (Blueprint $table) => $table->renameColumn('acompanantes_legacy', 'acompanantes'));
        DB::table('reservas')->orderBy('id')->each(function (object $reserva): void {
            $lista = DB::table('reserva_huespedes as h')->join('reserva_detalles as d', 'd.id', '=', 'h.reserva_detalle_id')
                ->where('d.reserva_id', $reserva->id)->whereNull('d.deleted_at')->where('h.es_titular', false)->orderBy('h.id')
                ->get(['h.nombre', 'h.identificacion', 'h.tipo_huesped'])->map(fn (object $h): array => [
                    'nombre' => $h->nombre, 'identificacion' => $h->identificacion,
                    'tipo' => match ((int) $h->tipo_huesped) {
                        2 => 'nino', 3 => 'bebe', default => 'adulto'
                    },
                ])->all();
            DB::table('reservas')->where('id', $reserva->id)->update(['acompanantes' => json_encode($lista, JSON_THROW_ON_ERROR)]);
        });
    }
};
