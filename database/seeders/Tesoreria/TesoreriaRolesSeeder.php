<?php

declare(strict_types=1);

namespace Database\Seeders\Tesoreria;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class TesoreriaRolesSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->call(CajaPermisosSeeder::class);
            foreach (['Create:Factura', 'ViewAny:Factura', 'View:Factura', 'Anular:Factura'] as $nombre) {
                Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
            }
            foreach ($this->perfiles() as $nombre => $permisos) {
                $rol = Role::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
                // Existing permissions remain intact; no users or balances are created.
                $rol->givePermissionTo($permisos);
            }
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** @return array<string,list<string>> */
    private function perfiles(): array
    {
        $cajero = ['ViewAny:Caja', 'Abrir:Caja', 'Operar:Caja', 'Cerrar:Caja', 'Entregar:Caja', 'Solicitar:FondoTurno', 'Solicitar:Devolucion', 'Create:Factura', 'ViewAny:Factura', 'View:Factura'];
        $supervisor = array_merge($cajero, ['Create:Caja', 'Egreso:Caja', 'CerrarDiferencia:Caja', 'Auditar:Caja', 'Aprobar:Devolucion', 'Autorizar:FondoTurno', 'Recibir:Fondos']);
        $finanzas = array_merge($supervisor, ['ViewAny:Fondos', 'Administrar:Fondos', 'Transferir:Fondos', 'Operar:CajaChica', 'Aprobar:CajaChica', 'Registrar:Extracto', 'Conciliar:Banco', 'Revisar:Comprobante', 'Auditar:Fondos', 'Anular:Factura']);
        $auditor = ['ViewAny:Caja', 'Auditar:Caja', 'ViewAny:Fondos', 'Auditar:Fondos', 'ViewAny:Factura', 'View:Factura'];

        return [
            'cajero' => $cajero,
            'recepcionista' => $cajero,
            'recepcion_encargado' => $cajero,
            'restaurante_encargado' => $cajero,
            'supervisor_caja' => $supervisor,
            'recepcion_supervisor' => $supervisor,
            'tesoreria' => $finanzas,
            'contabilidad' => $finanzas,
            'admin' => $finanzas,
            'administrador' => $finanzas,
            'gerente' => $finanzas,
            'responsable_caja_chica' => ['Operar:CajaChica'],
            'auditor_financiero' => $auditor,
        ];
    }
}
