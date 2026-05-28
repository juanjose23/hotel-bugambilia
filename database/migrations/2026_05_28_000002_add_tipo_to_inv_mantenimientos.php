<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inv_mantenimientos', function (Blueprint $table) {
            $table->string('tipo', 50)->nullable()->after('plan_id')->comment('Tipo de mantenimiento (preventivo, correctivo, garantia, inspeccion)');
        });
    }

    public function down(): void
    {
        Schema::table('inv_mantenimientos', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
