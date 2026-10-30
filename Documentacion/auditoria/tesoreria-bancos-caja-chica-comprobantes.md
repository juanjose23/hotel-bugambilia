# Guía de tesorería: bancos, caja chica y comprobantes

Fecha: 29/10/2026. Implementación local con datos sintéticos. Complementa la primera entrega de gestión de cajas.

## Qué quedó disponible

El menú **Caja & Facturación** contiene, además de cajas, turnos, cierres y facturas:

| Pantalla | Operación |
|---|---|
| Fondos y bancos | Crear cuentas bancarias, bóveda y caja chica; documentar saldo inicial; trasladar o reponer fondos; registrar movimientos bancarios |
| Libro de fondos | Consultar entradas y salidas por fondo y descargar auditoría CSV |
| Caja chica | Solicitar vale, autorizar o rechazar, liquidar gasto con comprobante y recibir remanente |
| Comprobantes pendientes | Ver la imagen privada, conservar versiones y decisiones, aprobar contra extracto, rechazar o pedir aclaración |
| Conciliación bancaria | Registrar partidas del extracto con evidencia, relacionarlas con el libro y liquidar capturas de pasarela con comisión |
| Devoluciones de efectivo | Solicitar devolución vinculada al pago original; autorización por otro usuario; salida del turno |
| Entregas y depósitos | Retirar efectivo de caja, mantenerlo en tránsito y confirmar recepción en otro fondo o depósito bancario |
| Fondos para turnos | Solicitar un fondo de origen identificado; otro responsable autoriza la salida del origen y entrada al turno |

Las operaciones monetarias usan transacciones, bloqueos e identificadores de reintento. El mismo identificador devuelve su resultado original; reutilizarlo con otros datos se rechaza. Los libros confirmados, extractos, evidencias y conciliaciones se conservan. La moneda e identidad de un fondo con movimientos no se cambian.

## Acceso de demostración

Administración: http://127.0.0.1:8029/admin/tesoreria/fondos

Portal de comprobantes: http://127.0.0.1:8029/portal/cuentas/5/comprobantes

| Perfil | Correo | Clave exclusiva de demostración |
|---|---|---|
| Supervisor | supervisor.caja@demo.local | SupervisorDemo2026! |
| Cajero y responsable de caja chica | caja@demo.local | CajaDemo2026! |
| Auditor de consulta | auditoria@demo.local | AuditorDemo2026! |
| Cliente de transferencias | transferencias@demo.local | ClienteDemo2026! |

El cliente inicia sesión en http://127.0.0.1:8029/auth/login . Los otros perfiles pueden entrar por http://127.0.0.1:8029/admin/login . Cambia de sesión para practicar los perfiles por separado.

Base: **hotel_cocina_demo**, en **127.0.0.1:55448**. La base real y su configuración no se modificaron. `TESORERIA_HABILITADO` sigue apagado por defecto fuera de esta demostración. Todas las referencias, imágenes, autorizaciones fiscales, cuentas y movimientos preparados para practicar son ficticios. No se enviaron cobros reales a bancos ni a pasarelas.

## Transferencia o depósito del cliente

1. Desde el detalle de su reserva, el cliente elige **Reportar transferencia o depósito**. También puede entrar al enlace de demostración indicado arriba.
2. Selecciona el banco de destino en la moneda de la cuenta y registra importe, fecha, referencia e imagen o PDF de hasta 5 MB.
3. El comprobante queda **Pendiente de revisión**. Subir la imagen no registra pago, no reduce saldo y no confirma disponibilidad.
4. El personal autorizado registra la partida real del extracto en **Conciliación bancaria**, con su soporte independiente. Registrar esa partida no añade dinero al libro por sí solo.
5. El revisor abre **Evidencias e historial**. Puede ver la imagen dentro del panel; los PDF se descargan de forma privada. La evidencia no es un enlace público.
6. En **Revisar**, elige aprobar y selecciona la partida bancaria pendiente. Cuenta bancaria, fecha, referencia, moneda e importe deben coincidir. La cuenta debe seguir admitiendo el pago y tener saldo suficiente.
7. Aprobar registra un solo pago, el abono al libro bancario y su conciliación. Cuenta y reserva reciben sus importes pagados actualizados. Si una cuenta habilitada para cierre queda liquidada, se genera su venta; puede solicitarse factura indicando serie y con permiso de emisión.
8. Un fallo de factura revierte esa aprobación completa, incluido pago, movimiento y conciliación. El comprobante continúa pendiente para resolver el problema y reintentar.
9. Rechazar exige motivo y conserva el expediente sin aplicar dinero. Pedir aclaración permite al mismo cliente adjuntar otra evidencia con los datos bancarios originales; se conservan archivos anteriores y decisiones.
10. El cliente consulta el resultado y la explicación desde su portal. Una cuenta ajena o un archivo de otro cliente se rechaza.

La revisión financiera no confirma por sí sola una habitación, mesa o espacio. La reserva conserva su flujo de confirmación y validación de disponibilidad. Una reserva cancelada, vencida o cuyo saldo ya fue cubierto por otra vía requiere resolución del hotel; no se fuerza la aplicación del comprobante ni se descarta silenciosamente.

## Caja chica

1. El supervisor crea el fondo con responsable, moneda y límite por vale. Puede iniciar en cero y financiarlo con **Trasladar / reponer fondo**.
2. El responsable solicita el vale indicando beneficiario, concepto, importe y fecha de liquidación.
3. Otro usuario autorizado aprueba o rechaza. Aprobar descuenta el efectivo del fondo; rechazar conserva el saldo. No se permite que quien solicita se autorice a sí mismo.
4. Al liquidar, otro revisor registra el gasto respaldado, la referencia y el comprobante privado, y confirma el dinero no utilizado recibido físicamente.
5. El remanente vuelve al fondo y el vale queda liquidado con gasto y devolución separados. También se admite gasto cero devolviendo todo el vale con su constancia.
6. Una reposición traslada dinero desde un fondo de origen y registra ambos lados; no vuelve a contabilizar el vale como gasto.

La liquidación de esta entrega cierra un vale con un comprobante o documento consolidado. No incorpora un desglose contable por múltiples facturas ni liquidaciones parciales sucesivas.

## Bancos y conciliación

- El saldo del libro se calcula con movimientos confirmados. Cada cuenta y fondo tiene su propia moneda.
- Un movimiento bancario de entrada o salida queda pendiente de relacionar con el extracto. El débito no puede superar el saldo autorizado del libro.
- Cada partida de extracto conserva fecha, referencia, importe, soporte y responsable. Las referencias repetidas en la misma cuenta se rechazan.
- **Relacionar libro con extracto** exige misma cuenta e importe, con el mismo signo. Cada lado solo se usa una vez. Relacionarlos no cambia sus importes ni duplica dinero.
- Las nuevas capturas de pasarela quedan **Pendientes de conciliación**. Capturar el cobro no significa recibir la liquidación del banco.
- **Liquidar cobro de pasarela** relaciona una captura con un abono neto del extracto. Importe bruto menos comisión debe ser exactamente el neto recibido, en la misma moneda. El libro recibe el neto y la conciliación conserva la comisión.
- El circuito es manual, con soporte de extracto. No se conectó una API bancaria ni se importan formatos masivos de extractos en esta entrega. Tampoco se reclasificaron automáticamente conciliaciones históricas de otros entornos.

## Fondo para el turno, entregas y depósitos

**Recibir fondo:** abre el turno con cero si aún no recibiste efectivo. En **Fondos para turnos**, solicita importe, origen y referencia. Otro responsable autoriza la entrega: se descuenta el fondo de origen y se registra el ingreso en caja. Si el origen es banco, exige un débito coincidente del extracto. Una solicitud pendiente debe resolverse antes de cerrar el turno.

**Entregar efectivo:** el cajero solicita la entrega desde su turno abierto. El dinero sale de caja y queda **En tránsito**; no se acredita todavía al destino. Otro responsable confirma recepción. Si el destino es banco, debe seleccionar el abono correspondiente del extracto. Las recepciones no modifican retroactivamente el arqueo de una sesión cerrada.

El dinero en tránsito y los vales por liquidar se consultan en sus pantallas; no se confunden con efectivo ya recibido o gasto definitivamente liquidado.

## Devolución de efectivo

1. El cajero con turno abierto selecciona un pago de efectivo original, importe y motivo.
2. Se comprueba que solicitudes pendientes y devoluciones autorizadas no superen lo cobrado.
3. Otro usuario autorizado aprueba o rechaza. Para aprobar debe existir efectivo suficiente en el turno solicitado y el turno debe continuar abierto.
4. Si existe factura emitida vinculada a la cuenta, debe resolverse antes su anulación o ajuste por el flujo fiscal correspondiente; la devolución no la anula automáticamente.
5. Aprobar genera una salida de caja vinculada a la solicitud y al pago original. Reintentar no vuelve a entregar dinero.
6. El pago y la venta originales se conservan como historia. La devolución es un registro separado: no reabre automáticamente una deuda del cliente ni cambia el monto histórico del pago. Para revisar el neto recibido deben considerarse las devoluciones y los libros de movimientos.

Si el turno ya cerró, la solicitud no se aprueba sobre ese cierre; debe rechazarse y plantearse la operación en un turno disponible. Las acciones antiguas de anular o reembolsar directamente un pago de caja siguen bloqueadas para impedir una modificación sin salida física autorizada.

## Auditoría y permisos

El auditor preparado para demostración consulta fondos, libros, vales, evidencias, conciliación, devoluciones y entregas. Puede descargar el CSV, pero no crear fondos, aprobar pagos, autorizar devoluciones o mover dinero. La descarga y visualización de archivos comprueban permisos en el servidor.

La exportación contiene movimientos de fondos y cajas, con origen, identificador, fecha, moneda, usuario, importe, concepto y referencia. Las transferencias comparten referencia y las entregas o devoluciones identifican sus expedientes. Conserva separadamente cierres, partidas en tránsito y vales para reconstruir la operación completa.

## Ejemplos conservados en la demostración

- Banco ficticio, bóveda y caja chica financiada con traslado de origen identificado.
- Turno con fondo solicitado y autorizado desde bóveda.
- Vale de C$100 autorizado y pendiente de liquidación, para practicar comprobante y remanente.
- Pago de C$50 y devolución independiente de C$20, sin modificar el pago original.
- Comprobante de C$40 aprobado contra el extracto y otro de C$40 enviado desde el portal que permanece pendiente: la carga del segundo no alteró el saldo.
- Captura ficticia de C$100 y extracto neto de C$97, con comisión de C$3, para revisar la liquidación y su evidencia.

## Verificación realizada

- **89 pruebas integradas aprobadas, 416 verificaciones**, de tesorería, cajas, cuentas y facturación.
- **Cuatro ensayos con dos procesos simultáneos:** traslados contra el mismo fondo, aprobación del mismo comprobante, solicitudes de devolución sobre el mismo pago y liquidación de la misma captura. Se preservan fondos y se impiden efectos duplicados.
- **PHPStan sin errores** en los archivos intervenidos; Pint, ESLint y TypeScript verificados. Compilación de la interfaz completada.
- Prueba real de interfaz: imagen privada visible al revisor, aprobación de transferencia, autorización de vale, devolución y envío de imagen desde el portal del cliente.
- Portal móvil de 390 px sin desbordamiento horizontal; formulario de 356 px.
- Evidencia técnica de ejecución: `.local/tesoreria-integral/`. Pruebas permanentes: `tests/Feature/Tesoreria/TesoreriaIntegralTest.php`.

## Alcance para una futura liberación

Esta es una implementación básica local de los circuitos descritos, no una certificación de todo el hotel ni un despliegue real. Falta la aceptación operativa con responsables y documentos reales en un entorno preparado para ello. La primera auditoría también identificó un problema independiente del seeder general de reservas, que esta entrega no resuelve.

Continúan fuera de esta entrega: cambio físico entre divisas con vuelto mixto, distribución y entrega de propinas, arqueo por denominaciones y relevo de cajeros, importación bancaria masiva, liquidaciones parciales sucesivas de vales y nuevas integraciones fiscales externas. Los cobros de caja mantienen la moneda de la cuenta. Los documentos de la demostración no tienen validez fiscal ni bancaria.

## Activación en el proyecto principal — 29/10/2026

Tesorería quedó habilitada en la aplicación principal local, sobre `hotel_bugambilias`, después de respaldo y ensayo en copia restaurada. Se integraron los permisos con los perfiles del hotel, se preservaron las 18 tablas históricas verificadas y no se copiaron datos de demostración. Consulta [Activación del proyecto principal](activacion-tesoreria-proyecto-principal.md).
