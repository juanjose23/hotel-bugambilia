<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $teams = (bool) config('permission.teams');
        /** @var array<string, string> $tableNames */
        $tableNames = (array) config('permission.table_names');
        /** @var array<string, string> $columnNames */
        $columnNames = (array) config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        throw_if(empty($tableNames), 'Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        throw_if($teams && empty($columnNames['team_foreign_key'] ?? null), 'Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');

        /**
         * See `docs/prerequisites.md` for suggested lengths on 'name' and 'guard_name' if "1071 Specified key was too long" errors are encountered.
         */
        Schema::create($tableNames['permissions'], static function (Blueprint $table) {
            $table->comment('Tabla nativa de Spatie que almacena los permisos individuales definidos en el sistema.');
            $table->id()->comment('Identificador único autoincremental del permiso'); // permission id
            $table->string('name')->comment('Nombre único del permiso en PascalCase');
            $table->string('guard_name')->comment('Nombre de guard de Laravel (ej. web)');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        /**
         * See `docs/prerequisites.md` for suggested lengths on 'name' and 'guard_name' if "1071 Specified key was too long" errors are encountered.
         */
        Schema::create($tableNames['roles'], static function (Blueprint $table) use ($teams, $columnNames) {
            $table->comment('Tabla nativa de Spatie que almacena los roles organizacionales del sistema.');
            $table->id()->comment('Identificador único autoincremental del rol'); // role id
            if ($teams || (bool) config('permission.testing')) { // permission.testing is a fix for sqlite testing
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable()->comment('FK al equipo (multitenancy)');
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
            $table->string('name')->comment('Nombre único del rol');
            $table->string('guard_name')->comment('Nombre de guard de Laravel (ej. web)');
            $table->timestamps();
            if ($teams || (bool) config('permission.testing')) {
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }
        });

        Schema::create('model_has_permissions', static function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams) {
            $table->comment('Tabla nativa de Spatie (relación polimórfica N:M) para asignar permisos específicos a modelos o usuarios.');
            $table->unsignedBigInteger($pivotPermission)->comment('FK al permiso asignado');

            $table->string('model_type')->comment('Nombre de clase (namespace) del modelo asociado');
            $table->unsignedBigInteger($columnNames['model_morph_key'])->comment('ID único del modelo asociado');
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign($pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->cascadeOnDelete();
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], $pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            } else {
                $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            }
        });

        Schema::create('model_has_roles', static function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams) {
            $table->comment('Tabla nativa de Spatie (relación polimórfica N:M) para asignar roles organizacionales a modelos o usuarios.');
            $table->unsignedBigInteger($pivotRole)->comment('FK al rol asignado');

            $table->string('model_type')->comment('Nombre de clase (namespace) del modelo asociado');
            $table->unsignedBigInteger($columnNames['model_morph_key'])->comment('ID único del modelo asociado');
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign($pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->cascadeOnDelete();
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            } else {
                $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            }
        });

        Schema::create($tableNames['role_has_permissions'], static function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
            $table->comment('Tabla nativa de Spatie (tabla pivote) que gestiona la asignación N:M de permisos específicos a cada rol.');
            $table->unsignedBigInteger($pivotPermission)->comment('FK al permiso');
            $table->unsignedBigInteger($pivotRole)->comment('FK al rol');

            $table->foreign($pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->cascadeOnDelete();

            $table->foreign($pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->cascadeOnDelete();

            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        $cacheStoreVal = config('permission.cache.store');
        $cacheStore = is_scalar($cacheStoreVal) ? (string) $cacheStoreVal : 'default';

        $cacheKeyVal = config('permission.cache.key');
        $cacheKey = is_scalar($cacheKeyVal) ? (string) $cacheKeyVal : 'spatie.permission.cache';

        app('cache')
            ->store($cacheStore !== 'default' ? $cacheStore : null)
            ->forget($cacheKey);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /** @var array<string, string> $tableNames */
        $tableNames = (array) config('permission.table_names');

        throw_if(empty($tableNames), 'Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');

        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
    }
};
