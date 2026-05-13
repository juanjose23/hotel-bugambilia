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
- **Reportes PDF**: `imprimir_solicitud`, `imprimir_orden_compra`, `imprimir_recepcion`, `imprimir_cotizacion`.
- **Exportaciones**: `exportar_compras_excel`.
- **Dashboards Especiales**: `view_comparativa_solicitud`.

### 4. Widgets
Control de visibilidad de los widgets estadísticos (KPIs) en el Dashboard principal.

---

## Configuración Técnica (config/filament-shield.php)

- **Separador**: `:` (Ejemplo de permiso: `view:Solicitud`)
- **Case**: `Pascal` (Convención para nombres de modelos)
- **Super Admin**: El rol `super_admin` tiene acceso total. Está configurado para interceptar mediante el gate `before`, permitiendo acceso incluso si no tiene el permiso explícito.
- **Tabs en UI**: Se han habilitado todas las pestañas de gestión para una administración granular desde el módulo de Roles.

---

## Comandos Operativos

Si agregas un nuevo Recurso, Modelo o Página al sistema:
1.  Ejecuta: `php artisan shield:generate --all` para sincronizar la matriz de permisos.
2.  Limpia caché si es necesario: `php artisan cache:forget spatie.permission.cache`.

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
