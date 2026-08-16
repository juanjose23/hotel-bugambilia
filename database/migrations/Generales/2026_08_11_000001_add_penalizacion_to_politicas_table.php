<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('politicas', function (Blueprint $table) {
            $table->boolean('aplica_penalizacion')
                ->default(false)
                ->after('descripcion')
                ->comment('Indica si esta politica define reglas ejecutables de penalizacion por cancelacion');
        });
    }

    public function down(): void
    {
        Schema::table('politicas', function (Blueprint $table) {
            $table->dropColumn('aplica_penalizacion');
        });
    }
};
