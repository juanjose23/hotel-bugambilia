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
        $reservas = DB::table('reservas')
            ->whereNotNull('meta_datos')
            ->select('id', 'meta_datos')
            ->get();

        foreach ($reservas as $reserva) {
            $meta = is_array($reserva->meta_datos) ? $reserva->meta_datos : [];
            $now = now()->toDateTimeString();

            if (isset($meta['platos_preordenados']) && is_array($meta['platos_preordenados'])) {
                DB::table('reservas_bitacora')->insert([
                    'reserva_id' => $reserva->id,
                    'tipo' => 'preorden',
                    'datos' => json_encode(['items' => $meta['platos_preordenados']]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            if (isset($meta['resumen_restaurante']) && is_array($meta['resumen_restaurante'])) {
                DB::table('reservas_bitacora')->insert([
                    'reserva_id' => $reserva->id,
                    'tipo' => 'resumen_restaurante',
                    'datos' => json_encode($meta['resumen_restaurante']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            if (isset($meta['politica_pago']) && is_array($meta['politica_pago'])) {
                DB::table('reservas_bitacora')->insert([
                    'reserva_id' => $reserva->id,
                    'tipo' => 'politica_pago',
                    'datos' => json_encode($meta['politica_pago']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            if (isset($meta['stripe']) && is_array($meta['stripe'])) {
                DB::table('reservas_bitacora')->insert([
                    'reserva_id' => $reserva->id,
                    'tipo' => 'stripe',
                    'datos' => json_encode($meta['stripe']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            if (isset($meta['cancelacion']) && is_array($meta['cancelacion'])) {
                DB::table('reservas_bitacora')->insert([
                    'reserva_id' => $reserva->id,
                    'tipo' => 'cancelacion',
                    'datos' => json_encode($meta['cancelacion']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        Schema::table('reservas', function (Blueprint $table): void {
            $table->dropColumn('meta_datos');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table): void {
            $table->json('meta_datos')->nullable()->after('saldo');
        });

        $bitacoras = DB::table('reservas_bitacora')
            ->select('reserva_id', 'tipo', 'datos')
            ->orderBy('reserva_id')
            ->orderBy('tipo')
            ->get()
            ->groupBy('reserva_id');

        foreach ($bitacoras as $reservaId => $entries) {
            $meta = [];
            foreach ($entries as $entry) {
                $datos = is_string($entry->datos) ? json_decode($entry->datos, true) : $entry->datos;
                match ($entry->tipo) {
                    'preorden' => $meta['platos_preordenados'] = $datos['items'] ?? $datos,
                    'resumen_restaurante' => $meta['resumen_restaurante'] = $datos,
                    'politica_pago' => $meta['politica_pago'] = $datos,
                    'stripe' => $meta['stripe'] = $datos,
                    'cancelacion' => $meta['cancelacion'] = $datos,
                    default => null,
                };
            }
            DB::table('reservas')->where('id', $reservaId)->update(['meta_datos' => json_encode($meta)]);
        }

        Schema::dropIfExists('reservas_bitacora');
    }
};
