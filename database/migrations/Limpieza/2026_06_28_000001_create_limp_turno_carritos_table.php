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
        Schema::create('limp_turno_carritos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turno_id')->constrained('limp_horario_turnos')->cascadeOnDelete();
            $table->foreignId('ubicacion_id')->constrained('ubicaciones')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['turno_id', 'ubicacion_id']);
        });

        // Migrate existing data from JSON carritos_ids to pivot table
        $turnos = DB::table('limp_horario_turnos')
            ->whereNotNull('carritos_ids')
            ->get(['id', 'carritos_ids']);

        foreach ($turnos as $turno) {
            $decoded = json_decode($turno->carritos_ids, true);
            /** @var list<int|string> $decodedList */
            $decodedList = is_array($decoded) ? $decoded : [];
            $carritosIds = array_map(static fn (int|string $v): int => (int) $v, $decodedList);
            $rows = array_map(fn (int $ubicacionId) => [
                'turno_id' => $turno->id,
                'ubicacion_id' => $ubicacionId,
                'created_at' => now(),
                'updated_at' => now(),
            ], $carritosIds);

            if ($rows !== []) {
                DB::table('limp_turno_carritos')->insert($rows);
            }
        }
        // Drop redundant JSON column after data migration
        Schema::table('limp_horario_turnos', function (Blueprint $table) {
            $table->dropColumn('carritos_ids');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('limp_turno_carritos');
    }
};
