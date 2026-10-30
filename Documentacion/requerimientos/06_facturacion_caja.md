# Requerimientos del Sistema: Facturación Fiscal, Pasarelas de Pago y Cajas

### 3. Requerimientos del Sistema

#### 3.1. Requerimientos Funcionales (RF)

##### Requerimientos del Módulo de Series Fiscales y Autorizaciones DGI
* **RF-60:** El software listará las series fiscales autorizadas por la Dirección General de Ingresos (DGI), mostrando en pantalla código de serie, prefijo fiscal, rango numérico autorizado (desde/hasta), fecha de vencimiento legal, folio actual en uso y estado activo/inactivo.
* **RF-61:** El S.I. permitirá registrar y configurar series fiscales y autorizaciones DGI, solicitando serie, prefijo, número inicial, número final autorizado, fecha de resolución y fecha de caducidad, verificando que no existan solapamientos numéricos entre series fiscales activas.
* **RF-62:** Se asignará un correlativo interno de folio (`factura_folios`) con el cual se reservarán y auditarán secuencialmente los números fiscales de comprobantes emitidos.
* *Excepción/Regla:* Los rangos autorizados y folios ya emitidos serán estrictamente inmutables; no se permitirá la modificación ni eliminación física de registros de folios fiscales.

##### Requerimientos del Proceso de Emisión de Factura Fiscal desde Ventas
* **RF-63:** Proceso de Emisión de Factura Fiscal desde Venta o Cuenta de Estancia:
  1) Verificación de la cuenta/venta, serie fiscal y disponibilidad de folios autorizados:
     * i) Condición A (Venta cerrada sin factura previa, con serie activa y folio dentro del rango DGI vigente): El sistema bloqueará la venta contra doble facturación concurrente, reservará el siguiente folio correlativo, calculará subtotales, IVA (15%), propina sugerida y retenciones en NIO y USD según la tasa de cambio oficial, registrará la factura en estado "Emitida", vinculará cada detalle y marcará el folio fiscal como "Emitido".
     * ii) Condición B (Venta previamente facturada, serie fiscal vencida o agotamiento del rango autorizado por la DGI): El sistema detendrá la operación de emisión, emitirá una alerta crítica al cajero y notificará al administrador sobre la necesidad de registrar una nueva resolución fiscal antes de proseguir.

##### Requerimientos del Proceso de Anulación de Facturas Fiscales
* **RF-64:** Proceso de Anulación de Factura Fiscal Emitida:
  1) Verificación del estado de la factura y motivo formal de anulación:
     * i) Condición A (Factura en estado "Emitida" con motivo de anulación justificado de al menos 10 caracteres y usuario con permisos de auditoría): El sistema ejecutará la anulación en transacción ACID, marcará la factura en estado "Anulada", actualizará el folio a "Anulado", registrará la fecha y usuario responsable, y disparará el evento de reversión contable para emisión de nota de crédito.
     * ii) Condición B (Factura ya anulada, no emitida o solicitud sin motivo especificado): El sistema cancelará la solicitud sin alterar el estado del documento e informará que el motivo de anulación es un campo mandatorio para efectos fiscales.

##### Requerimientos de Pasarelas de Pago Electrónico y Conciliación Bancaria
* **RF-65:** El software registrará las transacciones cursadas por pasarelas de pago digitales (Stripe, POS bancario, transferencias), mostrando en pantalla referencia interna, ID de pasarela, moneda, monto original, estado de captura y conciliación bancaria.
* **RF-66:** El S.I. gestionará cobros electrónicos mediante `RegistrarTransaccionPasarela`, solicitando monto, moneda, canal de pago y clave de idempotencia (`idempotency_key`), verificando que no se dupliquen transacciones ante reintentos de red.
* **RF-67:** Se asignará un registro de conciliación (`pago_conciliaciones`) con el cual se comparará el monto liquidado por la entidad bancaria contra el total facturado en el sistema, alertando cualquier discrepancia de comisiones o depósitos.

##### Requerimientos del Módulo de Turnos, Arqueos y Cierre de Caja
* **RF-68:** El software gestionará las sesiones de caja por cajero y turno, mostrando en pantalla monto de apertura, ingresos en efectivo por moneda, cobros con tarjeta, egresos autorizados, transferencias bancarias y saldo teórico esperado en caja.
* **RF-69:** Proceso de Cierre y Cuadre de Caja (Corte de Turno):
  1) Verificación del conteo físico de divisas y vouchers contra el saldo teórico del sistema:
     * i) Condición A (Conteo físico coincide con el arqueo teórico o la diferencia está dentro de la tolerancia de redondeo): El sistema ejecutará el cierre de la sesión de caja, congelará los movimientos, emitirá el comprobante de corte Z y autorizará el depósito a caja fuerte o tesorería.
     * ii) Condición B (Presencia de faltantes o sobrantes superiores a la tolerancia): El sistema registrará el descuadre como incidencia formal con desglose por moneda, solicitará la justificación escrita del cajero y notificará de forma inmediata a la gerencia de finanzas y auditoría interna.
