# Requerimientos del Sistema: Clientes, Usuarios y Seguridad de Acceso

### 3. Requerimientos del Sistema

#### 3.1. Requerimientos Funcionales (RF)

##### Requerimientos del Registro Público de Clientes (Web)
* **RF-129:** El software ofrecerá un formulario de registro público (`/registro`), mostrando en pantalla un selector dinámico de tipo de persona: "Persona Natural" o "Empresa / Persona Jurídica".
* **RF-130:** El S.I. permitirá registrar nuevos clientes, solicitando condicionalmente según el tipo de persona:
  * Para Persona Natural: Nombres, Apellidos, Tipo de Documento (Cédula, Pasaporte, DNI), Número de Identificación, Correo Electrónico, Teléfono y Contraseña.
  * Para Persona Jurídica: Razón Social, Identificación Fiscal (RUC/NIT), Correo Electrónico Corporativo, Teléfono y Contraseña.
  * Verificando la validez del formato de correo electrónico y la fortaleza mínima de la contraseña.
* **RF-131:** Se asignará un registro unificado en la tabla base `personas` vinculado a la especialización (`persona_natural` o `persona_juridica`) y a la entidad `clientes` y `users`.
* *Excepción/Regla:* Los datos de identificación fiscal y nombres legales validados no podrán ser alterados por el propio usuario sin previa validación administrativa.

##### Requerimientos del Algoritmo de Resolución de Conflictos de Identidad
* **RF-132:** El software validará la identidad del cliente mediante `ResolverIdentidadPersona`, normalizando el número de documento ingresado (eliminación de guiones, puntos y espacios) y comparando contra el padrón histórico.
* **RF-133:** Proceso de Validación y Detección de Conflictos de Identidad:
  1) Verificación de la coincidencia del número de identificación contra registros existentes (`CompararDatosPersona`):
     * i) Condición A (Identificación no registrada previamente): El sistema ejecutará `RegistrarClienteNuevo`, creará la entidad Persona, Cliente y Usuario, e iniciará sesión automáticamente.
     * ii) Condición B (Identificación ya existente pero con nombre divergente con distancia de Levenshtein > 2): El sistema detendrá el registro automático, clasificará el evento como `conflicto_identidad` (Homonimia o Datos Divergentes), registrará la solicitud en estado `Pendiente` en el panel de auditoría y disparará el evento `PersonaConflictoIdentidad` para resolución manual por parte del administrador.

##### Requerimientos de la Gestión de Clientes y Creación de Cuentas (Admin)
* **RF-134:** El software listará todos los clientes registrados en el panel de administración, mostrando en pantalla nombre completo o razón social, número de identificación fiscal, teléfono, correo electrónico, tipo de cliente y fecha de alta.
* **RF-135:** El S.I. permitirá al administrador habilitar acceso al portal a clientes que aún no tengan usuario web mediante la acción "Crear cuenta de acceso", solicitando el correo electrónico, generando una contraseña temporal y marcando la cuenta con la bandera `password_change_required = true`.

##### Requerimientos del Control de Acceso, Roles y Forzado de Contraseña
* **RF-136:** El software restringirá el acceso al panel administrativo (`/admin/*`) mediante el middleware `RequerirAdmin`, verificando que el usuario autenticado posea la bandera `is_admin = true` y los roles requeridos mediante Filament Shield y Spatie Permission; de no cumplirlo, cerrará la sesión y redirigirá al inicio público.
* **RF-137:** Proceso de Forzado de Cambio de Contraseña Temporal:
  1) Verificación del estado de seguridad de la cuenta al iniciar sesión (`RequerirCambioContrasena`):
     * i) Condición A (Usuario con `password_change_required = false`): El sistema concederá acceso inmediato al portal o panel de control según sus permisos.
     * ii) Condición B (Usuario con `password_change_required = true`): El sistema interceptará cualquier petición y redirigirá obligatoriamente a la pantalla `/cambiar-contrasena`, solicitando la contraseña temporal recibida, la nueva contraseña robusta y su confirmación antes de permitir cualquier navegación en la plataforma.

##### Requerimientos del Módulo de Colaboradores
* **RF-138:** El software gestionará los colaboradores del hotel (recepcionistas, camaristas, cocineros, meseros, cajeros, administradores), mostrando en pantalla código de empleado, nombre, cargo, departamento, fecha de contratación y estado laboral.
* **RF-139:** El S.I. permitirá registrar colaboradores vinculándolos a su registro de persona natural y usuario del sistema, verificando que el código de colaborador sea único en la nómina.
