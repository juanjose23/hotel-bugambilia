<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de auditoría del paquete owen-it/laravel-auditing.
     * Registra automáticamente todas las operaciones CRUD sobre los
     * modelos auditados (crear, actualizar, eliminar, restaurar).
     * Almacena valores anteriores y nuevos, URL, IP, agente de usuario.
     */
    public function up(): void
    {
        $connVal = config('audit.drivers.database.connection') ?? config('database.default') ?? '';
        $connection = is_scalar($connVal) ? (string) $connVal : '';
        $tableVal = config('audit.drivers.database.table') ?? 'audits';
        $table = is_scalar($tableVal) ? (string) $tableVal : 'audits';

        Schema::connection($connection)->create($table, function (Blueprint $table) {
            $table->comment('Tabla nativa de laravel-auditing que registra todas las operaciones CRUD detalladas efectuadas sobre modelos auditables.');
            $morphPrefixVal = config('audit.user.morph_prefix', 'user');
            $morphPrefix = is_scalar($morphPrefixVal) ? (string) $morphPrefixVal : 'user';

            $table->bigIncrements('id')->comment('Identificador único autoincremental de la auditoría');
            $table->string($morphPrefix.'_type')->nullable()->comment('Tipo de modelo del usuario que realizó la acción');
            $table->unsignedBigInteger($morphPrefix.'_id')->nullable()->comment('ID del usuario que realizó la acción');
            $table->string('event')->comment('Evento registrado: created, updated, deleted, restored');
            $table->morphs('auditable');
            $table->text('old_values')->nullable()->comment('Valores anteriores en formato JSON');
            $table->text('new_values')->nullable()->comment('Valores nuevos en formato JSON');
            $table->text('url')->nullable()->comment('URL donde se realizó la acción');
            $table->ipAddress('ip_address')->nullable()->comment('Dirección IP del usuario');
            $table->string('user_agent', 1023)->nullable()->comment('Agente de usuario del navegador');
            $table->string('tags')->nullable()->comment('Etiquetas adicionales del evento');
            $table->timestamps();

            $table->index([$morphPrefix.'_id', $morphPrefix.'_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connVal = config('audit.drivers.database.connection') ?? config('database.default') ?? '';
        $connection = is_scalar($connVal) ? (string) $connVal : '';
        $tableVal = config('audit.drivers.database.table') ?? 'audits';
        $table = is_scalar($tableVal) ? (string) $tableVal : 'audits';

        Schema::connection($connection)->drop($table);
    }
};
