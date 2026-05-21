# AGENTS.md - Hotel Bugambilias

## Quick Commands

```bash
composer test          # Run tests (clears config first)
composer pint          # Laravel Pint linting
composer phpstan       # PHPStan level 6 (needs 1GB memory)
composer dev           # Full dev: PHP server + queue + Vite
php artisan db:seed --class=Database\\Seeders\\{Name}  # Run single seeder
php artisan migrate:fresh --seed  # Full DB refresh + all seeders
```

## Architecture

- **Framework**: Laravel 13 + Filament 5 admin panel
- **Admin panel**: `/admin` path, brand "Hotel Bugambilias"
- **Testing**: Pest PHP 4.x
- **Excel Library**: `maatwebsite/excel` (Official for all .xlsx exports)

## Filament Resource Structure

Resources auto-discovered from `app/Filament/Resources/`. Each resource follows:

```
app/Filament/Resources/{Group}/{ResourceName}/
├── {ResourceName}Resource.php   # Main resource class
├── Schemas/                    # Form schemas
│   └── {ResourceName}Form.php
├── Tables/                     # Table configs
│   └── {ResourceName}Table.php
└── Pages/                     # Page classes
    ├── List{Name}.php
    ├── Create{Name}.php
    ├── Edit{Name}.php
    └── View{Name}.php
```

## Model Namespaces

```
App\Models\Colaboradores\        # Colaborador, ColaboradorDatosMedicos, etc.
App\Filament\Resources\Colaboradores\  # Resource classes
```

## Use Cases Pattern

Use Cases organized as Queries (read) / Mutations (write) per module:

```
app/UseCases/{Module}/{Queries|Mutations}/
app/UseCases/{Module}/{SubModule}/{Queries|Mutations}/   (e.g. Compras/Recepciones)
```

Direct Eloquent calls in Use Cases (no repositories, no DDD layers). Domain logic helpers go in `Services/` subdirectory.

## Inventario Module

Module at `app/UseCases/Inventario/` with 4 use cases:
- `RegistrarEntradaRecepcion` (UC-01) — triggered by `RecepcionInventoryObserver` on reception state change
- `LiberarLotesCuarentena` (UC-02) — bulk action in LoteResource
- `ConsumirStock` (UC-03) — FEFO-based stock consumption via `FEFOStrategy`
- `VerificarCaducidades` (UC-04) — scheduled daily at 06:00 via `routes/console.php`

Models: `App\Models\Inventario\Lote` (table `inv_lotes`), `App\Models\Inventario\MovimientoStock` (table `inv_movimientos`).
Reuses `ubicaciones` table (tipo `almacen`) for inventory locations — no separate `inv_almacenes/zona/ubicaciones` tables.

Observers: `App\Observers\Inventario\RecepcionInventoryObserver` registered in `AppServiceProvider::boot()`.

Filament resources at `app/Filament/Resources/Inventario/{Lote,MovimientoStock}/`.

## PHPStan

- Level 6, analyzes `app/`, `routes/`, `database/`
- Config: `phpstan.neon` (includes Larastan extension)

## Common Gotchas

- `ColaboradorForm` has two schema methods: `getRegistroInicialSchema()` and `getEdicionCompletaSchema()`
- Page class files sometimes get stray `$` at end of class declarations - always run `php -l` after editing
- Use `->configure($table)` not `::configure($table)` for table methods
- Filament form methods: `Schema $schema` parameter, return `$schema->components(...)`
- UbicacionSeeder seeds `tipo='almacen'` for the Almacén General — PutawayPolicy picks the first active one
- RecepcionItem `$fillable` includes `lote_proveedor` and `fecha_vencimiento` — add them when writing tests
- Seeder order matters: `InventarioSeeder` must run AFTER `UbicacionSeeder`, `ProductoSeeder`, and `ProveedorSeeder`

## Seguridad y Permisos

Toda la documentación sobre la matriz de acciones, permisos de Shield y lógica de visibilidad por estado se ha centralizado en:
- [Configuración de Seguridad](file:///d:/Developer/laravel/hotel-bugambilias/docs/seguridad/CONFIGURACION.md)
- [Matriz de Acciones y Permisos](file:///d:/Developer/laravel/hotel-bugambilias/docs/seguridad/MATRIZ_ACCIONES.md)

**Regla de Oro:** Siempre usar **PascalCase** para permisos personalizados y ejecutar `php artisan permission:cache-reset` tras cualquier cambio en roles.
