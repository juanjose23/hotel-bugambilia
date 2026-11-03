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
        Schema::table('promociones', function (Blueprint $table): void {
            $table->unsignedSmallInteger('noches_incluidas')->nullable();
            $table->unsignedSmallInteger('personas_incluidas')->nullable();
            $table->unsignedSmallInteger('anticipacion_horas')->default(0);
        });
        foreach (['PROM-ROMANTICA' => [1, 2, 48], 'PROM-ESTANCIA7X6' => [7, 2, 0], 'PROM-GASTRONOMICO' => [2, 2, 0]] as $codigo => [$noches, $personas, $horas]) {
            DB::table('promociones')->where('codigo', $codigo)->update([
                'noches_incluidas' => $noches, 'personas_incluidas' => $personas, 'anticipacion_horas' => $horas,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('promociones', fn (Blueprint $table) => $table->dropColumn(['noches_incluidas', 'personas_incluidas', 'anticipacion_horas']));
    }
};
