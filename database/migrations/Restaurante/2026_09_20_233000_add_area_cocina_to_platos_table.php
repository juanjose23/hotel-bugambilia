<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platos', function (Blueprint $table): void {
            if (! Schema::hasColumn('platos', 'area_cocina')) {
                $table->string('area_cocina', 30)
                    ->default('cocina')
                    ->after('producto_receta_id')
                    ->comment('Área de cocina/barra: cocina, bar, postres, parrilla');
                $table->index('area_cocina');
            }
        });
    }

    public function down(): void
    {
        Schema::table('platos', function (Blueprint $table): void {
            if (Schema::hasColumn('platos', 'area_cocina')) {
                $table->dropIndex(['area_cocina']);
                $table->dropColumn('area_cocina');
            }
        });
    }
};
