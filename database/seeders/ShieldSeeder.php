<?php

namespace Database\Seeders;

use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $tenants = '[]';
        $users = '[]';
        $userTenantPivot = '[]';
        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["ViewAny:AuditoriaReporte","View:AuditoriaReporte","Create:AuditoriaReporte","Update:AuditoriaReporte","Delete:AuditoriaReporte","DeleteAny:AuditoriaReporte","Restore:AuditoriaReporte","ForceDelete:AuditoriaReporte","ForceDeleteAny:AuditoriaReporte","RestoreAny:AuditoriaReporte","Replicate:AuditoriaReporte","Reorder:AuditoriaReporte","ViewAny:Audit","View:Audit","Create:Audit","Update:Audit","Delete:Audit","DeleteAny:Audit","Restore:Audit","ForceDelete:Audit","ForceDeleteAny:Audit","RestoreAny:Audit","Replicate:Audit","Reorder:Audit","ViewAny:CatalogoTipo","View:CatalogoTipo","Create:CatalogoTipo","Update:CatalogoTipo","Delete:CatalogoTipo","DeleteAny:CatalogoTipo","Restore:CatalogoTipo","ForceDelete:CatalogoTipo","ForceDeleteAny:CatalogoTipo","RestoreAny:CatalogoTipo","Replicate:CatalogoTipo","Reorder:CatalogoTipo","ViewAny:Catalogo","View:Catalogo","Create:Catalogo","Update:Catalogo","Delete:Catalogo","DeleteAny:Catalogo","Restore:Catalogo","ForceDelete:Catalogo","ForceDeleteAny:Catalogo","RestoreAny:Catalogo","Replicate:Catalogo","Reorder:Catalogo","ViewAny:Pais","View:Pais","Create:Pais","Update:Pais","Delete:Pais","DeleteAny:Pais","Restore:Pais","ForceDelete:Pais","ForceDeleteAny:Pais","RestoreAny:Pais","Replicate:Pais","Reorder:Pais","ViewAny:Producto","View:Producto","Create:Producto","Update:Producto","Delete:Producto","DeleteAny:Producto","Restore:Producto","ForceDelete:Producto","ForceDeleteAny:Producto","RestoreAny:Producto","Replicate:Producto","Reorder:Producto","ViewAny:Ubicacion","View:Ubicacion","Create:Ubicacion","Update:Ubicacion","Delete:Ubicacion","DeleteAny:Ubicacion","Restore:Ubicacion","ForceDelete:Ubicacion","ForceDeleteAny:Ubicacion","RestoreAny:Ubicacion","Replicate:Ubicacion","Reorder:Ubicacion","ViewAny:ColaboradorCargoHistorial","View:ColaboradorCargoHistorial","Create:ColaboradorCargoHistorial","Update:ColaboradorCargoHistorial","Delete:ColaboradorCargoHistorial","DeleteAny:ColaboradorCargoHistorial","Restore:ColaboradorCargoHistorial","ForceDelete:ColaboradorCargoHistorial","ForceDeleteAny:ColaboradorCargoHistorial","RestoreAny:ColaboradorCargoHistorial","Replicate:ColaboradorCargoHistorial","Reorder:ColaboradorCargoHistorial","ViewAny:ColaboradorContactoEmergencia","View:ColaboradorContactoEmergencia","Create:ColaboradorContactoEmergencia","Update:ColaboradorContactoEmergencia","Delete:ColaboradorContactoEmergencia","DeleteAny:ColaboradorContactoEmergencia","Restore:ColaboradorContactoEmergencia","ForceDelete:ColaboradorContactoEmergencia","ForceDeleteAny:ColaboradorContactoEmergencia","RestoreAny:ColaboradorContactoEmergencia","Replicate:ColaboradorContactoEmergencia","Reorder:ColaboradorContactoEmergencia","ViewAny:ColaboradorDatosMedicos","View:ColaboradorDatosMedicos","Create:ColaboradorDatosMedicos","Update:ColaboradorDatosMedicos","Delete:ColaboradorDatosMedicos","DeleteAny:ColaboradorDatosMedicos","Restore:ColaboradorDatosMedicos","ForceDelete:ColaboradorDatosMedicos","ForceDeleteAny:ColaboradorDatosMedicos","RestoreAny:ColaboradorDatosMedicos","Replicate:ColaboradorDatosMedicos","Reorder:ColaboradorDatosMedicos","ViewAny:ColaboradorDocumento","View:ColaboradorDocumento","Create:ColaboradorDocumento","Update:ColaboradorDocumento","Delete:ColaboradorDocumento","DeleteAny:ColaboradorDocumento","Restore:ColaboradorDocumento","ForceDelete:ColaboradorDocumento","ForceDeleteAny:ColaboradorDocumento","RestoreAny:ColaboradorDocumento","Replicate:ColaboradorDocumento","Reorder:ColaboradorDocumento","ViewAny:ColaboradorSalario","View:ColaboradorSalario","Create:ColaboradorSalario","Update:ColaboradorSalario","Delete:ColaboradorSalario","DeleteAny:ColaboradorSalario","Restore:ColaboradorSalario","ForceDelete:ColaboradorSalario","ForceDeleteAny:ColaboradorSalario","RestoreAny:ColaboradorSalario","Replicate:ColaboradorSalario","Reorder:ColaboradorSalario","ViewAny:Persona","View:Persona","Create:Persona","Update:Persona","Delete:Persona","DeleteAny:Persona","Restore:Persona","ForceDelete:Persona","ForceDeleteAny:Persona","RestoreAny:Persona","Replicate:Persona","Reorder:Persona","ViewAny:Cotizacion","View:Cotizacion","Create:Cotizacion","Update:Cotizacion","Delete:Cotizacion","DeleteAny:Cotizacion","Restore:Cotizacion","ForceDelete:Cotizacion","ForceDeleteAny:Cotizacion","RestoreAny:Cotizacion","Replicate:Cotizacion","Reorder:Cotizacion","ViewAny:OrdenCompra","View:OrdenCompra","Create:OrdenCompra","Update:OrdenCompra","Delete:OrdenCompra","DeleteAny:OrdenCompra","Restore:OrdenCompra","ForceDelete:OrdenCompra","ForceDeleteAny:OrdenCompra","RestoreAny:OrdenCompra","Replicate:OrdenCompra","Reorder:OrdenCompra","ViewAny:Proveedor","View:Proveedor","Create:Proveedor","Update:Proveedor","Delete:Proveedor","DeleteAny:Proveedor","Restore:Proveedor","ForceDelete:Proveedor","ForceDeleteAny:Proveedor","RestoreAny:Proveedor","Replicate:Proveedor","Reorder:Proveedor","ViewAny:RecepcionCompra","View:RecepcionCompra","Create:RecepcionCompra","Update:RecepcionCompra","Delete:RecepcionCompra","DeleteAny:RecepcionCompra","Restore:RecepcionCompra","ForceDelete:RecepcionCompra","ForceDeleteAny:RecepcionCompra","RestoreAny:RecepcionCompra","Replicate:RecepcionCompra","Reorder:RecepcionCompra","ViewAny:Solicitud","View:Solicitud","Create:Solicitud","Update:Solicitud","Delete:Solicitud","DeleteAny:Solicitud","Restore:Solicitud","ForceDelete:Solicitud","ForceDeleteAny:Solicitud","RestoreAny:Solicitud","Replicate:Solicitud","Reorder:Solicitud","ViewAny:Role","View:Role","Create:Role","Update:Role","Delete:Role","DeleteAny:Role","Restore:Role","ForceDelete:Role","ForceDeleteAny:Role","RestoreAny:Role","Replicate:Role","Reorder:Role","ViewAny:User","View:User","Create:User","Update:User","Delete:User","DeleteAny:User","Restore:User","ForceDelete:User","ForceDeleteAny:User","RestoreAny:User","Replicate:User","Reorder:User","ImprimirSolicitud","ImprimirCotizacion","ImprimirOrdenCompra","ImprimirRecepcion","ImprimirReportesCompras","ViewComparativaCotizaciones","ViewComparativaSolicitud"]},{"name":"compras","guard_name":"web","permissions":["ViewAny:Cotizacion","View:Cotizacion","Create:Cotizacion","Update:Cotizacion","Delete:Cotizacion","DeleteAny:Cotizacion","Restore:Cotizacion","ForceDelete:Cotizacion","ForceDeleteAny:Cotizacion","RestoreAny:Cotizacion","Replicate:Cotizacion","Reorder:Cotizacion","ViewAny:OrdenCompra","View:OrdenCompra","Create:OrdenCompra","Update:OrdenCompra","Delete:OrdenCompra","DeleteAny:OrdenCompra","Restore:OrdenCompra","ForceDelete:OrdenCompra","ForceDeleteAny:OrdenCompra","RestoreAny:OrdenCompra","Replicate:OrdenCompra","Reorder:OrdenCompra","ViewAny:Proveedor","View:Proveedor","Create:Proveedor","Update:Proveedor","Delete:Proveedor","DeleteAny:Proveedor","Restore:Proveedor","ForceDelete:Proveedor","ForceDeleteAny:Proveedor","RestoreAny:Proveedor","Replicate:Proveedor","Reorder:Proveedor","ViewAny:RecepcionCompra","View:RecepcionCompra","Create:RecepcionCompra","Update:RecepcionCompra","Delete:RecepcionCompra","DeleteAny:RecepcionCompra","Restore:RecepcionCompra","ForceDelete:RecepcionCompra","ForceDeleteAny:RecepcionCompra","RestoreAny:RecepcionCompra","Replicate:RecepcionCompra","Reorder:RecepcionCompra","ViewAny:Solicitud","View:Solicitud","Create:Solicitud","Update:Solicitud","Delete:Solicitud","DeleteAny:Solicitud","Restore:Solicitud","ForceDelete:Solicitud","ForceDeleteAny:Solicitud","RestoreAny:Solicitud","Replicate:Solicitud","Reorder:Solicitud","ImprimirSolicitud","ImprimirCotizacion","ImprimirOrdenCompra","ImprimirRecepcion","ImprimirReportesCompras","ViewComparativaCotizaciones","ViewComparativaSolicitud"]}]';
        $directPermissions = '{"228":{"name":"ViewComparativaSolicitud","guard_name":"web"},"241":{"name":"ViewComparativaCotizaciones","guard_name":"web"},"242":{"name":"ImprimirSolicitud","guard_name":"web"},"243":{"name":"ImprimirCotizacion","guard_name":"web"},"244":{"name":"ImprimirOrdenCompra","guard_name":"web"},"245":{"name":"ImprimirRecepcion","guard_name":"web"},"246":{"name":"ImprimirReportesCompras","guard_name":"web"}}';

        // 1. Seed tenants first (if present)
        if (! blank($tenants) && $tenants !== '[]') {
            static::seedTenants($tenants);
        }

        // 2. Seed roles with permissions
        static::makeRolesWithPermissions($rolesWithPermissions);

        // 3. Seed direct permissions
        static::makeDirectPermissions($directPermissions);

        // 4. Seed users with their roles/permissions (if present)
        if (! blank($users) && $users !== '[]') {
            static::seedUsers($users);
        }

        // 5. Seed user-tenant pivot (if present)
        if (! blank($userTenantPivot) && $userTenantPivot !== '[]') {
            static::seedUserTenantPivot($userTenantPivot);
        }

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function seedTenants(string $tenants): void
    {
        if (blank($tenantData = json_decode($tenants, true))) {
            return;
        }

        $tenantModel = '';
        if (blank($tenantModel)) {
            return;
        }

        foreach ($tenantData as $tenant) {
            $tenantModel::firstOrCreate(
                ['id' => $tenant['id']],
                $tenant
            );
        }
    }

    protected static function seedUsers(string $users): void
    {
        if (blank($userData = json_decode($users, true))) {
            return;
        }

        $userModel = 'App\Models\User';
        $tenancyEnabled = false;

        foreach ($userData as $data) {
            // Extract role/permission data before creating user
            $roles = $data['roles'] ?? [];
            $permissions = $data['permissions'] ?? [];
            $tenantRoles = $data['tenant_roles'] ?? [];
            $tenantPermissions = $data['tenant_permissions'] ?? [];
            unset($data['roles'], $data['permissions'], $data['tenant_roles'], $data['tenant_permissions']);

            $user = $userModel::firstOrCreate(
                ['email' => $data['email']],
                $data
            );

            // Handle tenancy mode - sync roles/permissions per tenant
            if ($tenancyEnabled && (! empty($tenantRoles) || ! empty($tenantPermissions))) {
                foreach ($tenantRoles as $tenantId => $roleNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncRoles($roleNames);
                }

                foreach ($tenantPermissions as $tenantId => $permissionNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncPermissions($permissionNames);
                }
            } else {
                // Non-tenancy mode
                if (! empty($roles)) {
                    $user->syncRoles($roles);
                }

                if (! empty($permissions)) {
                    $user->syncPermissions($permissions);
                }
            }
        }
    }

    protected static function seedUserTenantPivot(string $pivot): void
    {
        if (blank($pivotData = json_decode($pivot, true))) {
            return;
        }

        $pivotTable = '';
        if (blank($pivotTable)) {
            return;
        }

        foreach ($pivotData as $row) {
            $uniqueKeys = [];

            if (isset($row['user_id'])) {
                $uniqueKeys['user_id'] = $row['user_id'];
            }

            $tenantForeignKey = 'team_id';
            if (! blank($tenantForeignKey) && isset($row[$tenantForeignKey])) {
                $uniqueKeys[$tenantForeignKey] = $row[$tenantForeignKey];
            }

            if (! empty($uniqueKeys)) {
                DB::table($pivotTable)->updateOrInsert($uniqueKeys, $row);
            }
        }
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            return;
        }

        /** @var Model $roleModel */
        $roleModel = Utils::getRoleModel();
        /** @var Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        $tenancyEnabled = false;
        $teamForeignKey = 'team_id';

        foreach ($rolePlusPermissions as $rolePlusPermission) {
            $tenantId = $rolePlusPermission[$teamForeignKey] ?? null;

            // Set tenant context for role creation and permission sync
            if ($tenancyEnabled) {
                setPermissionsTeamId($tenantId);
            }

            $roleData = [
                'name' => $rolePlusPermission['name'],
                'guard_name' => $rolePlusPermission['guard_name'],
            ];

            // Include tenant ID in role data (can be null for global roles)
            if ($tenancyEnabled && ! blank($teamForeignKey)) {
                $roleData[$teamForeignKey] = $tenantId;
            }

            $role = $roleModel::firstOrCreate($roleData);

            if (! blank($rolePlusPermission['permissions'])) {
                $permissionModels = collect($rolePlusPermission['permissions'])
                    ->map(fn ($permission) => $permissionModel::firstOrCreate([
                        'name' => $permission,
                        'guard_name' => $rolePlusPermission['guard_name'],
                    ]))
                    ->all();

                $role->syncPermissions($permissionModels);
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (blank($permissions = json_decode($directPermissions, true))) {
            return;
        }

        /** @var Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        foreach ($permissions as $permission) {
            if ($permissionModel::whereName($permission['name'])->doesntExist()) {
                $permissionModel::create([
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ]);
            }
        }
    }
}
