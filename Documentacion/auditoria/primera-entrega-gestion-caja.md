# Primera entrega de Gestión de caja — 29/10/2026

## Alcance entregado

En el menú **Caja & Facturación** están disponibles:

- **Gestión de cajas**: crear caja, indicar ubicación y monedas, ver responsable y estado, revisar cierres con diferencias.
- **Mi turno de caja**: apertura con fondo contado y procedencia, cobro de cuentas en efectivo, ingresos, egresos autorizados, arqueo y cierre.
- **Arqueos y cierres**: consultar responsables, importes esperados, conteos y diferencias conservadas.

Cada caja admite un turno activo; cada cajero también. Un arqueo pendiente mantiene ocupada la caja hasta revisión. Los importes se separan por moneda. El egreso no puede superar los fondos registrados. Los movimientos confirmados no se editan ni se borran, incluyendo las actualizaciones directas protegidas por PostgreSQL.

El cobro de esta pantalla usa una clave de operación: repetir el mismo abono devuelve el resultado original. Cambiar sus datos reutilizando la misma clave se rechaza. La cuenta, pago, movimientos, venta y factura se guardan juntos; un error de emisión revierte esa operación completa.

## Acceso a la demostración

Aplicación: http://127.0.0.1:8029/admin/tesoreria/cajas

| Perfil | Correo | Clave solo de demostración |
|---|---|---|
| Cajero | caja@demo.local | CajaDemo2026! |
| Supervisor | supervisor.caja@demo.local | SupervisorDemo2026! |

Base exclusivamente sintética: `hotel_cocina_demo`, PostgreSQL en `127.0.0.1:55448`. La base real y su configuración no se modificaron. El módulo permanece apagado por defecto fuera de la demostración mediante `TESORERIA_HABILITADO`.

La demostración conserva una práctica realizada: apertura de C$500, cobro de C$115 recibiendo C$200 y entregando C$85 de vuelto; esperado C$615, contado C$610, faltante C$5 aprobado por otro usuario. La factura utiliza una autorización inventada sin validez fiscal. Las cajas quedan disponibles para practicar otra apertura y existen otras cuentas pendientes.

## Operación diaria

1. Administración crea la caja e indica las monedas que admite.
2. El cajero entra en **Mi turno de caja**, elige una caja disponible y cuenta el fondo de cada moneda, incluso si es cero. Anota quién entrega el fondo y su procedencia.
3. En **Cobrar una cuenta**, selecciona la cuenta, escribe el efectivo recibido y, si corresponde, solicita factura y elige serie. El sistema registra el vuelto por separado. Un abono conserva la cuenta abierta; al liquidarla se genera la venta y la factura si se solicitó.
4. Los ingresos y egresos manuales requieren motivo y pueden incluir referencia del respaldo. El cajero de demostración tiene ingresos; el permiso de egreso se mantiene separado.
5. En **Arqueo y cierre** escribe el efectivo realmente contado por moneda. Si cuadra, el turno cierra. Si hay faltante o sobrante, explica la diferencia; el turno pasa a revisión y deja de admitir movimientos.
6. Otro usuario con permiso revisa el arqueo en **Gestión de cajas** y aprueba el cierre. La aprobación conserva el importe de la diferencia; no genera dinero ni modifica el conteo.
7. Consulta el resultado en **Arqueos y cierres**.

## Correcciones financieras incluidas

- Elegir factura al liquidar una cuenta ahora solicita su emisión.
- Se rechaza emitir en otra moneda sin tasa válida y usar autorizaciones con fecha futura.
- Un usuario de consulta no puede ejecutar la anulación de factura; el motivo exige diez caracteres.
- La impresión usa el IVA y el total de línea almacenados.
- Una reserva pública con transferencia pendiente de revisión no aplica dinero ni se confirma automáticamente.
- Un segundo cierre concurrente de la misma cuenta devuelve un rechazo controlado.
- Mientras esté habilitada caja, un pago registrado en su libro no se anula ni reembolsa por las acciones antiguas, porque falta implementar su devolución física autorizada.

## Límites y siguientes entregas

Esta entrega no completa la tesorería del hotel. Falta desarrollar:

1. **Comprobantes de transferencia/depósito**: cargar imagen, cola de revisión, aprobar/rechazar, impedir referencias repetidas y aplicar dinero únicamente al aprobar.
2. **Caja chica**: fondos, solicitudes, comprobantes, autorizaciones y reposiciones.
3. **Bancos y conciliación**: cuentas, estados de cuenta, depósitos, liquidaciones de tarjetas, comisiones y correspondencia con movimientos bancarios. Hallazgo pendiente: confirmar una captura de pasarela todavía la marca conciliada sin comparar con un movimiento bancario.
4. **Entrega de efectivo** a administración/bóveda, recepción por otro usuario y depósitos a bancos con ambos lados registrados. La apertura actual anota procedencia; no descuenta automáticamente una bóveda.
5. **Devoluciones de efectivo** autorizadas y vinculadas al pago original.
6. **Arqueo por denominaciones**, turnos con relevo y reportes exportables de auditoría.
7. **Cambio entre divisas** con tasa congelada y vuelto desglosado. En esta entrega el cobro en efectivo debe usar la moneda de la cuenta; las monedas del fondo no se suman entre sí.
8. **Propinas en caja** con su distribución y entrega. El cobro nuevo no recibe propinas separadas; debe existir el cargo correspondiente en la cuenta. No representa todavía un circuito de liquidación de propinas.
9. Extender la protección contra reenvíos a los formularios antiguos de abonos, además de la pantalla nueva que ya la aplica.

La aprobación de una diferencia registra una revisión administrativa; no salda contablemente el faltante ni lo convierte en ingreso o egreso.

## Instalación y permisos futuros

Se incluyeron la migración `Tesoreria/2026_10_29_000002_create_operational_cash_registers.php` y el seeder `Database\Seeders\Tesoreria\CajaPermisosSeeder`. Este último crea permisos y no los concede automáticamente. La instalación real requiere su propia preparación y validación; no se realizó.

Permisos separados: `ViewAny:Caja`, `Create:Caja`, `Abrir:Caja`, `Operar:Caja`, `Egreso:Caja`, `Cerrar:Caja`, `CerrarDiferencia:Caja`, `Auditar:Caja`. La emisión conserva además `Create:Factura` y la anulación `Anular:Factura`.

## Evidencia

- Pruebas permanentes: `tests/Feature/Tesoreria/GestionCajaTest.php` y `tests/Feature/Facturacion/IntegridadFiscalTest.php`.
- Evidencia de ejecución local: `.local/implementacion-caja/`.
- Pruebas reales con dos procesos: una apertura, un abono, retiros limitados al fondo disponible y exclusión entre cierre y movimiento.
- Revisión visual en escritorio y móvil; se practicaron apertura, cobro con factura, vuelto, arqueo con faltante y aprobación del supervisor.
- La auditoría anterior detectó una prueba independiente del seeder general de reservas que falla por datos de prueba. Esta entrega no certifica toda la aplicación ni resuelve ese seeder general.

### Resultado final de validación de esta entrega

- **66 pruebas integradas aprobadas, 292 verificaciones**: 24 de caja, 13 de integridad fiscal y 29 de flujos financieros existentes.
- **PHPStan: sin errores** en los archivos intervenidos. **Pint: aprobado**.
- **Cuatro escenarios simultáneos verificados** con dos procesos independientes: apertura repetida, abono repetido, egresos contra el mismo fondo y cierre frente a un movimiento.
- **Interfaz comprobada**: cajero y supervisor, cobro con factura de demostración, vuelto, faltante, aprobación y consulta del cierre. Detalle móvil: ancho de pantalla y contenido 390 px; modal de 343 px sin desbordamiento.

Estos resultados corresponden al alcance financiero probado; no constituyen una certificación global de todos los módulos ni de una integración fiscal o bancaria externa.

## Ampliación posterior

Los pendientes de bancos, caja chica, comprobantes, conciliación y devoluciones ya cuentan con implementación básica en la demostración. Consulta la [guía de la ampliación](tesoreria-bancos-caja-chica-comprobantes.md) para el alcance actual, permisos, accesos, pruebas y límites. Los apartados anteriores describen la primera entrega histórica.
