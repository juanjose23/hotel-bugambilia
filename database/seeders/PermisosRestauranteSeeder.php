<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Repository\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

final class PermisosRestauranteSeeder extends Seeder
{
    /**
     * Modelos del módulo de restaurante y sus habilidades Shield.
     *
     * @var array<int, string>
     */
    private const MODELOS = ['Pedido', 'Plato', 'ProcesoCocina'];

    /**
     * @var array<int, string>
     */
    private const ABILITIES = [
        'ViewAny',
        'View',
        'Create',
        'Update',
        'Delete',
        'DeleteAny',
        'Restore',
        'ForceDelete',
        'ForceDeleteAny',
        'RestoreAny',
        'Replicate',
        'Reorder',
    ];

    /**
     * Páginas Filament del módulo (HasPageShield).
     *
     * @var array<int, string>
     */
    private const PAGINAS = [
        'page_GestionMesas',
        'page_CocinaPedidos',
        'page_PantallaPedidos',
        'page_ReportesRestaurante',
    ];

    /**
     * Permisos personalizados del módulo.
     *
     * @var array<int, string>
     */
    private const PERMISOS_CUSTOM = [
        'Restaurante:ImprimirComanda',
    ];

    public function run(): void
    {
        $permisos = collect(self::ABILITIES)
            ->flatMap(
                fn (string $ability): array => array_map(
                    fn (string $modelo): string => $ability.':'.$modelo,
                    self::MODELOS,
                )
            )
            ->merge(self::PAGINAS)
            ->merge(self::PERMISOS_CUSTOM)
            ->unique()
            ->values();

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        $nombresPermisos = $permisos->all();

        $operadores = User::query()
            ->where(function ($query) use ($nombresPermisos): void {
                $query
                    ->where('is_admin', true)
                    ->orWhereHas('roles', fn ($roles) => $roles->where('name', config('filament-shield.super_admin.name', 'super_admin')))
                    ->orWhereHas('permissions', fn ($permissions) => $permissions->whereIn('name', $nombresPermisos));
            })
            ->get();

        foreach ($operadores as $operador) {
            $operador->givePermissionTo($nombresPermisos);
        }
    }
}
