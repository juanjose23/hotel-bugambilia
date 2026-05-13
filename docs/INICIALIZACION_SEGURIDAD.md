# Quick Initialization Steps

1. Install Shield Tables:
   php artisan shield:install

2. Generate Global Permissions:
   php artisan shield:generate --all

3. Generate Shield Seeder (snapshot de permisos actuales):
   php artisan shield:seeder

4. Create First Super Admin:
   php artisan shield:super-admin

5. Run seeders (incluye ShieldSeeder):
   php artisan db:seed

> [!IMPORTANT]
> El `super_admin` ahora usa `define_via_gate => true` en `config/filament-shield.php`, lo que le permite saltarse todas las comprobaciones de permisos vía `Gate::before()`.

# Custom Permissions (Reportes)

Las siguientes custom permissions deben existir en la BD y estar asignadas al rol `super_admin` (y cualquier otro rol que necesite descargar reportes):

| Permiso | Propósito |
|---------|-----------|
| `ImprimirSolicitud` | Descargar PDF de Solicitud de Compra (HTB-COM-001) |
| `ImprimirCotizacion` | Descargar PDF de Cotización (HTB-COM-002) |
| `ImprimirOrdenCompra` | Descargar PDF de Orden de Compra (HTB-COM-003) |
| `ImprimirRecepcion` | Descargar PDF de Recepción de Mercancía (HTB-COM-004) |
| `ImprimirReportesCompras` | Descargar PDF de Resumen por Departamentos (HTB-COM-005) |
| `ViewComparativaCotizaciones` | Acceder al dashboard de comparativa de cotizaciones |

Se definen en `config/filament-shield.php` → `custom_permissions`. Si faltan, regenerar con:

```bash
php artisan shield:generate --all
# Luego re-asignar al super_admin desde la UI: Shield → Roles → super_admin → editar → guardar
```

O bien, asignar todas directo:
```php
$sa = Role::where('name', 'super_admin')->first();
$sa->syncPermissions(Permission::all()->pluck('name')->toArray());
```

# Troubleshooting

Si botones o páginas no aparecen tras asignar permisos:
- `php artisan cache:forget spatie.permission.cache`
- Asegurar que el usuario tenga el rol `panel_user` además de su rol específico.
- Si el usuario es `super_admin`, verificar `define_via_gate` en la configuración.
