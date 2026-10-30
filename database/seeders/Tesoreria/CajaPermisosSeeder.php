<?php

declare(strict_types=1);

namespace Database\Seeders\Tesoreria;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

final class CajaPermisosSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['ViewAny:Caja', 'Create:Caja', 'Abrir:Caja', 'Operar:Caja', 'Egreso:Caja', 'Cerrar:Caja', 'CerrarDiferencia:Caja', 'Auditar:Caja', 'ViewAny:Fondos', 'Administrar:Fondos', 'Transferir:Fondos', 'Operar:CajaChica', 'Aprobar:CajaChica', 'Registrar:Extracto', 'Conciliar:Banco', 'Revisar:Comprobante', 'Solicitar:Devolucion', 'Aprobar:Devolucion', 'Entregar:Caja', 'Recibir:Fondos', 'Auditar:Fondos', 'Solicitar:FondoTurno', 'Autorizar:FondoTurno'] as $nombre) {
            Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
