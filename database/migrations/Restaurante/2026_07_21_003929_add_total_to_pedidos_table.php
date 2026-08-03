<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pedidos') && ! Schema::hasColumn('pedidos', 'total')) {
            Schema::table('pedidos', function (Blueprint $table): void {
                $table->decimal('total', 10, 2)->default(0)->after('subtotal');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pedidos') && Schema::hasColumn('pedidos', 'total')) {
            Schema::table('pedidos', function (Blueprint $table): void {
                $table->dropColumn('total');
            });
        }
    }
};
