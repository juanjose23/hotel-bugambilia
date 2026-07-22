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
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS users_persona_id_unique ON users (persona_id) WHERE persona_id IS NOT NULL');
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('persona_id', 'users_persona_id_unique');
            });
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS users_persona_id_unique');
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_persona_id_unique');
            });
        }
    }
};
