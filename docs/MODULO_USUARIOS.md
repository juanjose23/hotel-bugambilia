# Módulo de Usuarios — Hotel Bugambilias

Este documento detalla la arquitectura, el flujo operativo y los estándares de implementación del módulo de Usuarios del sistema.

---

## 1. Recurso Filament (`UserResource`)

- **Ruta:** `/admin/usuarios`
- **Grupo de navegación:** `Seguridad`
- **Modelo:** `App\Models\User`
- **Estilo:** CRUD modal (página única `ManageUsers` que extiende `ManageRecords`)
- **Archivos:**
  - `app/Filament/Resources/Usuarios/Users/UserResource.php`
  - `app/Filament/Resources/Usuarios/Users/Schemas/UserForm.php`
  - `app/Filament/Resources/Usuarios/Users/Tables/UsersTable.php`
  - `app/Filament/Resources/Usuarios/Users/Pages/ManageUsers.php`

### Columnas de la tabla

| Columna | Descripción |
|---------|-------------|
| **Código** | Código del colaborador asociado (`persona.colaborador.codigo`) |
| **Nombre de usuario** | Nombre de usuario para inicio de sesión (`name`) |
| **Trabajador** | Nombre completo de la persona asociada |
| **Correo electrónico** | Correo electrónico del usuario |

---

## 2. Formulario de Usuario (`UserForm`)

El formulario está organizado en dos secciones:

### 2.1 Datos de la cuenta

| Campo | Tipo | Descripción |
|-------|------|-------------|
| **Trabajador** | `Select` | Lista de colaboradores **sin cuenta de usuario**. Busca por código o nombre. Al seleccionar, autogenera nombre de usuario y correo. |
| **Nombre de usuario** | `TextInput` | Nombre de usuario para inicio de sesión. Se autogenera. |
| **Correo electrónico** | `TextInput` | Correo electrónico. Se autogenera. Validación única. |
| **Contraseña** | `PasswordInput` | Requerida solo en creación. Opcional en edición (si se deja vacía, se conserva la actual). |

### 2.2 Roles y Permisos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| **Roles** | `Select` múltiple | Asignación de roles Spatie. Usa la relación `roles()` del modelo User. |

---

## 3. Generación Automática de Credenciales

### 3.1 UseCase: `GenerarCredencialesUsuario`

- **Archivo:** `app/UseCases/Usuarios/GenerarCredencialesUsuario.php`
- **Método:** `execute(Persona $persona): array{name: string, email: string}`

#### Lógica de generación:

**Nombre de usuario (`name`):**
1. Toma el `primer_nombre` y `primer_apellido` de la persona.
2. Convierte a minúsculas y reemplaza caracteres acentuados/ñ (ej. `José` → `jose`, `Muñoz` → `munoz`).
3. Concatena con un punto: `jose.munoz`.
4. Si ya existe un usuario con ese nombre, agrega un sufijo numérico incremental (`jose.munoz1`, `jose.munoz2`, etc.).

**Correo electrónico:**
1. Usa el nombre de usuario generado como prefijo.
2. El dominio se obtiene de `config('app.email_domain')` (valor por defecto: `hotel.com`).
3. Resultado: `jose.munoz@hotel.com`.

### 3.2 Configuración del dominio

| Variable de entorno | Config | Valor por defecto |
|--------------------|--------|-------------------|
| `APP_EMAIL_DOMAIN` | `config('app.email_domain')` | `hotel.com` |

Agregar en `.env`:
```env
APP_EMAIL_DOMAIN=hotel.com
```

---

## 4. Filtro de Personas Disponibles

El selector de trabajadores (`persona_id`) solo muestra colaboradores **que aún no tienen una cuenta de usuario**:

- Usa `User::pluck('persona_id')` para obtener los IDs de personas con cuenta.
- Excluye esas personas del listado (`whereNotIn('id', $excludedIds)`).
- **En edición:** incluye automáticamente la persona del usuario actual para permitir conservar la selección.

---

## 5. Permisos y Autorización

### 5.1 Rutas de reportes

Las rutas de descarga de PDF están protegidas con middleware de permisos:

| Ruta | Permiso requerido |
|------|-------------------|
| `admin/compras/reportes/solicitud/{solicitud}` | `can:ImprimirSolicitud` |
| `admin/compras/reportes/orden-compra/{orden}` | `can:ImprimirOrdenCompra` |
| `admin/compras/reportes/recepcion/{recepcion}` | `can:ImprimirRecepcion` |
| `admin/compras/reportes/cotizacion/{cotizacion}` | `can:ImprimirCotizacion` |
| `admin/compras/reportes/resumen-departamentos` | `can:ImprimirReportesCompras` |

### 5.2 Policies

| Modelo | Policy | Mecanismo |
|--------|--------|-----------|
| `RecepcionCompra` | `RecepcionCompraPolicy` | Permisos Shield (`ViewAny:RecepcionCompra`, `Create:RecepcionCompra`, etc.) |
| `Solicitud` | `SolicitudPolicy` | Permisos Shield |
| `OrdenCompra` | `OrdenCompraPolicy` | Permisos Shield |

---

## 6. Arquitectura y Flujo

```
UserResource (ManageRecords - Modal CRUD)
│
├── UserForm
│   ├── Select persona_id → options() → Personas sin usuario
│   ├── afterStateUpdated → GenerarCredencialesUsuario::execute()
│   ├── TextInput name (autogenerado)
│   ├── TextInput email (autogenerado)
│   └── Select roles (múltiple)
│
└── UsersTable
    ├── Código (colaborador)
    ├── Nombre de usuario
    ├── Trabajador (nombre completo)
    └── Correo electrónico
```

### Flujo de creación de usuario:

1. El usuario administrador abre el modal de creación.
2. Selecciona un trabajador del listado (solo colaboradores sin cuenta).
3. El sistema genera automáticamente:
   - Nombre de usuario: `jose.munoz`
   - Correo: `jose.munoz@hotel.com`
4. El administrador asigna contraseña y roles.
5. Guarda el registro.

---

## 7. Archivos del Módulo

| Archivo | Propósito |
|---------|-----------|
| `app/Models/User.php` | Modelo con relaciones `persona()` y `roles()` |
| `app/UseCases/Usuarios/GenerarCredencialesUsuario.php` | Generación de nombre de usuario y correo |
| `app/Filament/Resources/Usuarios/Users/UserResource.php` | Recurso Filament |
| `app/Filament/Resources/Usuarios/Users/Pages/ManageUsers.php` | Página CRUD modal |
| `app/Filament/Resources/Usuarios/Users/Schemas/UserForm.php` | Esquema del formulario |
| `app/Filament/Resources/Usuarios/Users/Tables/UsersTable.php` | Configuración de la tabla |
| `config/app.php` | Configuración `email_domain` |
