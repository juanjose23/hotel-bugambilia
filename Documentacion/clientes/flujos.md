# Documentación de Flujos de Procesos: Módulo Clientes / Usuarios

## 1. Submódulo / Funcionalidad: Registro Público de Cliente

- **Descripción de la Pantalla / Vista:** Página `/registro` con formulario público. Toggle para seleccionar tipo: Persona Natural o Empresa (Persona Jurídica). Campos dinámicos según el tipo. Identificación opcional (cédula, pasaporte, NIT, RUC).
- **Disparador (Trigger):** Click en "Crear Cuenta" desde el header o link en login.
- **Flujo Paso a Paso:**
    1. El usuario accede a `/registro` y selecciona el tipo de persona (Natural o Jurídica).
    2. El sistema muestra campos específicos:
        - **Natural**: Nombre, Apellido, Tipo ID (cédula/DNI/pasaporte), Número ID, Email, Teléfono, Contraseña.
        - **Jurídica**: Razón Social, Tipo ID (NIT/RUC), Número ID, Email, Teléfono, Contraseña.
    3. El usuario completa los campos y acepta los términos.
    4. El sistema envía el formulario a `POST /registro`.
    5. El `AutenticacionController::registrar()` valida condicionalmente según `tipo_persona`.
    6. El `RegistrarCliente` interactor orquesta:
        - `ResolverIdentidadPersona`: busca coincidencias por identificación (normalizada: quita guiones, puntos).
        - Si encuentra → `CompararDatosPersona` (levenshtein ≤ 2) clasifica como vincular, actualizar o conflicto.
        - Si no encuentra → `RegistrarClienteNuevo`: crea Persona + PersonaNatural/PersonaJuridica + Cliente + User.
    7. El sistema inicia sesión automáticamente y redirige al Home con mensaje de éxito.

---

## 2. Submódulo / Funcionalidad: Resolución de Conflictos de Identidad

- **Descripción de la Pantalla / Vista:** Página admin `Conflictos de Identidad` con tabla de conflictos pendientes. Vista detalle con KeyValueEntry mostrando datos_providos vs datos_existentes.
- **Disparador (Trigger):** Cuando un registro intenta usar una identificación ya existente con nombre diferente.
- **Flujo Paso a Paso:**
    1. Un huésped intenta registrarse con una cédula ya existente pero nombre diferente.
    2. El `ResolverIdentidadPersona` encuentra la PersonaNatural por ID.
    3. El `CompararDatosPersona` detecta que los nombres no coinciden (levenshtein > 2).
    4. El sistema clasifica como `conflicto_identidad` (Homonimia o DatosDivergentes).
    5. Se crea un registro en `conflictos_identidad` con estado Pendiente.
    6. Se dispara el evento `PersonaConflictoIdentidad`.
    7. El administrador revisa el conflicto en `admin/usuarios/conflictos-identidad`.
    8. Puede resolverlo manualmente (vincular, rechazar, o crear nueva persona).

---

## 3. Submódulo / Funcionalidad: Gestión de Clientes (Admin)

- **Descripción de la Pantalla / Vista:** Tabla de clientes con columnas: nombre completo, identificación, teléfono, email, tipo, fecha de registro. Permite crear clientes con o sin usuario.
- **Disparador (Trigger):** Acceso desde `Usuarios > Clientes` o `Ventas > Clientes`.
- **Flujo Paso a Paso:**
    1. El administrador accede a la lista de clientes.
    2. Puede crear un nuevo cliente con `tipo_persona` (Natural/Jurídica) y todos los campos de identificación.
    3. **Crear cuenta de acceso**: Si el cliente no tiene usuario, aparece un botón "Crear cuenta de acceso".
    4. El administrador ingresa el email y el sistema crea un User vinculado con contraseña temporal (`password_change_required = true`).
    5. Al iniciar sesión, el middleware `RequerirCambioContrasena` fuerza al cliente a cambiar su contraseña.

---

## 4. Submódulo / Funcionalidad: Usuarios (Admin)

- **Descripción de la Pantalla / Vista:** Tabla de usuarios con filtros toggle arriba: Administradores, Clientes, Sin cliente. Columnas: Código Colaborador, Nombre, Email, Cliente (badge Sí/No).
- **Disparador (Trigger):** Acceso desde `Seguridad > Usuarios`.
- **Flujo Paso a Paso:**
    1. El administrador visualiza todos los usuarios del sistema.
    2. Usa los botones de filtro (Administradores / Clientes / Sin cliente) para segmentar.
    3. **Administradores**: `is_admin = true`. Pueden acceder al panel `/admin`.
    4. **Clientes**: `is_admin = false` + tienen `persona.cliente`. No acceden al panel admin (middleware `RequerirAdmin`).
    5. **Sin cliente**: `is_admin = false` + no tienen cliente asociado (posiblemente colaboradores sin cuenta de cliente).

---

## 5. Submódulo / Funcionalidad: Control de Acceso

- **Descripción de la Pantalla / Vista:** Middleware que protege rutas administrativas y fuerza cambio de contraseña temporal.
- **Disparador (Trigger):** Cada petición HTTP (web middleware).
- **Flujo Paso a Paso:**
    1. **RequerirAdmin**: Si el usuario no es `is_admin` y accede a `/admin/*`, cierra sesión y redirige a `/`.
    2. **RequerirCambioContrasena**: Si `password_change_required = true`, redirige a `/cambiar-contrasena`.
    3. La página de cambio de contraseña pide: contraseña actual (temporal), nueva contraseña, confirmación.
    4. Al cambiar exitosamente, `password_change_required = false` y redirige al Home.

---

## Arquitectura del Módulo

```
app/
├── BusinessLogic/Usuarios/
│   ├── ResolverIdentidadPersona.php         ← Búsqueda normalizada por ID
│   └── CompararDatosPersona.php             ← Comparación con Levenshtein
├── Interactors/Usuarios/
│   ├── RegistrarCliente.php                 ← Orquestador principal
│   ├── RegistrarClienteNuevo.php            ← Crea Persona + Cliente + User
│   └── VincularPersonaExistenteAUser.php    ← Vincula User a Persona existente
├── Http/Controllers/
│   └── AutenticacionController.php          ← Login, Registro, Cambio contraseña
├── Http/Middleware/
│   ├── RequerirAdmin.php                    ← Bloquea no-admin de /admin
│   └── RequerirCambioContrasena.php         ← Fuerza cambio de contraseña temporal
├── Repository/Models/
│   ├── User.php                             ← is_admin, password_change_required
│   ├── Personas/Persona.php
│   ├── Personas/PersonaNatural.php
│   ├── Personas/PersonaJuridica.php
│   ├── Clientes/Cliente.php
│   └── Usuarios/ConflictoIdentidad.php
└── Filament/Resources/Usuarios/
    ├── Users/                               ← Tabla con filtros toggle
    ├── Clientes/                            ← CRUD + botón crear cuenta
    └── ConflictosIdentidad/                 ← Vista de conflictos
```
