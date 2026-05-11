<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea las tablas de autenticación de Laravel + cuentas sociales.
     * users: usuarios del sistema con acceso al panel admin.
     * social_accounts: autenticación OAuth (Google, Facebook, etc.).
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')
                ->nullable()
                ->comment('Relación con la persona física a la que pertenece esta cuenta')
                ->constrained('personas')
                ->nullOnDelete();
            $table->string('name')->comment('Nombre de usuario visible en el sistema');
            $table->string('email')->unique()->comment('Correo electrónico para inicio de sesión');
            $table->timestamp('email_verified_at')->nullable()->comment('Fecha de verificación del correo');
            $table->string('password')->comment('Hash de la contraseña (bcrypt)');
            $table->boolean('is_admin')->default(false)->comment('Indica si tiene permisos de administrador');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token')->comment('Token de recuperación (hash)');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index()->comment('Usuario al que pertenece la sesión');
            $table->string('ip_address', 45)->nullable()->comment('Dirección IP desde donde se inició sesión');
            $table->text('user_agent')->nullable()->comment('Agente de usuario del navegador/cliente');
            $table->longText('payload')->comment('Datos serializados de la sesión');
            $table->integer('last_activity')->index()->comment('Timestamp UNIX de la última actividad');
        });
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->comment('Usuario al que pertenece esta cuenta social')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('provider')->comment('Proveedor OAuth (google, facebook, github, etc.)');
            $table->string('provider_id')->comment('ID único del usuario en el proveedor externo');
            $table->string('provider_email')->nullable()->comment('Correo asociado a la cuenta externa');
            $table->string('avatar')->nullable()->comment('URL del avatar del proveedor externo');
            $table->json('provider_data')->nullable()->comment('Datos adicionales devueltos por el proveedor');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['provider', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('social_accounts');
        Schema::dropIfExists('users');
    }
};
