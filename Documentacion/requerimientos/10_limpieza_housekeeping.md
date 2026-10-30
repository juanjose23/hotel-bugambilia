# Requerimientos del Sistema: Limpieza y Housekeeping

### 3. Requerimientos del Sistema

#### 3.1. Requerimientos Funcionales (RF)

##### Requerimientos del Módulo de Turnos de Limpieza
* **RF-110:** El software gestionará los turnos de trabajo de camaristas y personal de limpieza, mostrando en pantalla nombre del turno (ej. "Turno Matutino A", 07:00 a 15:00), líder asignado, personal de apoyo, carritos/bodegas móviles asociadas, horario de inicio y fin, y estado operativo.
* **RF-111:** El S.I. permitirá crear y editar turnos de limpieza, solicitando nombre, horarios de cobertura y selección de colaboradores con permisos específicos en el módulo (`ObtenerColaboradoresLimpieza`), vinculando múltiples carritos de limpieza mediante la tabla pivote `limp_turno_carritos`.
* **RF-112:** Se asignará un identificador único de turno con el cual se vincularán las hojas de trabajo materializadas y los consumos de lencería e insumos.
* *Excepción/Regla:* Los turnos que cuenten con ejecuciones de limpieza activas o registradas en la jornada actual no podrán ser eliminados; únicamente se admitirá su cierre o desactivación.

##### Requerimientos de Planificación de Horarios y Plantillas de Checklist
* **RF-113:** El software permitirá planificar las rutinas de aseo periódicas para habitaciones y áreas comunes, mostrando en pantalla turno vinculado, frecuencia (diaria o semanal por día específico), hora estimada y lista de tareas estandarizadas (Checklist).
* **RF-114:** El S.I. permitirá configurar rutinas con destinos polimórficos (`Habitacion`, `Espacio`, `Ubicacion`), solicitando entidad a limpiar y plantilla de checklist estructurada (ej. "Tender camas", "Sanitizar baño", "Reponer toallas", "Aspirar alfombra"), verificando que el horario esté vinculado a un turno activo.

##### Requerimientos de la Materialización Automática de Ejecuciones Diarias
* **RF-115:** El comando programado por el sistema `limpieza:materializar-ejecuciones` se ejecutará de forma horaria en segundo plano (`jobs.limpieza_materializar`), examinando los horarios activos correspondientes al día y turno vigente.
* **RF-116:** El S.I. generará automáticamente una ejecución diaria en estado `Pendiente` (`LimpiezaEjecucion`) por cada detalle programado que no haya sido materializado previamente para esa fecha, copiando las tareas de la plantilla de checklist y notificando de inmediato al líder y apoyo del turno vía la campana de notificaciones de Filament (`NotificadorLimpieza`).

##### Requerimientos de la Ejecución de Limpieza y Registro de Consumos
* **RF-117:** El software presentará la hoja de trabajo digital del camarista, mostrando en pantalla la entidad física a limpiar, el cálculo de abastecimiento recomendado (comparativa entre `cantidad_actual` vs. `cantidad_ideal` del stock de la habitación), el checklist interactivo de tareas y el resumen de consumos de insumos.
* **RF-118:** Proceso de Finalización y Entrega de Limpieza de Habitación/Espacio:
  1) Verificación de la completitud de las tareas del checklist y registro de reposición de insumos:
     * i) Condición A (Checklist completado al 100% y consumos de insumos del carrito reportados): El sistema ejecutará el cambio de estado de la ejecución a "Completada", descontará los insumos utilizados del stock del carrito de limpieza, actualizará el stock físico de la habitación, cambiará el estado de la habitación de "Sucia" a "Disponible / Limpia" (`ActualizadorEstadoEspacioLimpieza`) y notificará a recepción que la habitación está lista para check-in.
     * ii) Condición B (Detección de faltante de insumos críticos de reposición o tareas del checklist incompletas): El sistema registrará la ejecución con advertencia, disparará el evento `FaltanteReposicionDetectado` al supervisor de ama de llaves y mantendrá la habitación en estado de revisión hasta subsanar el faltante.

##### Requerimientos de Recordatorios y Notificaciones de Limpieza
* **RF-119:** El comando diario programado `limpieza:enviar-recordatorios` se ejecutará automáticamente a las 12:00 PM, identificando las ejecuciones programadas del día que continúen en estado "Pendiente" y cuya hora estimada haya expirado, despachando recordatorios urgentes (`RecordatorioLimpiezaPendiente`) a la bandeja de notificaciones del camarista y del supervisor del turno.
* **RF-120:** El sistema despachará notificaciones en tiempo real ante eventos operativos: `SolicitudLimpiezaCreada` (alerta inmediata ante checkout o solicitud de recepción), `PersonalLimpiezaAsignado` y `NuevasAsignacionesLimpiezaDisponibles`, mostrándolas en la campana de alertas con sondeo de 15 segundos.
