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
            $table->string('codigo_reserva', 50)->unique()->comment('Código correlativo único de reservación');
            $table->foreignId('cliente_id')->nullable()->comment('FK al usuario titular si está registrado')->constrained('users')->nullOnDelete();
            $table->string('nombre_cliente', 150)->comment('Nombre completo del titular de la reserva');
            $table->string('telefono_cliente', 50)->nullable()->comment('Teléfono móvil o contacto del cliente');
            $table->string('email_cliente', 150)->nullable()->comment('Correo electrónico de confirmaciones');
            $table->string('tipo_reserva', 30)->default('habitacion')->comment('Tipo de reserva: habitacion, restaurante, servicio, paquete');

            $table->foreignId('habitacion_id')->nullable()->comment('FK a habitación reservada si aplica')->constrained('habitaciones')->nullOnDelete();
            $table->foreignId('espacio_id')->nullable()->comment('FK a mesa o espacio asignado')->constrained('espacios')->nullOnDelete();
            $table->foreignId('servicio_id')->nullable()->comment('FK a servicio contratado')->constrained('servicios')->nullOnDelete();
            $table->foreignId('promocion_id')->nullable()->comment('FK a promoción/cupón aplicado')->constrained('promociones')->nullOnDelete();

            $table->date('fecha_check_in')->comment('Fecha de llegada / inicio de reservación');
            $table->date('fecha_check_out')->nullable()->comment('Fecha de salida / fin de reservación');
            $table->string('hora_reserva', 20)->nullable()->comment('Hora pactada para reservación de restaurante o servicio');

            $table->integer('adultos')->default(1)->comment('Número de adultos huéspedes/comensales');
            $table->integer('ninos')->default(0)->comment('Número de niños huéspedes/comensales');
            $table->json('acompanantes')->nullable()->comment('Lista detallada de acompañantes registrados (JSON)');

            $table->unsignedTinyInteger('estado')->default(1)
                ->comment('Estado de la reserva: 1=pendiente, 2=confirmada, 3=checked_in, 4=checked_out, 5=cancelada');

            $table->decimal('subtotal', 12, 2)->nullable()->comment('Subtotal antes de promociones e impuestos');
            $table->decimal('descuento', 12, 2)->default(0.00)->comment('Monto total descontado');
            $table->decimal('total', 12, 2)->default(0.00)->comment('Monto total final a pagar');
            $table->text('notas')->nullable()->comment('Observaciones o solicitudes especiales del cliente');

            $table->timestamps();
            $table->softDeletes();

            $table->index('cliente_id');
            $table->index('email_cliente');
            $table->index('tipo_reserva');
            $table->index('habitacion_id');
            $table->index('espacio_id');
            $table->index('estado');
            $table->index(['fecha_check_in', 'fecha_check_out']);
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
