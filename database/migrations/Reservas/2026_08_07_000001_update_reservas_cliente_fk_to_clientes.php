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
        DB::transaction(function (): void {
            $reservas = DB::table('reservas')
                ->select('id', 'cliente_id')
                ->whereNotNull('cliente_id')
                ->get();

            foreach ($reservas as $reserva) {
                $clienteId = DB::table('users')
                    ->join('personas', 'personas.id', '=', 'users.persona_id')
                    ->join('clientes', 'clientes.persona_id', '=', 'personas.id')
                    ->where('users.id', $reserva->cliente_id)
                    ->value('clientes.id');

                if ($clienteId !== null) {
                    DB::table('reservas')
                        ->where('id', $reserva->id)
                        ->update(['cliente_id' => $clienteId]);
                }
            }
        });

        Schema::table('reservas', function (Blueprint $table): void {
            $table->dropForeign(['cliente_id']);
            $table->foreign('cliente_id')->references('id')->on('clientes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table): void {
            $table->dropForeign(['cliente_id']);
            $table->foreign('cliente_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
