<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->comment('Tabla nativa de Laravel para registrar notificaciones persistentes enviadas a usuarios.');
            $table->uuid('id')->primary()->comment('Identificador único en formato UUID de la notificación');
            $table->string('type')->comment('Clase/Tipo de notificación de Laravel (namespace)');
            $table->morphs('notifiable');
            $table->text('data')->comment('Datos JSON o texto con el contenido y contexto de la notificación');
            $table->timestamp('read_at')->nullable()->comment('Fecha y hora en que el usuario leyó la notificación');
            $table->timestamps();
        });
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE notifications ALTER COLUMN data TYPE jsonb USING data::jsonb');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE notifications ALTER COLUMN data TYPE text');
        }
        Schema::dropIfExists('notifications');
    }
};
