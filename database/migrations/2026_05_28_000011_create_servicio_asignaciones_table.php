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
        Schema::create('servicio_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->morphs('serviceable');
            $table->foreignId('servicio_id')->constrained('servicios');
            $table->boolean('incluido')->default(false);
            $table->tinyInteger('estado')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['serviceable_type', 'serviceable_id', 'servicio_id', 'deleted_at'], 'uq_servicio_asignacion');
        });

        $driver = DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE servicio_asignaciones DROP CONSTRAINT IF EXISTS uq_servicio_asignacion');
            DB::statement('CREATE UNIQUE INDEX uq_servicio_asignacion ON servicio_asignaciones (serviceable_type, serviceable_id, servicio_id) WHERE deleted_at IS NULL');
        } elseif ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS uq_servicio_asignacion');
            DB::statement('CREATE UNIQUE INDEX uq_servicio_asignacion ON servicio_asignaciones (serviceable_type, serviceable_id, servicio_id) WHERE deleted_at IS NULL');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('servicio_asignaciones');
    }
};
