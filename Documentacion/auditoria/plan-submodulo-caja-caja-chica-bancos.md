# Plan del submódulo de caja, caja chica, bancos y auditoría

Fecha: 2026-10-29. Hotel Bugambilia. Estado: activado en el proyecto principal local; configuración operativa de cajas y bancos pendiente.

## 1. Qué se construirá

Un submódulo visible y operativo dentro de **Caja y facturación**, conectado con reservas, restaurante, buffet, servicios, cuentas, pagos, ventas y facturas.

Punto de partida de esta propuesta: existían piezas de cobros y facturación. Se planteó construir la gestión de cajas físicas, sesiones por cajero, libro de movimientos, caja chica, bancos operativos, arqueos y cierres. El cierre de una cuenta de huésped y el cierre de un turno de caja tendrán estados, evidencias y permisos propios.

Cada etapa debe entregar pantallas utilizables, reglas de negocio, autorizaciones, auditoría y pruebas.

## 2. Menú propuesto

| Pantalla | Trabajo del usuario | Resultado |
| --- | --- | --- |
| Inicio financiero | Revisar cajas abiertas, saldos por moneda, cobros por canal, documentos pendientes y diferencias | Accesos directos y pendientes del día |
| Gestión de cajas | Crear caja de recepción, restaurante/bar o caja chica; asignar ubicación, responsable, monedas y límites | Catálogo de cajas y fondos autorizados |
| Mi turno de caja | Abrir turno, cobrar, registrar ingreso/egreso autorizado, consultar movimientos y entregar turno | Operación diaria atribuida a un responsable |
| Arqueos y cierres | Contar por moneda/denominación, comparar, justificar, solicitar aprobación y cerrar | Corte histórico con cuadre o diferencia registrada |
| Caja chica | Administrar fondo, gastos menores, vales pendientes, liquidaciones y reposiciones | Fondo controlado y gastos respaldados |
| Bancos | Configurar cuentas, registrar depósitos, transferencias propias, comisiones y movimientos | Libro por cuenta bancaria y moneda |
| Comprobantes pendientes | Revisar imágenes de transferencias o depósitos y aprobar, rechazar o pedir aclaración | Pago aplicado una sola vez tras verificarlo |
| Conciliación bancaria | Comparar movimientos internos con extracto o liquidación independiente | Partidas conciliadas, diferencias y pendientes |
| Facturación | Emitir, consultar, reimprimir, ajustar o anular según permisos; controlar series y folios | Documentos vinculados a sus cuentas y ventas |
| Auditoría y reportes | Consultar quién hizo cada operación y comparar cierres, pagos, documentos y bancos | Evidencia para supervisión y revisión del hotel |

## 3. Crear una caja

Datos mínimos:

- Código, nombre, ubicación y tipo: operativa o caja chica.
- Responsable y usuarios autorizados.
- Monedas aceptadas, con saldos separados por moneda.
- Fondo autorizado y límites para egresos o retiros.
- Cuenta o caja de origen del fondo y destino permitido de entregas.
- Estado activo/inactivo. Una caja con historia se conserva para consulta.

Propuesta inicial: recepción como primera caja operativa; restaurante/bar y caja chica se habilitan según la operación del hotel. Los montos, tolerancias y límites serán configurables. No se inventa un saldo inicial sin registrar de dónde proviene.

## 4. Apertura y responsabilidad del turno

1. Elegir caja disponible.
2. Identificar cajero y fecha/hora del servidor.
3. Contar el fondo recibido por moneda y registrar quién lo entrega.
4. Compararlo con la entrega anterior o la transferencia de tesorería.
5. Si hay diferencia de apertura, registrar incidencia y autorización antes de operar.
6. Abrir el turno y mostrar su número, responsable y fondo.

Controles:

- Una sesión activa por caja; la apertura concurrente se protege también en base de datos.
- Un responsable de custodia por sesión. Se conserva además el usuario que ejecuta cada movimiento.
- Los movimientos de efectivo requieren una sesión abierta y permiso.
- Los pagos electrónicos del portal se registran por su canal aunque no haya un cajero atendiendo; no se convierten en efectivo de una caja física.
- Un cambio de responsable requiere entrega y recepción documentadas.

## 5. Libro de movimientos

Tipos básicos: fondo de apertura, cobro en efectivo, otros ingresos autorizados, vuelto entregado, devolución en efectivo, gasto autorizado, retiro/entrega de efectivo, transferencia entre fondos y reversión autorizada.

Cada movimiento conservará:

- Caja y sesión, fecha/hora, tipo, moneda, importe y responsable.
- Origen: pago, cuenta, reserva, venta, devolución, gasto, vale o transferencia.
- Referencia, concepto, comprobante y autorización cuando corresponda.
- Importe recibido, aplicado y vuelto, con sus monedas y tasa histórica si hay conversión.
- Identificador único de la operación para evitar duplicados por reintento.

Los movimientos confirmados no se editarán ni borrarán para corregir un saldo. Se registrará una reversión o ajuste autorizado vinculado al original. Las transferencias entre fondos tendrán salida y entrada enlazadas y se registrarán una sola vez.

## 6. Cobros y facturación integrados

Recorrido: **reserva o consumo → cuenta → pago verificado → venta/comprobante/factura → movimiento del canal correspondiente**.

- Efectivo: registrar lo recibido y lo devuelto físicamente. El pago aplicado a la cuenta se distingue del efectivo recibido.
- Tarjeta: guardar referencia y estado del cobro; la comisión y liquidación bancaria se reconocen por separado.
- Transferencia o depósito: aplicar dinero después de revisión autorizada, siguiendo el flujo del apartado 9.
- Pagos mixtos: varias aplicaciones vinculadas a una cuenta, respetando importes y monedas de cada una.
- Anticipos: vinculados a la reserva/cuenta, sin cobrarlos de nuevo al salir.
- Propinas: identificadas por separado y trazables hasta su entrega cuando corresponda.
- Factura: mantener vínculo cuenta → venta → factura → serie/folio. La reimpresión conserva el documento y número originales.
- Operaciones fallidas: distinguir dinero confirmado, documento pendiente y conciliación pendiente. Un fallo local no debe hacer desaparecer una captura ya confirmada externamente.

Bloqueos previos a operar: corregir la emisión omitida al elegir factura, tasa desconocida 1:1, permisos de anulación, vigencia de autorizaciones y detalle impreso incorrecto, ya reproducidos en la auditoría anterior.

## 7. Arqueo, cuadre, faltante y sobrante

Se permitirán arqueos de control durante el turno y un arqueo de cierre.

1. Fijar hora de corte y, para cierre, pasar a **En arqueo**, deteniendo nuevos movimientos de efectivo de esa sesión.
2. Mostrar o solicitar el conteo por denominaciones y moneda. Puede habilitarse conteo ciego según la política del hotel.
3. Calcular el saldo esperado con los movimientos confirmados hasta el corte.
4. Comparar contra el efectivo contado, conservando ambos importes.
5. Registrar y clasificar la diferencia por moneda.
6. Solicitar explicación, adjuntos y autorización cuando exista diferencia.

**Efectivo esperado = fondo inicial + entradas físicas - salidas físicas.**

**Diferencia = efectivo contado - efectivo esperado.**

- Diferencia cero: cuadre.
- Diferencia negativa: faltante.
- Diferencia positiva: sobrante.

Ejemplo exclusivamente ilustrativo, todo en córdobas:

| Concepto | Importe |
| --- | ---: |
| Fondo inicial | 1.000 |
| Efectivo recibido de un cliente | +550 |
| Vuelto entregado | -50 |
| Gasto autorizado | -100 |
| Retiro documentado a tesorería | -200 |
| Efectivo esperado al corte | **1.200** |
| Efectivo contado | **1.180** |
| Diferencia | **-20: faltante** |

El sistema conserva el faltante y su resolución. No cambia ventas, pagos ni importes esperados para forzar un cuadre. Tampoco convierte una diferencia en un gasto sin autorización.

El conteo de dólares se compara con el saldo esperado en dólares; su equivalencia no se mezcla con el efectivo físico en córdobas.

## 8. Cierre y entrega

Estados propuestos: **Abierta → En arqueo → Pendiente de autorización, si corresponde → Cerrada**.

- Conservar fondo, ingresos, salidas, saldo esperado, contado, diferencia, explicación y aprobador.
- Registrar el fondo que queda para la siguiente apertura y el dinero entregado a tesorería.
- Identificar entregante, receptor, fecha/hora y comprobante de entrega.
- Distinguir entrega a tesorería, depósito en tránsito y depósito bancario confirmado.
- Emitir comprobante de cierre con número propio, responsable y desglose por moneda/método.
- Bloquear movimientos retroactivos sobre la sesión cerrada.
- Repetir la solicitud de cierre devuelve el cierre existente o una respuesta de negocio controlada.
- Una corrección posterior conserva el cierre original y crea una incidencia/ajuste enlazado, con autorización.

El arqueo y la entrega tendrán fotografías de sus momentos respectivos. Una entrega posterior al corte no debe alterar silenciosamente el conteo que ya fue aprobado.

## 9. Transferencias con comprobante pendiente

1. El cliente reporta monto, moneda, fecha, banco/referencia e imagen del comprobante.
2. Archivo privado, vinculado a su reserva/cuenta; se validan formato, tamaño y propiedad del expediente.
3. Estado **Pendiente de revisión**. No se aplica dinero ni se confirma el pago por subir una imagen.
4. Caja contrasta el comprobante con el movimiento bancario y revisa diferencias o duplicados.
5. Un revisor autorizado aprueba, rechaza o pide aclaración.
6. Aprobar registra un único pago y actualiza cuenta/reserva según su política. Dos revisores o un doble clic no duplican el efecto.
7. Rechazar mantiene los saldos y exige motivo; las nuevas evidencias conservan versiones y decisiones anteriores.
8. El cliente consulta el estado y recibe la notificación correspondiente.

Pendientes de definición operativa: plazo de retención de habitación/recurso, aprobación tardía y pago excedente o confirmado por otra vía. El sistema deberá mostrar estas excepciones para resolución, sin descartar dinero ni confirmar disponibilidad inexistente.

## 10. Caja chica

Fondo para gastos menores con responsable, monto autorizado y límites configurables.

Procesos:

- Crear y financiar el fondo desde una caja o cuenta bancaria identificada.
- Registrar gasto con categoría, beneficiario/proveedor, motivo, importe, moneda, comprobante y autorización cuando corresponda.
- Entregar vale o anticipo por liquidar a una persona responsable.
- Liquidar el vale: comprobantes de gasto y devolución del remanente; conservar pendientes y vencimientos.
- Reponer el fondo con autorización y referencia a los gastos ya revisados.
- Realizar arqueo y cierre del responsable.

La reposición es traslado de fondos y no vuelve a registrar como gasto los comprobantes que ya fueron liquidados. Un vale por liquidar debe quedar identificado como dinero pendiente de justificar, con fecha y responsable.

## 11. Bancos y conciliación

Cuenta bancaria: banco, identificación de cuenta, moneda, titular, saldo de apertura y fecha inicial. Su consulta y modificación se restringen por rol.

Operaciones básicas:

- Depósito de efectivo proveniente de caja/tesorería, con estado en tránsito hasta verificar recepción.
- Transferencia entre cuentas propias con salida y entrada enlazadas.
- Pago o devolución por banco con comprobante y documento de origen.
- Registro de comisiones y otros movimientos autorizados.
- Libro de movimientos con saldo calculado y trazabilidad.
- Carga inicial manual de extractos/movimientos; importación de archivos bancarios en una etapa posterior según formato disponible.

Conciliación:

1. Seleccionar cuenta y período.
2. Comparar registros internos con extracto o liquidación independiente.
3. Relacionar referencias, fechas, moneda e importe, incluyendo comisiones cuando corresponda.
4. Identificar partidas pendientes, duplicadas o con diferencia.
5. Resolver y aprobar la conciliación, conservando el detalle y sus evidencias.

Confirmar un cobro no equivale a comprobar la liquidación bancaria. El saldo del libro y el saldo del banco se comparan; no se reemplaza uno por el otro para ocultar diferencias.

## 12. Auditoría, permisos y reportes

Roles propuestos:

- Cajero: su turno, cobros y solicitudes permitidas.
- Responsable de caja chica: su fondo, vales y comprobantes.
- Supervisor: autorización de diferencias, retiros y operaciones sensibles.
- Tesorería/administración: fondos, bancos, entregas y conciliaciones.
- Auditor: lectura de movimientos, soportes, cierres y decisiones, sin alterar datos.

Separar permisos para abrir caja, registrar gastos/retiros, revisar comprobantes, aprobar pagos, cerrar con diferencia, anular documentos, devolver dinero y conciliar. Las restricciones se verifican en servidor y se prueban con cada rol.

Reportes mínimos:

- Caja del día y por turno/responsable.
- Movimiento por moneda, método y origen.
- Arqueos, faltantes y sobrantes pendientes o resueltos.
- Entregas y depósitos en tránsito.
- Caja chica: gastos, vales pendientes, liquidaciones y reposiciones.
- Bancos: saldo de libro, partidas conciliadas y pendientes.
- Documentos pendientes de emitir, facturas anuladas y devoluciones.
- Rastro completo desde un cobro hasta su cuenta, factura, caja/banco y cierre.

## 13. Orden de construcción

| Etapa | Entrega visible y funcional | Condición de terminado |
| --- | --- | --- |
| 0. Seguridad y reglas financieras | Corregir las fallas críticas ya reproducidas y definir límites, responsables y tratamiento de diferencias | No se acreditan transferencias sin revisión; permisos, tasas y documentos consistentes |
| 1. Cajas y fondos | Gestión de cajas, responsables, monedas y procedencia del fondo | Puede crearse una caja real de demostración con su fondo trazable |
| 2. Mi turno y movimientos | Apertura, cobro integrado, ingresos, gastos/retiros autorizados y consulta | Cada movimiento queda vinculado a sesión, actor y origen; no hay duplicados |
| 3. Arqueo y cierre | Conteo, cuadre/descuadre, aprobación, cierre y entrega | Cuadre correcto; faltante/sobrante conservado; cierre inmutable e idempotente |
| 4. Caja chica y bancos | Fondo, vales, liquidaciones, reposiciones, cuentas bancarias y depósitos | Se conserva el dinero entre fondos y no se duplican gastos |
| 5. Comprobantes y conciliación | Portal de evidencias, bandeja de aprobación y conciliación independiente | Una aprobación genera un pago; una imagen o captura no concilia por sí sola |
| 6. Auditoría y aceptación | Reportes y ensayo integral con roles distintos | Casos críticos aprobados y personal responsable capaz de reconstruir las operaciones |

La primera entrega de desarrollo debe permitir ver **Gestión de cajas** y **Mi turno**, crear una caja de demostración, abrirla y registrar movimientos. Se conecta a los cobros existentes durante esa etapa. El trabajo posterior completa los controles de cierre y los demás fondos.

## 14. Pruebas de aceptación

- Abrir caja y rechazar dos aperturas simultáneas.
- Cobrar reserva, restaurante, buffet y servicio con efectivo, tarjeta, transferencia y pago mixto.
- Efectivo recibido, aplicado y vuelto coherentes en NIO y USD, con tasa válida cuando corresponda.
- Reintento/doble clic sin duplicar pagos ni movimientos.
- Registrar gasto/retiro autorizado y rechazar una operación sin permiso o sin fondos según la regla configurada.
- Transferencia entre fondos con ambas partes vinculadas y sin crear ingresos ficticios.
- Arqueo exacto, faltante y sobrante, con sus autorizaciones e historial.
- Bloquear nuevos movimientos durante el corte y cualquier cambio retroactivo después del cierre.
- Repetir cierre sin errores técnicos ni ventas duplicadas.
- Entrega de efectivo y depósito en tránsito/confirmado, sin contar dos veces el mismo dinero.
- Vale de caja chica, liquidación parcial, devolución del remanente y reposición sin duplicar gasto.
- Comprobante privado, reserva ajena, evidencia repetida, rechazo, aclaración y dos aprobadores simultáneos.
- Comisiones bancarias y diferencias de conciliación detectadas y resueltas.
- Emisión, reimpresión, anulación y reembolso con saldos y documentos consistentes.
- Trazabilidad completa y acceso de auditor sin capacidad de modificar.
- Restauración de respaldo y recuperación de operación interrumpida en otro entorno sintético.

## 15. Diseño técnico propuesto

- Reutilizar cuentas, pagos, ventas, facturas, folios y datos de pasarela existentes.
- Incorporar entidades de caja, sesión, movimiento financiero, arqueo/conteo, cierre, incidencia, vale/liquidación, cuenta bancaria, depósito, solicitud de transferencia y evidencia privada.
- Unificar los movimientos de dinero con origen, destino y referencia; las pantallas de caja, caja chica y banco consultan las operaciones que les corresponden.
- Restricciones en base de datos para sesiones activas y vínculos únicos de aplicación de pagos/aprobaciones.
- Transacciones y bloqueos para abrir, cobrar, transferir fondos, aprobar y cerrar.
- Importes de precisión fija, moneda explícita y fotografía de tasa; no guardar el vuelto únicamente como texto.
- Servicios de negocio separados de las pantallas; ninguna decisión monetaria se confía al formulario del navegador.
- Migraciones, pruebas y demostración primero en entorno aislado. Activación real después de validación y aceptación.

Referencias: [diagnóstico de caja y facturación](diagnostico-caja-facturacion.md), [plan de validación financiera](plan-integral-validacion-financiera.md) y requisitos RF-68/RF-69 en `Documentacion/requerimientos/06_facturacion_caja.md`.

## Primera entrega implementada — 29/10/2026

Se implementaron Gestión de cajas, Mi turno y Arqueos y cierres en la demostración local aislada. Incluye cobros en efectivo vinculados a cuenta/venta/factura, movimientos, arqueo por moneda y revisión de diferencias por otro usuario. El alcance y los pendientes están detallados en [Primera entrega de Gestión de caja](primera-entrega-gestion-caja.md). Caja chica, bancos, comprobantes de transferencia y conciliación bancaria continúan pendientes; esta entrega no representa liberación a producción.

## Ampliación local implementada — 29/10/2026

Quedaron implementados fondos y bancos, caja chica, comprobantes privados con aprobación contra extracto, conciliación manual, liquidación de capturas con comisión, devoluciones de efectivo, entregas y fondos para turnos. Se validaron 89 pruebas integradas y cuatro casos simultáneos. El alcance operativo y las limitaciones están en [Guía de tesorería](tesoreria-bancos-caja-chica-comprobantes.md). Esta actualización complementa la primera entrega y mantiene pendiente la activación real.


## Activación principal local — 29/10/2026

La activación en la base principal local ya se completó. Consulta [el informe de activación](activacion-tesoreria-proyecto-principal.md) para el respaldo, la conservación del historial, los perfiles y la configuración inicial necesaria. No corresponde a un despliegue remoto.

