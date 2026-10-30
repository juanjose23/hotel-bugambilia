# Requerimientos del Sistema: Tareas en Segundo Plano (Jobs) y Auditoría

### 3. Requerimientos del Sistema

#### 3.1. Requerimientos Funcionales (RF)

##### Requerimientos de la Arquitectura de Background Jobs
* **RF-140:** El software desacoplará las operaciones pesadas, notificaciones masivas y rutinas de mantenimiento del hilo principal HTTP, procesándolas mediante colas de trabajo asíncronas (`queue:work`) respetando estrictamente el patrón de capas: Scheduler/Queue Worker → Job → Interactor → BusinessLogic & Repository.

##### Requerimientos de Tareas Programadas de Restaurante y Reservas
* **RF-141:** Proceso Automatizado de No-Shows de Restaurante (`ProcesarNoShowsRestauranteJob`):
  1) Verificación periódica (cada 15 minutos) de reservas de mesa activas:
     * i) Condición A (Huésped presente o reserva dentro del margen de tolerancia de 30 minutos): El sistema mantendrá la mesa reservada y la reserva en estado "Confirmada".
     * ii) Condición B (Reserva con más de 30 minutos de atraso sin check-in de llegada registrado): El sistema ejecutará automáticamente la expiración de la reserva marcándola como "No-Show", liberará la mesa asignada cambiándola a estado "Disponible", notificará al anfitrión y enviará aviso al cliente.
* **RF-142:** El job programado `EnviarRecordatoriosReservasJob` evaluará cada 5 minutos las reservaciones confirmadas de hotel y restaurante próximas a iniciar, despachando recordatorios automáticos por correo y alertas con indicaciones de acceso.

##### Requerimientos de Tareas Programadas de Inventario, Activos y Mantenimiento
* **RF-143:** El job diario `VerificarCaducidadesJob` examinará las existencias a las 06:00 AM, identificará los lotes con fecha de vencimiento alcanzada o dentro del umbral de alerta de 30, 15 y 7 días, marcará los lotes caducados y despachará avisos de reabastecimiento urgente.
* **RF-144:** Los jobs coordinados de activos fijos `VerificarMantenimientosPreventivosJob` (06:00 AM), `VerificarGarantiasJob` (06:15 AM), `SincronizarEstadoActivoJob` (06:40 AM) y `NotificarMantenimientosJob` (07:00 AM) inspeccionarán los calendarios de servicio técnico, generarán las órdenes preventivas, restablecerán activos reparados y alertarán sobre garantías por expirar en menos de 30 días.

##### Requerimientos de Auditoría Inmutable de Eventos Críticos
* **RF-145:** El software registrará en una bitácora inmutable de auditoría (`AuditLog`) todos los eventos críticos del sistema (emisión y anulación de facturas, cortes de caja, asignación y baja de activos, traslados de inventario, cambios de tarifas y accesos administrativos), guardando usuario responsable, marca de tiempo, dirección IP, tipo de operación, valores anteriores y valores nuevos.
* **RF-146:** El S.I. prohibirá cualquier operación de edición o eliminación directa sobre la bitácora de auditoría, garantizando la trazabilidad forense requerida por estándares de control interno hotelero.
