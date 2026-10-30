# Plan integral de reservas, pagos, caja y facturación

Fecha: 2026-10-29. Proyecto: Hotel Bugambilia.

## 1. Objetivo y alcance confirmado

Completar y validar el recorrido reserva → pago/anticipo → cuenta → venta → factura/folio → conciliación → ajuste/anulación/reembolso → cierre de caja.

El flujo confirmado por el usuario es **transferencia o depósito con imagen del comprobante, pendiente de aprobación o rechazo por caja**. Subir una imagen no demuestra recepción de dinero, no constituye una factura fiscal y no debe confirmar la reserva ni reducir su saldo.

Las pruebas se realizaron con PostgreSQL sintético en `127.0.0.1:55448`. Se separaron las baterías en tres bases: `hotel_finanzas_regresion_20261029`, `hotel_finanzas_diagnostico_20261029` y `hotel_finanzas_concurrencia_20261029`. No se modificaron las reglas del sistema ni la base real. Los servicios de pago se simularon; no hubo cobros ni reembolsos bancarios reales.

## 2. Resultado ejecutivo de las pruebas

| Batería | Resultado | Qué demuestra |
| --- | --- | --- |
| Regresión de reservas, cuentas, pasarela y cálculos | **150 de 151 aprobadas**, 504 comprobaciones; un error | La mayoría del comportamiento existente conserva sus pruebas. Sigue fallando la prueba de datos masivos `ReservaSeederTest`, con una transacción abortada; no se atribuye una causa inicial todavía. |
| Contratos financieros específicos | **14 escenarios: 6 aprobados y 8 fallidos** | Reproduce brechas que la batería anterior no cubría, incluidos permisos, transferencia pendiente y detalle impreso. Son pruebas diagnósticas frente al comportamiento esperado, no correcciones ya aplicadas. |
| Concurrencia real | **4 escenarios, 8 procesos: 3 correctos y 1 con error técnico** | Folios, doble emisión y captura conservan un efecto válido; el cierre simultáneo necesita respuesta idempotente/controlada. |
| PDF | Generado y revisado visualmente; **hay una inconsistencia de importes** | Se puede imprimir sin consumir otro folio, pero el detalle muestra importes incorrectos. No basta con que exista un archivo PDF. |
| Comprobantes de transferencia y caja por turnos | **Circuitos pendientes de implementación en el alcance revisado** | No se presentan como pruebas aprobadas ni como pantallas ya entregadas. |

La matriz completa está en [matriz-validacion-financiera.csv](matriz-validacion-financiera.csv). Las evidencias reproducibles se conservan en `.local/validacion-financiera/`.

## 3. Fallas reproducidas y prioridad

| ID | Prioridad | Qué ocurrió | Resultado que debe exigirse |
| --- | --- | --- | --- |
| FI-13 | **P0** | Una reserva pública por transferencia quedó `CONFIRMADA`, total 5.060,00, abono aplicado 2.530,00 y saldo 2.530,00, sin aprobación de caja. | El reporte queda pendiente; dinero aplicado = 0 hasta validar el depósito. La confirmación de la reserva depende del pago realmente aprobado y su política. |
| FI-11 | **P0** | Un usuario con permisos de lectura de facturas pudo anular desde la ficha. | Permiso específico de supervisor, comprobado en servidor; un lector no puede ejecutar la acción. |
| FI-07 | **P0** | Cobrar con `tipo_comprobante=factura` cerró cuenta y venta, pero creó cero facturas. | La opción debe emitir y vincular el documento, o registrar una excepción pendiente de resolución sin presentar una emisión falsa. |
| FI-08 | **P0** | Se emitió factura en una moneda sin tasa configurada, con equivalencia 1:1. | Rechazar conversión desconocida y conservar la tasa histórica de la operación. |
| FI-09 | **P0** | Una captura se marcó conciliada usando solo el pago interno. | Separar pago confirmado de depósito/liquidación bancaria conciliada. |
| FI-14 | **P0** | El detalle impreso mostró `C$ 0.00`; el detalle guardado valía `115.00`, coincidente con el total de la factura. | Imprimir IVA y total de línea desde los campos correctos y comprobar aritmética y presentación. |
| FI-10 | **P1, bloquea emisión afectada** | Permitió emitir hoy con una autorización fechada en el futuro. | Respetar inicio y vencimiento de la autorización configurada. |
| FI-12 | **P1** | Aceptó motivo de un carácter para anular. | Aplicar el mínimo de diez caracteres definido por RF-64, en interfaz y servidor. Es una regla del proyecto; no una certificación normativa. |
| CC-04 | **P1** | Dos cierres generaron una venta, pero el segundo devolvió una excepción técnica de unicidad. | Conservar un único cierre y responder de forma controlada: operación ya completada o conflicto de negocio. |
| RG-01 | **Pendiente de diagnóstico** | La prueba del sembrado masivo terminó en transacción abortada. | Identificar y corregir el primer error, regenerar datos completos y dejar la regresión sin errores de infraestructura. |

Además siguen pendientes los requisitos operativos RF-68/RF-69: sesiones de caja, apertura, movimientos, arqueo y cierre. Tener un grupo de menú llamado Caja no completa esos procesos.

## 4. Hallazgos que ya funcionan

- Cierre y emisión fiscal explícita: cuenta liquidada, venta, detalle y folio vinculados.
- Repetir una emisión no consume otro consecutivo ni crea otra factura emitida.
- Autorización vencida y rango agotado detienen la emisión.
- El fallo de emisión revierte el cierre/venta de esa transacción y conserva un pago registrado previamente.
- Anulación repetida conserva factura y folio, sin reutilizar el número. Anular el documento no devuelve dinero por sí mismo.
- Dos emisiones de la misma venta producen una factura y un rechazo de negocio.
- Dos ventas concurrentes en una serie obtuvieron consecutivos 1 y 2, con siguiente número 3.
- Dos confirmaciones concurrentes de una captura dejaron un pago y la cuenta liquidada.
- Las pruebas existentes cubren recorridos de reserva, abonos, cancelaciones, pagos y reembolsos simulados. Eso no equivale a validar un banco real ni la aceptación fiscal de la configuración.

## 5. Flujo objetivo de transferencia o depósito

### 5.1. Cliente: reservar y reportar

1. Crear reserva y cuenta con total calculado por el servidor.
2. Mostrar importe requerido, moneda, referencia de reserva y datos de la cuenta bancaria del hotel configurados para ese canal.
3. Mostrar estado **Pendiente de pago**. Una selección del canal transferencia no crea un pago aplicado.
4. El cliente pulsa **Reportar transferencia o depósito** e indica monto, moneda, fecha de operación, banco y referencia.
5. Adjunta imagen del comprobante. El servidor valida formato real y tamaño configurado, la guarda de forma privada y la vincula a la solicitud y al cliente/reserva autorizados.
6. Se registra una solicitud con estado **Pendiente de revisión**. El saldo pagado permanece igual y el cliente recibe una constancia de recepción, no una confirmación de pago.
7. La pantalla muestra imagen, referencia, fecha de envío, historial y aviso: **Comprobante recibido. Caja debe verificarlo antes de confirmar el pago.**

### 5.2. Caja: revisar y resolver

1. Bandeja de pendientes con antigüedad, reserva, cliente, importe esperado/reportado, moneda y referencia.
2. Abrir la evidencia privada y contrastarla con el movimiento bancario. Una imagen o un texto extraído de ella no bastan para aprobar.
3. Mostrar diferencias de monto, moneda, referencia, titular o fecha y avisar de referencias/evidencias repetidas.
4. **Aprobar:** con permiso específico, bloquear solicitud y cuenta, verificar estado/saldo actual, registrar un pago aplicado vinculado a la solicitud y a la evidencia, actualizar cuenta/reserva y registrar actor/fecha/observación.
5. La aprobación es idempotente: doble clic o dos revisores generan un solo pago. La relación solicitud → pago debe protegerse también en base de datos.
6. **Rechazar:** motivo obligatorio, pago aplicado = 0 y notificación al cliente. Una nueva evidencia conserva la versión anterior y su decisión.
7. **Pedir aclaración:** mantiene el saldo y el trámite pendiente, con mensaje e historial.
8. Si la cuenta ya fue pagada por otra vía, no aplicar de nuevo ni descartar el dinero reportado silenciosamente; llevarlo a una excepción revisable según la política de saldo a favor/devolución acordada.

### 5.3. Estados separados

| Objeto | Estados propuestos y efecto |
| --- | --- |
| Solicitud de transferencia | Pendiente de revisión / Requiere aclaración / Aprobada / Rechazada / Cancelada. |
| Pago en cuenta | No se aplica por subir imagen. Se crea/aplica una vez tras aprobación. |
| Reserva | Sigue su política: pendiente mientras no haya pago aprobado suficiente; confirmar cuando corresponda. |
| Factura | Emisión y folio propios, separados de la imagen bancaria y del voucher de reserva. |
| Conciliación bancaria | Se completa al contrastar liquidación o movimiento independiente; no por recibir la imagen. |

El hotel debe definir el plazo de retención del recurso mientras se revisa el depósito y cómo resolver aprobación tardía, reserva vencida o falta de disponibilidad. No se fija un plazo arbitrario en este plan.

## 6. Datos y permisos que debe implementar el circuito

- Solicitud de pago: cliente, reserva/cuenta, moneda, monto reportado, fecha, banco, referencia, estado, actor y marcas de tiempo.
- Evidencias privadas versionadas: archivo, formato, tamaño, huella, dueño, vínculo a solicitud y acceso autenticado. No usar enlaces públicos permanentes para comprobantes.
- Decisión: revisor, permiso, monto realmente verificado, motivo, fecha y vínculo único con el pago resultante.
- Registro monetario: importe original, moneda, tasa usada, equivalente aplicado y distinción del canal. El efectivo recibido y su vuelto pertenecen al libro de caja cuando corresponda.
- Auditoría de envío, acceso de revisión y decisiones. No borrar decisiones o movimientos históricos para ocultar diferencias.
- Roles: cliente solo reporta/consulta lo suyo; caja revisa según permisos; supervisor autoriza operaciones sensibles. Ser usuario del panel no debe habilitar anulaciones automáticamente.
- Validaciones: archivos no permitidos, imagen dañada, exceso de tamaño, reserva ajena, reenvío, referencia repetida y dos revisores simultáneos.

## 7. Paquetes de implementación y condición de terminado

| Orden | Trabajo | Condición verificable |
| --- | --- | --- |
| 0 | Contener canales inseguros y corregir permisos | Una solicitud pública nunca se acredita como dinero verificado; solo un rol autorizado puede anular. Si el circuito de transferencia no está listo, el canal debe permanecer deshabilitado para operación real. |
| 1 | Corregir cobro → factura, tasa obligatoria, vigencia e impresión | FI-07, FI-08, FI-10 y FI-14 pasan; el operador puede identificar documentos pendientes de emisión. |
| 2 | Construir reporte de transferencia y bandeja de caja | Adjuntar, revisar, aprobar, rechazar y pedir aclaración funcionan con permisos, privacidad, auditoría e idempotencia. |
| 3 | Separar confirmación de pago y conciliación bancaria | Se registran bruto, comisión, neto, fecha/referencia de banco y diferencia. FI-09 pasa sin falsos éxitos. |
| 4 | Integrar caja operativa RF-68/RF-69 | Apertura por responsable/moneda, movimientos, conteo físico, diferencias justificadas y cierre inmutable. Canales electrónicos separados del efectivo físico. |
| 5 | Completar ajustes, devoluciones y recuperación | Anular documento, anular venta y devolver dinero tienen efectos explícitos y vínculos auditables; no hay doble reembolso. Una captura externa no desaparece por un fallo local. |
| 6 | Corregir respuesta de concurrencia y datos masivos | CC-04 responde de forma controlada; RG-01 deja de fallar; se puede repetir el día completo de demostración. |
| 7 | Ensayo y aceptación | Recepción, caja y administración prueban el recorrido completo y aprueban la configuración y los documentos antes de un piloto real. |

Este documento deja el plan y las fallas reproducidas. No presenta las correcciones como implementadas.

## 8. Matriz de pruebas por completar para el nuevo flujo

1. Reserva con transferencia sin imagen: ningún importe aplicado y estado pendiente.
2. Imagen válida: solicitud pendiente; no emisión fiscal automática ni confirmación de pago.
3. Archivo inválido, imagen dañada o tamaño excesivo: rechazo controlado sin archivos huérfanos.
4. Usuario ajeno: no puede enviar ni descargar la evidencia de otra reserva.
5. Aprobación exacta, parcial y en otra moneda: aplica solo lo verificado, con tasa explícita y política de anticipo.
6. Rechazo y aclaración: saldo intacto, motivo visible al cliente y auditoría conservada.
7. Reenvío de evidencia: conserva versiones y no duplica pagos.
8. Doble clic y dos revisores: una aprobación y un solo pago.
9. Pago por Stripe mientras hay transferencia pendiente: evitar doble aplicación y mostrar excepción clara.
10. Revisión después del vencimiento de la reserva o cambio de disponibilidad: resolver según política del hotel sin perder la trazabilidad del dinero.
11. Emisión, reimpresión y anulación: no duplican ni reutilizan folios; sumas impresas coinciden con datos.
12. Autorización fiscal vencida, futura, inactiva o agotada: detención o excepción controlada.
13. Cargos de habitación, restaurante, buffet y servicios: cuenta y documento coherentes, sin cobrar dos veces los consumos.
14. Reembolso parcial/total, repetido y con error del proveedor: resultado explícito y recuperación segura.
15. Arqueo por moneda, diferencias, entrega y cierre de turno: evidencia firmada/aprobada y movimientos congelados.
16. Respaldo/restauración y recuperación de una operación interrumpida: ensayo en otro entorno, sin tocar la base real.

Las pruebas de adjuntos, aprobación y caja por turnos no pudieron ejecutarse como recorridos completos porque faltan sus implementaciones. Deben quedar bloqueantes, no marcadas como aprobadas por simulación de pantallas.

## 9. Evidencias y reproducción

- Regresión válida: `.local/validacion-financiera/regresion-final.json`.
- Diagnóstico fiscal, permisos y transferencia: `.local/validacion-financiera/fiscal-final.json` (FI-01 a FI-13).
- Detalle impreso: `.local/validacion-financiera/impresion-detalle.json` (FI-14).
- Estado de transferencia reproducido: `.local/validacion-financiera/transferencia-actual.json`.
- Concurrencia: `.local/validacion-financiera/concurrencia-resultados.json`.
- PDF de prueba y revisión visual: `.local/validacion-financiera/factura-demo-no-fiscal.pdf` y `factura-demo.png`. Datos sintéticos; no es un comprobante válido de pago ni una autorización fiscal real.
- Fuentes de diagnóstico: `.local/validacion-financiera/FlujoFiscalDiagnosticoTest.php`, `escenarios.php`, `concurrencia.php` y `concurrencia.py`.
- Resumen: `.local/validacion-financiera/resumen-validacion.json`.

Se descartó una primera ejecución con interferencia entre baterías y otra con un fixture incompleto. Esos resultados no se cuentan como fallas de la aplicación. Las cifras anteriores pertenecen a ejecuciones válidas sobre bases separadas.

## 10. Condición para liberar

Cero P0 pendientes; pruebas financieras y de concurrencia sin fallas; circuito de comprobantes verificable; caja y conciliación operativas; documentos consistentes; permisos probados; recuperación demostrada y aceptación del responsable del hotel. La configuración y validez fiscal deben revisarse con el responsable competente: estas pruebas verifican comportamiento de software y no certifican cumplimiento normativo.
