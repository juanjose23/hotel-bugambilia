# Documentación de Flujos de Procesos: Módulo Limpieza

## 1. Submódulo / Funcionalidad: Turnos de Limpieza

- **Descripción de la Pantalla / Vista:** Tabla con turnos registrados. Formulario con: nombre, líder, apoyo (colaboradores con permiso `Update:LimpiezaEjecucion`), carritos/bodegas (relación BelongsToMany), hora inicio/fin, estado.
- **Disparador (Trigger):** Acceso desde `Limpieza > Turnos` en el panel admin.
- **Flujo Paso a Paso:**
    1. El administrador crea un turno (ej: "Turno Matutino A", 07:00-15:00).
    2. El sistema muestra solo colaboradores con permisos en el módulo de limpieza (filtrados por `ObtenerColaboradoresLimpieza`).
    3. El administrador selecciona líder, apoyo, y carritos (múltiples).
    4. El sistema guarda el turno y los carritos se sincronizan vía la tabla pivote `limp_turno_carritos`.

---

## 2. Submódulo / Funcionalidad: Planificación de Horarios

- **Descripción de la Pantalla / Vista:** Formulario de horario (cabecera) con turno, hora estimada, frecuencia (diaria/semanal), día de semana, checklist (plantilla JSON). Detalles polimórficos: tipo (Habitación/Espacio/Ubicación) + ítem específico.
- **Disparador (Trigger):** Creación de horario desde `Limpieza > Horarios`.
- **Flujo Paso a Paso:**
    1. El administrador crea un horario vinculado a un turno.
    2. Define frecuencia: diaria o semanal (con día específico).
    3. Agrega detalles polimórficos: selecciona `limpiable_type` (Habitacion, Espacio) y `limpiable_id`.
    4. Define checklist de tareas (KeyValue JSON: "Tender camas" → false).
    5. El sistema guarda el horario con sus detalles.

---

## 3. Submódulo / Funcionalidad: Materialización Diaria de Ejecuciones

- **Descripción de la Pantalla / Vista:** Comando Artisan programado que convierte horarios activos en ejecuciones del día.
- **Disparador (Trigger):** `php artisan limpieza:materializar-ejecuciones` (programado cada hora vía scheduler).
- **Flujo Paso a Paso:**
    1. El scheduler ejecuta el comando cada hora (`jobs.limpieza_materializar = hourly`).
    2. El comando consulta `LimpiezaHorario` activos con `turno_id` no nulo.
    3. Filtra por frecuencia: diarias siempre, semanales solo si `dia_semana` coincide con el día actual.
    4. Para cada detalle, verifica si ya existe una ejecución para esa fecha + turno + limpiable.
    5. Si no existe, crea `LimpiezaEjecucion` con estado `Pendiente`, checklist copiado de la plantilla.
    6. Notifica a líder y apoyo del turno vía `NotificadorLimpieza` (nuevas asignaciones disponibles).
    7. La notificación usa `Notification::sendToDatabase()` (Filament) para aparecer en la campana.

---

## 4. Submódulo / Funcionalidad: Ejecución de Limpieza

- **Descripción de la Pantalla / Vista:** Formulario con tabs: Información General (tipo ubicación, ubicación, turno, camarista, carrito, fecha, estado, hora inicio/fin), Abastecimiento Recomendado (insumos faltantes), Checklist de Tareas (KeyValue), Consumos y Cambios (resumen de insumos consumidos).
- **Disparador (Trigger):** Edición de una ejecución desde `Limpieza > Ejecuciones`.
- **Flujo Paso a Paso:**
    1. El camarista accede a la ejecución asignada.
    2. Visualiza el Abastecimiento Recomendado: compara `cantidad_actual` vs `cantidad_ideal` del stock de la habitación.
    3. Marca tareas del checklist como completadas.
    4. Registra consumos de insumos del carrito.
    5. Cambia el estado: Pendiente → En Progreso → Completada.
    6. El sistema actualiza el estado del espacio limpiado (`ActualizadorEstadoEspacioLimpieza`).

---

## 5. Submódulo / Funcionalidad: Recordatorios de Limpieza Pendiente

- **Descripción de la Pantalla / Vista:** Comando Artisan que envía notificaciones para ejecuciones pendientes cuya hora estimada ya pasó.
- **Disparador (Trigger):** `php artisan limpieza:enviar-recordatorios` (programado a las 12:00 diario).
- **Flujo Paso a Paso:**
    1. El comando busca ejecuciones del día pendientes, sin recordatorio enviado, con hora estimada ≤ hora actual.
    2. Resuelve destinatarios: colaborador asignado + líder del turno + apoyo del turno.
    3. Para cada uno, busca su `User` vinculado vía `persona_id`.
    4. Envía notificación `RecordatorioLimpiezaPendiente` vía `NotificadorLimpieza::recordatorioPendiente()`.
    5. Marca `recordatorio_enviado_at` en la ejecución.
    6. Las notificaciones aparecen en la campana Filament del panel admin.

---

## 6. Submódulo / Funcionalidad: Notificaciones de Limpieza

- **Descripción de la Pantalla / Vista:** Sistema unificado de notificaciones usando Filament Database Notifications (campana). Tipos: Nueva solicitud, Personal asignado, Faltante de reposición, Recordatorio, Nuevas asignaciones.
- **Disparador (Trigger):** Eventos del sistema (Observer, Comandos programados).
- **Flujo Paso a Paso:**
    1. **Eventos en tiempo real** (via Observer → Event → Listener):
        - `SolicitudLimpiezaCreada` → notifica a roles con permiso (super_admin, limpieza_encargado, limpieza_supervisor).
        - `PersonalLimpiezaAsignado` → notifica asignación de personal.
        - `FaltanteReposicionDetectado` → notifica faltante al usuario específico.
    2. **Notificaciones programadas** (via Comandos Artisan):
        - `limpieza:materializar-ejecuciones` → `NuevasAsignacionesLimpiezaDisponibles`.
        - `limpieza:enviar-recordatorios` → `RecordatorioLimpiezaPendiente`.
    3. Todas las notificaciones usan `NotificadorLimpieza` → `NotificadorBase::sendToDatabase()`.
    4. El panel Filament tiene `->databaseNotifications()` con polling de 15s.

---

## Arquitectura del Módulo

```
app/
├── Repository/Models/Limpieza/
│   ├── Turno.php                            ← Turnos (limp_horario_turnos)
│   ├── LimpiezaHorario.php                  ← Planificación (limp_horarios)
│   ├── LimpiezaEjecucion.php                ← Ejecuciones del día
│   └── SolicitudLimpieza.php                ← Solicitudes
├── Console/Commands/Limpieza/
│   ├── MaterializarEjecucionesLimpieza.php  ← Comando programado (hourly)
│   └── EnviarRecordatoriosLimpieza.php      ← Comando programado (12:00)
├── Events/Limpieza/                         ← Eventos del dominio
├── Listeners/Limpieza/                      ← Listeners → NotificadorLimpieza
├── Notifications/Limpieza/
│   ├── NotificadorLimpieza.php              ← Orquestador de notificaciones
│   ├── MensajesLimpieza.php                 ← Construye DatosNotificacion
│   └── DestinatariosLimpieza.php            ← Resuelve destinatarios por rol
└── Filament/Resources/Limpieza/
    ├── TurnoResource/                       ← CRUD de turnos
    └── LimpiezaEjecucionResource/           ← CRUD de ejecuciones
```
