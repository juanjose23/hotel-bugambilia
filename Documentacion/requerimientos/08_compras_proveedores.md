# Requerimientos del Sistema: Compras, Cotizaciones y Proveedores

### 3. Requerimientos del Sistema

#### 3.1. Requerimientos Funcionales (RF)

##### Requerimientos del Módulo de Solicitudes de Compra
* **RF-85:** El software registrará las solicitudes de compra generadas por los distintos departamentos del hotel (Cocina, Housekeeping, Mantenimiento, Administración), mostrando en pantalla código, departamento solicitante, fecha, estado operativo (`Borrador`, `Pendiente`, `Aprobada`, `Rechazada`, `En Proceso`, `Completada`) y desglose de ítems requeridos.
* **RF-86:** El S.I. permitirá registrar solicitudes de compra mediante tablas repetidoras de ítems, solicitando departamento, fecha estimada de necesidad, producto, variante, cantidad y unidad de medida, verificando que la cantidad solicitada sea mayor a cero y que los productos pertenezcan al catálogo activo.
* **RF-87:** Se asignará un código único correlativo de solicitud con el cual se vincularán las cotizaciones de proveedores y el historial auditable de aprobaciones.
* *Excepción/Regla:* Los ítems de una solicitud de compra aprobada o con cotizaciones registradas no podrán modificarse; requerirán anulación formal para emitir un nuevo requerimiento.

##### Requerimientos de Cotizaciones y Algoritmo de Scoring Automático
* **RF-88:** El software permitirá registrar múltiples ofertas de proveedores para una misma solicitud de compra, mostrando en pantalla precios unitarios, plazos de entrega en días, condiciones de pago (Contado, Crédito 15/30/60 días), impuestos, costos de flete y evaluación comparativa.
* **RF-89:** El S.I. ejecutará el algoritmo de evaluación comparativa `ScoringCotizaciones`, ponderando precio total, tiempo de entrega, historial de cumplimiento del proveedor y facilidades de pago para sugerir automáticamente la cotización ganadora.
* **RF-90:** El software permitirá seleccionar formalmente la cotización ganadora (`es_elegida = true`), garantizando mediante restricción de unicidad parcial que solo una cotización por solicitud ostente la condición de ganadora.

##### Requerimientos de Estrategia de Compra y Optimización de Costos
* **RF-91:** El software evaluará la conveniencia financiera de la compra mediante `ResolverEstrategiaCompra`, mostrando en pantalla el análisis comparativo de Costo Total de Propiedad (TCO) entre consolidar con un único proveedor vs. dividir la adquisición entre múltiples proveedores según urgencia y costos logísticos.

##### Requerimientos del Proceso de Generación y Cancelación de Órdenes de Compra
* **RF-92:** Proceso de Generación de Orden de Compra desde Cotización Elegida:
  1) Verificación de la cotización ganadora y disponibilidad de presupuesto asignado:
     * i) Condición A (Cotización marcada como ganadora con proveedor activo y condiciones comerciales validadas): El sistema generará automáticamente la Orden de Compra (OC) en estado "Emitida", heredará precios, moneda, plazos y condiciones de pago, registrará el compromiso presupuestario y enviará la notificación correspondiente vía `EnviarNotificacionOrdenEmitida`.
     * ii) Condición B (Cotización sin aprobación de gerencia o datos fiscales del proveedor incompletos): El sistema bloqueará la emisión de la OC y solicitará la subsanación de los datos antes de emitir el documento formal.
* **RF-93:** El S.I. permitirá la cancelación de una Orden de Compra emitida (`ValidarCancelacionOrden`), verificando que no existan recepciones parciales o totales registradas asociadas a dicha orden antes de autorizar su anulación.

##### Requerimientos del Proceso de Recepción de Mercancía y Control de Discrepancias
* **RF-94:** Proceso de Recepción en Almacén contra Orden de Compra:
  1) Verificación física de cantidades, calidad y especificaciones de la mercancía recibida:
     * i) Condición A (Entrega conforme con cantidades exactas a la OC): El sistema registrará la recepción, ejecutará `CreadorLoteRecepcion` para ingresar los lotes a inventario con su fecha de vencimiento, actualizará el stock, cambiará la OC a "Completada" y habilitará la recepción para programación de pago.
     * ii) Condición B (Discrepancia por faltante de unidades, producto dañado o fecha de caducidad inferior al umbral mínimo): El sistema registrará una recepción parcial o rechazo, disparará el evento `RecepcionConDiscrepancia`, generará el acta formal para el proveedor y dejará la OC en estado "Parcial" a la espera de entrega complementaria o nota de crédito.

##### Requerimientos de Devoluciones a Proveedor
* **RF-95:** El software gestionará devoluciones de productos a proveedores originadas en recepciones completadas, mostrando en pantalla los ítems a devolver, cantidad, motivo formal y saldo a favor generado.
* **RF-96:** El S.I. procesará la devolución disminuyendo el stock en el almacén de origen, registrando el movimiento de salida por devolución y disparando el evento `DevolucionCreada` para ajuste en cuentas por pagar.
