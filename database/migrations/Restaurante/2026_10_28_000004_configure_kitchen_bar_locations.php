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
        Schema::table('espacios', function (Blueprint $table): void {
            $table->foreignId('ubicacion_cocina_id')->nullable()->constrained('ubicaciones')->restrictOnDelete();
            $table->foreignId('ubicacion_bar_id')->nullable()->constrained('ubicaciones')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::table('espacios')->whereNotNull('ubicacion_cocina_id')->orWhereNotNull('ubicacion_bar_id')->exists()) {
            throw new RuntimeException('No se puede revertir sin revisar las ubicaciones configuradas de cocina y bar.');
        }
        Schema::table('espacios', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('ubicacion_cocina_id');
            $table->dropConstrainedForeignId('ubicacion_bar_id');
        });
    }
};
