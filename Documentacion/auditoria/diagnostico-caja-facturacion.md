# Diagnóstico de caja, cobros y facturación

Fecha: 2026-10-29. Proyecto: Hotel Bugambilia.

## Dictamen

El siguiente bloque de trabajo debe cerrar el ciclo financiero. Hay una base de cuentas, pagos, ventas, facturas y pasarela, pero no está completo el control de caja por turno. No corresponde declarar el sistema listo para producción basándose solo en pantallas existentes o en las pruebas de cocina.

Este diagnóstico revisa código, esquema, requisitos y pruebas del circuito financiero. No certifica cumplimiento fiscal ni sustituye la aceptación del responsable contable. Los controles de otros módulos, respaldos y operación se incluyen abajo como criterios pendientes de validación, no como ausencias demostradas.

## Lo que ya existe

- Cuentas con consumos, cargos, abonos, saldo y cierre que genera una venta histórica.
- Registro de pagos que bloquea y recalcula la cuenta y rechaza estados que ya no aceptan pagos.
- Emisión de factura a partir de venta, series, autorizaciones, folios y detalles históricos.
- Transacciones y bloqueos en emisión de factura; restricciones de numeración por serie.
- Integración de pasarela con confirmación y reembolsos, y pruebas que simulan sus respuestas.
- Auditoría en modelos de pagos y documentos. Eso es una base útil, pero no reemplaza el libro de caja ni el arqueo.

## Hallazgos priorizados

| ID | Prioridad | Diagnóstico | Evidencia y consecuencia | Corrección / aceptación |
| --- | --- | --- | --- | --- |
| FIN-01 | P0 | Caja por turno incompleta respecto de RF-68/RF-69. | Se encontraron grupos de menú y un `caja_codigo` en series; no modelos, migraciones o casos de uso de apertura, sesión, arqueo o cierre operativo de caja en el alcance buscado. | Registrar caja física, responsable, apertura por moneda, movimientos, retiros, conteo, diferencias y aprobación de cierre. Un cierre debe congelar sus movimientos y conservar el histórico. |
| FIN-02 | P0 | Falta un registro estructurado suficiente para cuadrar efectivo y vuelto por moneda. | `RegistrarPagoCuenta` guarda el vuelto en observaciones y registra el monto aplicado. `pagos_cuenta` no contiene sesión de caja, efectivo recibido, vuelto por moneda ni fotografía de la tasa aplicada. | Separar monto recibido, aplicado, vuelto y su moneda; conservar tasa y equivalencia de la operación. Poder reconstruir el dinero físico esperado sin interpretar textos libres. |
| FIN-03 | P0 | Hay una conversión silenciosa 1:1 cuando falta tasa. | `EmitirFacturaDesdeVenta.php:63-65` utiliza `1.0` si no encuentra registro para monedas distintas. `ConvertirMoneda.php` también tiene una salida que devuelve el monto sin conversión. | Bloquear operaciones entre monedas distintas cuando no exista una tasa válida. Conservar la tasa usada, sin recalcular históricos con la tasa posterior. |
| FIN-04 | P0 | Confirmación de pago y conciliación bancaria se están confundiendo. | `ConfirmarPagoPasarela.php:68-76` marca `Conciliada` al registrar el pago y compara transacción con pago interno; no usa un depósito o liquidación bancaria como fuente independiente. | Separar cobro confirmado de liquidación bancaria conciliada. Incorporar bruto, comisión, neto, referencia de liquidación, fecha y diferencia. Una discrepancia no debe figurar automáticamente conciliada. |
| FIN-05 | P0 | Las acciones personalizadas de anulación revisadas no exigen un permiso específico. | `FacturaResource.php:217-228` y `ViewFactura.php:54-78` condicionan la acción al estado de la factura; no se observa autorización de supervisor en esas acciones ni en `AnularFacturaFiscal`. La policy general no demuestra por sí sola protección de estas acciones personalizadas. | Permiso explícito de anulación y prueba con usuario de solo lectura. Rechazar la invocación directa del servidor, además de ocultar el botón. Registrar solicitante, autorizador, motivo y fecha. |
| FIN-06 | P1 | Anular documento, anular venta y devolver dinero son recorridos separados. | `AnularFacturaFiscal` cambia factura y folio. `AnularVenta` cambia venta y anula facturas, pero no devuelve el pago. `ReembolsarPagoCuenta` modifica pagos. No se encontró un flujo completo de documentos de ajuste vinculado en este alcance. | Definir una operación autorizada que relacione documento original, ajuste, reembolso y movimiento financiero, sin asumir que anular una factura devuelve efectivo o una tarjeta. Conservar evidencias e impedir doble devolución. |
| FIN-07 | P1 | Hay una diferencia entre requisitos y validación del motivo de anulación. | RF-64 pide motivo de al menos diez caracteres; `AnularFacturaFiscal.php:26` solo rechaza un texto vacío. | Alinear regla, interfaz y servidor; probar motivo insuficiente, estados inválidos y repetición. La regla se toma del requisito del proyecto, no se presenta como interpretación de una norma. |
| FIN-08 | P1 / verificar | Faltan pruebas integrales específicas de emisión y cierre concurrentes en el conjunto revisado. | Se encontraron pruebas de cuentas y pasarela, pero no escenarios funcionales que invoquen emisión de factura/folio o anulación fiscal directamente. El cierre valida el estado antes del bloqueo; debe revalidarlo dentro de la operación protegida. | Ejecutar dos cobros, dos cierres y dos emisiones simultáneas; verificar que solo exista un efecto válido, con respuesta controlada al reintento. Comprobar también reversión completa si falla la emisión. |

P0 bloquea la liberación del flujo financiero. P1 debe quedar resuelto o explícitamente fuera del alcance de la primera liberación, con su operación deshabilitada y un procedimiento definido.

## Flujo objetivo para el encargado

Apertura de caja → recibir o aplicar pagos → registrar ingresos/egresos → emitir comprobante o factura según la operación → autorizar ajustes y devoluciones → arqueo físico → revisar diferencias → cierre del turno → entrega o depósito → conciliación independiente.

Reservas, restaurante, buffet y servicios deben alimentar cuentas y pagos consistentes. Cada evento financiero se registra una vez. Las tarjetas y los pagos en línea se presentan por separado del efectivo físico. Las porciones de buffet no se convierten en cobros adicionales automáticamente.

El encargado necesita un asistente de caja equivalente al de cocina: **abrir turno**, **cobrar**, **registrar salida autorizada**, **contar**, **revisar diferencia** y **cerrar**. Debe mostrar qué falta para cerrar y quién debe autorizarlo.

## Orden de implementación

1. **Corregir los controles críticos existentes:** tasa obligatoria, permisos específicos, separación entre confirmación y conciliación, y contrato monetario de los pagos. Definir las reglas de anulaciones y devoluciones con el responsable operativo.
2. **Implementar caja operativa:** cajas, sesiones, fondo inicial, movimientos inmutables por moneda y canal, egresos autorizados, arqueo, diferencias, cierre y entrega. Impedir modificar turnos cerrados.
3. **Integrar caja con todos los cobros y documentos:** reservas/anticipos, restaurante/buffet, servicios, pagos mixtos, anulaciones y reembolsos. Tratar por separado dinero confirmado y documento pendiente de emisión; una falla local no debe hacer desaparecer un cobro ya confirmado externamente.
4. **Completar facturación y recuperación:** series vigentes y agotadas, fotografía monetaria/fiscal, reimpresión del documento existente, ajustes, idempotencia y recuperación de fallos. Revisar el texto de las acciones para distinguir un registro interno de un trámite externo.
5. **Ensayar un día completo antes del piloto:** dos cajeros, cambio de turno, operaciones simultáneas, dinero físico, tarjetas, devoluciones, diferencias y cierre. Revisar el resultado con recepción, caja, cocina y administración.

## Criterios de salida para una versión madura

- Toda entrada o salida tiene origen, responsable, moneda, método, referencia y movimiento correspondiente.
- El arqueo puede reconstruirse por moneda: fondo inicial + entradas - salidas = efectivo esperado. Las diferencias quedan explicadas y autorizadas.
- El turno cerrado conserva sus datos y no recibe cambios retroactivos silenciosos.
- Un doble clic, reintento, webhook repetido o dos operadores no duplican pagos, ventas, facturas ni reembolsos.
- El cobro y la factura coinciden con la cuenta; las excepciones quedan identificadas como pendientes, no como éxitos falsos.
- Se prueban anticipos, abonos, pagos mixtos, propina, vuelto en otra moneda, reembolsos parciales y totales y anulación autorizada.
- Las tarjetas y transferencias se cotejan con liquidaciones externas y sus comisiones.
- Se comprueba la continuidad del recorrido reserva → estancia/consumos → cobro → documento → cierre de caja y del recorrido cocina → buffet/restaurante → cuenta → cobro.
- Existe una matriz de permisos probada con usuarios reales de cada rol y aceptación del responsable contable de la configuración fiscal.
- Se demuestra la restauración de un respaldo en un entorno separado y se verifica el manejo de fallos de tareas, notificaciones y registros operativos. Estos puntos requieren evaluación específica; no se auditaron en este recorrido.

## Validación realizada y límites

Se ejecutó en la base sintética `hotel_auditoria_sintetica`, PostgreSQL local `127.0.0.1:55448`:

`tests/Feature/Cuentas`, `tests/Feature/Facturacion`, `tests/Unit/BusinessLogic/Facturacion` y `tests/Unit/Actions/Facturacion`.

Resultado: **41 pruebas aprobadas, 184 comprobaciones**. Los servicios externos se simulan en estas pruebas; aprobarlas no certifica conciliación bancaria real, validez fiscal ni caja por turnos. Resultado detallado: `.local/diagnostico-caja/pruebas.json`.

No se modificaron reglas de negocio, cobros reales ni la base real durante esta revisión. Los hallazgos proceden de revisión estática salvo la ejecución de la batería indicada. Los riesgos de autorización y concurrencia deben tener pruebas de reproducción específicas al corregirse.
