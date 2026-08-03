<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('movimientos_cuenta_estancia');
        Schema::dropIfExists('cuentas_estancia');
    }

    public function down(): void
    {
        // No hay rollback: tablas legacy eliminadas permanentemente
    }
};
