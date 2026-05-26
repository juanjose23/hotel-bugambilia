# Agent: Dev Hotel Bugambilias

## Role
Senior developer especializado en el stack exacto de este proyecto.
Genera código listo para producción siguiendo las convenciones del repo.

## Stack
- Laravel 13 + Filament 5 (`/admin`)
- Pest PHP 4.x para testing
- PostgreSQL (triggers, CHECK constraints, índices parciales)
- `maatwebsite/excel` para exports .xlsx
- PHPStan level 6 con Larastan

## Commands rápidos
```bash
composer test                                          # Pest (limpia config primero)
composer pint                                          # Linting
composer phpstan                                       # Análisis estático
composer dev                                           # PHP server + queue + Vite
php artisan db:seed --class=Database\\Seeders\\{Name} # Seeder individual
php artisan migrate:fresh --seed                       # Reset completo
php artisan permission:cache-reset                     # Tras cambios de roles
php -l app/path/to/File.php                           # Validar sintaxis PHP
```

## Estructura de archivos

### Filament Resource (SIEMPRE esta estructura)
```
app/Filament/Resources/{Group}/{ResourceName}/
├── {ResourceName}Resource.php
├── Schemas/
│   └── {ResourceName}Form.php
├── Tables/
│   └── {ResourceName}Table.php
└── Pages/
    ├── List{Name}.php
    ├── Create{Name}.php
    ├── Edit{Name}.php
    └── View{Name}.php
```

### Use Cases
```
app/UseCases/{Module}/{Queries|Mutations}/UseCase.php
app/UseCases/{Module}/{SubModule}/{Queries|Mutations}/UseCase.php
```
- Método principal: `execute()` o `ejecutar()`
- Eloquent directo — sin repositorios, sin DDD
- Helpers de dominio en `Services/` dentro del mismo módulo

### Modelos
```
App\Models\Colaboradores\
App\Models\Compras\
App\Models\Inventario\
App\Models\Catalogos\
App\Models\Habitaciones\
App\Models\Personas\
App\Models\Monedas\
App\Models\Servicios\
App\Models\Politicas\
```

## Convenciones de código

### Modelos Eloquent
```php
protected $guarded = ['id'];   // SIEMPRE, nunca $fillable masivo
use SoftDeletes;
use HasFactory;
// Si necesita auditoría:
use \OwenIt\Auditing\Contracts\Auditable;
use \OwenIt\Auditing\Auditable as AuditableTrait;
```

### Migraciones
```php
// Cada columna DEBE tener ->comment()
$table->id()->comment('Identificador único autoincremental');
$table->string('codigo', 30)->unique()->comment('Código de documento (XX-YYYY-NNN)');
// CHECK constraints para PostgreSQL:
if (DB::connection()->getDriverName() !== 'sqlite') {
    DB::statement('ALTER TABLE tabla ADD CONSTRAINT chk_estado CHECK (estado IN (1,2,3))');
}
```

### Enums PHP nativos
```php
enum EstadoActivo: int
{
    case Activo = 1;
    case EnMantenimiento = 2;
    case DadoDeBaja = 3;
    case Extraviado = 4;
    case EnTransito = 5;

    public function label(): string
    {
        return match($this) {
            self::Activo => 'Activo',
            self::EnMantenimiento => 'En mantenimiento',
            self::DadoDeBaja => 'Dado de baja',
            self::Extraviado => 'Extraviado',
            self::EnTransito => 'En tránsito',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Activo => 'success',
            self::EnMantenimiento => 'warning',
            self::DadoDeBaja => 'danger',
            self::Extraviado => 'danger',
            self::EnTransito => 'info',
        };
    }
}
```

### Filament Forms
```php
// Schema method signature CORRECTA:
public static function form(Schema $schema): Schema
{
    return $schema->components([
        // ...
    ]);
}
// NO usar Form $form — es Schema $schema en Filament v5
```

### Filament Tables
```php
// Usar ->configure($table) NO ::configure($table)
public static function table(Table $table): Table
{
    return ActActivoTable::configure($table);  // instancia, no estático
}
```

### Permisos (Spatie Shield)
- Siempre **PascalCase** para permisos personalizados
- Ejecutar `php artisan permission:cache-reset` tras cualquier cambio
- Docs en `docs/seguridad/CONFIGURACION.md` y `MATRIZ_ACCIONES.md`

## Módulos existentes

### Inventario (UseCases activos)
| UseCase | Trigger |
|---|---|
| `RegistrarEntradaRecepcion` (UC-01) | `RecepcionInventoryObserver` al cambiar estado recepción |
| `LiberarLotesCuarentena` (UC-02) | BulkAction en LoteResource |
| `ConsumirStock` (UC-03) | FEFO via `FEFOStrategy` |
| `VerificarCaducidades` (UC-04) | Scheduled diario 06:00 en `routes/console.php` |

Modelos:
- `App\Models\Inventario\Lote` → tabla `inv_lotes`
- `App\Models\Inventario\MovimientoStock` → tabla `inv_movimientos`
- Ubicaciones: reutiliza tabla `ubicaciones` con `tipo='almacen'`

Observer registrado en: `AppServiceProvider::boot()`

### Compras (flujo P2P completo)
`Solicitudes → SolicitudItems → Cotizaciones → CotizacionItems → OrdenCompra → OrdenCompraItems → RecepcionCompra → RecepcionItems → DevolucionCompra → DevolucionItems`

### Módulos en desarrollo
- `act_activos` — activos fijos individualizables (tipo=3 en productos)
- `act_activo_asignaciones` — polimórfico: Habitacion | Espacio | Ubicacion
- `act_registro_individualizacion` — puente recepción → individuos

## Gotchas conocidos

| Problema | Solución |
|---|---|
| `ColaboradorForm` tiene dos schemas | `getRegistroInicialSchema()` y `getEdicionCompletaSchema()` |
| `$` suelto al final de clases | Siempre `php -l archivo.php` después de editar |
| Table methods | `->configure($table)` nunca `::configure($table)` |
| Filament form param | `Schema $schema` no `Form $form` |
| `RecepcionItem $fillable` | Incluir `lote_proveedor` y `fecha_vencimiento` en tests |
| Orden seeders | InventarioSeeder DESPUÉS de UbicacionSeeder, ProductoSeeder, ProveedorSeeder |
| PutawayPolicy | Toma primera `ubicacion` activa con `tipo='almacen'` |
| productos.tipo | `1=Perecedero`, `2=No perecedero`, `3=Activo fijo (individualizable)` |

## Cuando generes código, siempre incluye:
1. Ruta exacta del archivo: `// app/UseCases/Activos/Mutations/IndividualizarActivos.php`
2. Imports completos al tope
3. Comments en cada columna de migraciones
4. `php -l` reminder si es clase PHP
5. Comando para ejecutar si es migración o seeder