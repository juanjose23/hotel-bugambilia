# Configuración y Patrones de Seguridad

Este documento detalla la implementación técnica de **Filament Shield** y los patrones de autorización en el código.

## 1. Configuración de Shield (`config/filament-shield.php`)

- **Separador**: `:` (Ejemplo: `view:Solicitud`).
- **Case**: `Pascal` (Convención obligatoria).
- **Custom Permissions**: Los permisos personalizados se declaran en este archivo para que aparezcan en la UI de Shield.

## 2. Patrones de Implementación en Código

Para asegurar que los permisos se apliquen correctamente, utiliza los siguientes patrones:

### En Rutas
```php
Route::get('/reporte/{id}', [Controller::class, 'metodo'])
    ->middleware('can:Compras:ImprimirSolicitud');
```

### En Controladores
```php
public function imprimir(Solicitud $solicitud)
{
    $this->authorize('Compras:ImprimirSolicitud'); 
    // ...
}
```

### En Filament (Actions)
```php
Action::make('imprimir')
    ->visible(fn () => auth()->user()->can('Compras:ImprimirSolicitud'))
```

## 3. Mantenimiento y Sincronización

Cada vez que se agreguen nuevos recursos o permisos personalizados:

1.  **Generar**: `php artisan shield:generate --all`
2.  **Limpiar Caché**: `php artisan permission:cache-reset`
3.  **Asignar**: Ir a Shield UI -> Roles -> Editar y marcar los nuevos permisos.

---

## 4. Resolución de Problemas

### El botón no aparece
1.  **Caché**: Ejecuta `php artisan permission:cache-reset`.
2.  **Case Sensitivity**: Verifica que el permiso en el código (`Compras:ImprimirSolicitud`) sea idéntico al de la base de datos.
3.  **Guard**: Asegúrate de que tanto el rol como el usuario pertenezcan al guard `web`.
