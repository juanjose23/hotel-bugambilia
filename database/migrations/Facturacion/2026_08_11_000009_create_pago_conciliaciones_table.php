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
        Schema::create('pago_conciliaciones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pago_transaccion_id')->unique()->constrained('pago_transacciones')->restrictOnDelete();
            $table->unsignedTinyInteger('estado')->default(1)->index();
            $table->decimal('monto_esperado', 14, 2)->default(0);
            $table->decimal('monto_recibido', 14, 2)->default(0);
            $table->decimal('diferencia', 14, 2)->default(0);
            $table->timestamp('conciliada_at')->nullable();
            $table->foreignId('conciliada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('observaciones', 500)->nullable();
            $table->jsonb('meta_datos')->nullable();
            $table->timestamps();
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE pago_conciliaciones ADD CONSTRAINT chk_pc_estado CHECK (estado IN (1, 2, 3, 4))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pago_conciliaciones');
    }
};
