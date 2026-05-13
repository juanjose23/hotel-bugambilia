# Sistema de Seguridad y Permisos (Filament Shield)

Este documento detalla la implementación del Control de Acceso Basado en Roles (RBAC) utilizando **Filament Shield** (basado en Spatie Permission) para el ecosistema Hotel Bugambilias.

## Arquitectura de Seguridad

El sistema utiliza una matriz de permisos granular dividida en cuatro categorías principales:

### 1. Recursos (Resources)
Cada módulo del sistema (Colaboradores, Compras, Catálogos, etc.) genera automáticamente permisos estándar:
- `view_any`, `view`, `create`, `update`, `delete`, `delete_any`, `restore`, `restore_any`, `force_delete`, `force_delete_any`, `replicate`, `reorder`.

### 2. Páginas Personalizadas (Pages)
Las páginas que no son CRUD estándar requieren permisos de visualización específicos registrados en el trait `HasShieldPagePermissions`. 
Ejemplos incluidos:
- `ComparativaSolicitud`
- `ComparativaCotizaciones`

### 3. Permisos Personalizados (Custom Permissions)
Ubicados en la pestaña dedicada dentro de la edición de Roles. Utilizados para acciones transversales:
- **Reportes PDF**: `ImprimirSolicitud`, `ImprimirOrdenCompra`, `ImprimirRecepcion`, `ImprimirCotizacion`, `ImprimirReportesCompras`.
- **Exportaciones**: `ExportarComprasExcel`.
- **Dashboards Especiales**: `ViewComparativaSolicitud`, `ViewComparativaCotizaciones`.

---

## Configuración Técnica (config/filament-shield.php)

- **Separador**: `:` (Ejemplo de permiso: `view:Solicitud`)
- **Case**: `Pascal` (Convención obligatoria para todos los permisos, incluidos los personalizados).
- **Super Admin**: El rol `super_admin` tiene acceso total. Está configurado para interceptar mediante el gate `before`, permitiendo acceso incluso si no tiene el permiso explícito. Sin embargo, se recomienda asignar los permisos explícitamente para evitar fallos en la validación de visibilidad de la UI.

---

## Implementación de Autorización

Para asegurar que los permisos definidos se apliquen correctamente en el código, se deben seguir estos patrones:

### 1. En Rutas (web.php / api.php)
Utiliza el middleware `can:` seguido del nombre del permiso. **Siempre en PascalCase**.

```php
Route::get('/reporte/{id}', [Controller::class, 'metodo'])
    ->middleware('can:ImprimirSolicitud');
```

### 2. En Controladores
Puedes proteger métodos específicos o todo el controlador.

- **En el constructor**:
```php
public function __construct()
{
    $this->middleware('can:ImprimirSolicitud')->only(['imprimir']);
}
```

- **Dentro de un método**:
```php
public function imprimir(Solicitud $solicitud)
{
    $this->authorize('ImprimirSolicitud'); // Lanza 403 si falla
    // ...
}
```

### 3. En Vistas (Blade)
```blade
@can('ImprimirSolicitud')
    <button>Imprimir</button>
@endcan
```

---

## Comandos Operativos y Resolución de Problemas

Si agregas un nuevo Recurso, Modelo o Página al sistema:
1.  Ejecuta: `php artisan shield:generate --all` para sincronizar la matriz de permisos.
2.  Limpia caché obligatoriamente: 
    ```bash
    php artisan config:clear
    php artisan permission:cache-reset
    ```

### Problemas comunes en PostgreSQL (Case Sensitivity)
Dado que el motor de base de datos es PostgreSQL, los nombres de los permisos son sensibles a mayúsculas/minúsculas. 
- **ERROR**: `can:imprimir_solicitud` fallará si el permiso en BD es `ImprimirSolicitud`.
- **SOLUCIÓN**: Asegurar que tanto el Seeder, como las Rutas y el archivo de Configuración usen exactamente el mismo caso (**PascalCase**).

### Sincronización del Rol de Compras
Si tras correr el seeder los permisos no aparecen marcados en la interfaz de Shield:
1.  Verifica que los nombres en `config/filament-shield.php` coincidan con el seeder.
2.  Limpia la caché de permisos (`php artisan permission:cache-reset`).
3.  Si persiste, marca los permisos manualmente en la UI de Roles y guarda cambios; esto forzará la relación en la tabla `role_has_permissions`.

Para crear un Super Usuario de emergencia:
```bash
php artisan shield:super-admin
```

---

## Integración con Auditoría
La seguridad está vinculada al módulo de **Auditoría de Reportes**. Cada vez que un usuario utiliza un permiso de tipo `imprimir_*`, el sistema registra:
- Usuario que ejecutó la acción.
- Fecha y hora exacta.
- Parámetros del documento generado.
