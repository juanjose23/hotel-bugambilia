<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('habitaciones', function (Blueprint $table) {
            $table->foreignId('reservable_id')
                ->nullable()
                ->unique()
                ->constrained('recursos_reservables')
                ->nullOnDelete();
        });

        Schema::table('espacios', function (Blueprint $table) {
            $table->foreignId('reservable_id')
                ->nullable()
                ->unique()
                ->constrained('recursos_reservables')
                ->nullOnDelete();
        });

        Schema::table('servicios', function (Blueprint $table) {
            $table->foreignId('reservable_id')
                ->nullable()
                ->unique()
                ->constrained('recursos_reservables')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reservable_id');
        });

        Schema::table('espacios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reservable_id');
        });

        Schema::table('habitaciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reservable_id');
        });
    }
};
