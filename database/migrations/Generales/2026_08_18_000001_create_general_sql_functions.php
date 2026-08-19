<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $pdo = DB::connection()->getPdo();
        $pdo->exec($this->sql('General/TipoObtenerNombre.sql'));
        $pdo->exec($this->sql('General/FuncionObtenerNombre.sql'));
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP FUNCTION IF EXISTS obtener_nombre_completo(INT)');
        DB::statement('DROP DOMAIN IF EXISTS nombre_completo_t');
    }

    private function sql(string $path): string
    {
        return file_get_contents(database_path("funciones/{$path}")) ?: '';
    }
};
