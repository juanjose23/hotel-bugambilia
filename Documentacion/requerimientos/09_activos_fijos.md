# Requerimientos del Sistema: Activos Fijos y Mobiliario

### 3. Requerimientos del Sistema

#### 3.1. Requerimientos Funcionales (RF)

##### Requerimientos del Módulo de Registro y Codificación de Activos Fijos
* **RF-97:** El software listará todos los activos fijos y bienes muebles del hotel (televisores, aires acondicionados, mesas, sillas, camas, maquinaria), mostrando en pantalla código de inventario, nombre, categoría, marca, modelo, número de serie de fábrica, fecha de adquisición, valor monetario, ubicación física actual y estado operativo.
* **RF-98:** El S.I. permitirá registrar activos fijos individuales o crearlos automáticamente al recepcionar compras de bienes patrimoniales, solicitando nombre, categoría, marca, modelo, número de serie, ubicación inicial y fecha de compra, verificando que el número de serie de fábrica no se encuentre duplicado para la misma marca.
* **RF-99:** Se asignará un código patrimonial único de inventario generado por `GeneradorCodigoInventario` con estructura semántica (Prefijo por categoría + Año + Correlativo numérico, ej. `TV-2026-0001`, `AC-2026-0012`) con el cual se etiquetarán físicamente los bienes mediante códigos de barras o QR.
* *Excepción/Regla:* El código de inventario patrimonial y el valor histórico de adquisición serán inmutables tras su registro inicial.

##### Requerimientos del Proceso de Individualización de Activos Comprados en Lote
* **RF-100:** Proceso de Individualización de Compras Masivas de Activos Fijos:
  1) Verificación de la recepción de compra que contenga artículos catalogados como activos fijos:
     * i) Condición A (Recepción de compra aprobada con ítems tipo activo fijo): El sistema ejecutará `ProcesadorIndividualizacionCompra`, generará un código de inventario patrimonial único para cada unidad física individual, creará el registro de stock y registrará el movimiento de entrada en el almacén patrimonial hasta completar el total adquirido.
     * ii) Condición B (Cantidad a individualizar superior a la cantidad recibida en la orden): El sistema bloqueará la generación de activos excedentes conforme a las reglas de validación (`ReglasIndividualizacion`) y alertará al encargado de inventario.

##### Requerimientos de Asignación y Traslado de Activos Fijos
* **RF-101:** El software controlará la tenencia de cada activo fijo mediante asignaciones a espacios físicos (Habitación, Terraza, Restaurante, Lavandería) o colaboradores responsables, mostrando en pantalla el custodio actual y la fecha de asignación.
* **RF-102:** El S.I. procesará reasignaciones de activos cerrando la asignación previa con `fecha_fin = now()`, creando la nueva asignación activa y disparando el evento `ActivoAsignado` para actualizar la ficha del bien.

##### Requerimientos de Mantenimiento Preventivo y Correctivo
* **RF-103:** El software planificará programas de mantenimiento preventivo y atenderá reportes de mantenimiento correctivo por averías, mostrando en pantalla tipo de mantenimiento, periodicidad, fecha programada, costo incurrido, técnico o proveedor responsable y estado (`Programado`, `En Proceso`, `Completado`, `Cancelado`).
* **RF-104:** Los jobs programados diarios `VerificarMantenimientosPreventivosJob` (06:00 AM) y `NotificarMantenimientosJob` (07:00 AM) inspeccionarán los planes activos y emitirán alertas a mantenimiento sobre órdenes pendientes.
* **RF-105:** Durante la ejecución de un mantenimiento, el sistema actualizará temporalmente el estado del activo a "En Mantenimiento", y al finalizar el trabajo, `SincronizarEstadoActivoJob` restaurará automáticamente el estado operativo del bien a "Activo".

##### Requerimientos de Control y Alertas de Garantías
* **RF-106:** El software supervisará las fechas de vencimiento de las garantías comerciales de los fabricantes de cada bien mueble y equipo.
* **RF-107:** El job diario `VerificarGarantiasJob` (06:15 AM) examinará los activos fijos e identificará las garantías con vencimiento inferior a 30 días, emitiendo notificaciones preventivas al área de administración para agendar revisiones técnicas antes de la expiración de la cobertura.

##### Requerimientos de Depreciación Contable en Libros
* **RF-108:** El software calculará en tiempo real la depreciación contable lineal de los activos fijos mediante `CalcularDepreciacionActivo`, mostrando en pantalla el valor original de adquisición, vida útil en meses, valor residual estimado, depreciación acumulada a la fecha y valor neto en libros actual sin alterar registros de transacciones.

##### Requerimientos del Proceso de Baja Definitiva de Activos
* **RF-109:** Proceso de Desincorporación y Baja de Activo Fijo:
  1) Verificación del estado del activo y motivo formal de desincorporación (Obsolescencia, Daño irreparable, Venta, Pérdida o Robo):
     * i) Condición A (Activo en estado operativo con motivo de baja justificado, dictamen técnico adjunto y autorización de gerencia): El sistema registrará el acta formal de baja (`ActivoBaja`), cambiará el estado del activo a "Dado de Baja", desvinculará de inmediato cualquier asignación activa a mesas, habitaciones o colaboradores, ajustará el saldo contable y disparará el evento `ActivoDadoDeBaja`.
     * ii) Condición B (Activo previamente dado de baja, en proceso de mantenimiento activo o sin justificación reglamentaria): El sistema denegará la desincorporación y emitirá una advertencia al usuario.
