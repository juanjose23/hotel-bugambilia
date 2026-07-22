<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_reserva', 50)->unique();
            $table->foreignId('cliente_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nombre_cliente', 150);
            $table->string('telefono_cliente', 50)->nullable();
            $table->string('email_cliente', 150)->nullable();
            $table->string('tipo_reserva', 30)->default('habitacion')->comment('habitacion, restaurante, servicio, paquete');

            $table->foreignId('habitacion_id')->nullable()->constrained('habitaciones')->nullOnDelete();
            $table->foreignId('espacio_id')->nullable()->constrained('espacios')->nullOnDelete();
            $table->foreignId('servicio_id')->nullable()->constrained('servicios')->nullOnDelete();

            $table->date('fecha_check_in');
            $table->date('fecha_check_out')->nullable();
            $table->string('hora_reserva', 20)->nullable();

            $table->integer('adultos')->default(1);
            $table->integer('ninos')->default(0);

            $table->string('estado', 30)->default('pendiente')->comment('pendiente, confirmada, checked_in, checked_out, cancelada');
            $table->decimal('total', 12, 2)->default(0.00);
            $table->text('notas')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
