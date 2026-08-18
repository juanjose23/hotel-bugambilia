<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarjeta_cliente', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->cascadeOnDelete();
            $table->string('stripe_payment_method_id', 120)->unique();
            $table->string('stripe_customer_id', 120);
            $table->string('ultimo_digitos', 4);
            $table->string('marca', 20)->comment('visa, mastercard, amex, etc.');
            $table->tinyInteger('exp_month');
            $table->smallInteger('exp_year');
            $table->boolean('es_predeterminada')->default(true);
            $table->timestamp('eliminado_at')->nullable();
            $table->timestamps();

            $table->index('cliente_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarjeta_cliente');
    }
};
