# Reservas de mesas, pedidos y promociones

Guía de operación del flujo revisado el 2 de noviembre de 2026.

## Preparación del encargado

1. Publicar el restaurante y los platillos que realmente se pueden vender.
2. Configurar la moneda y las tarifas vigentes. Los precios públicos se calculan en el servidor; el cliente no puede cambiarlos desde su navegador.
3. Verificar mesas, capacidad, zonas, mobiliario asignado y disponibilidad. La llegada requiere que la mesa pueda operar.
4. Configurar cobertura y costo de envío. Revisar los impuestos y cargos obligatorios de facturación.
5. Configurar completamente Stripe para ofrecer tarjeta. Las pruebas usan una pasarela simulada y no hacen cargos reales.
6. En promociones, definir componentes, fechas, precio o descuento, personas, noches y anticipación. Cada platillo incluido representa una unidad por reserva.

## Reserva de mesa y preorden

1. Entrar a **Restaurante → Reservar mesa** y abrir **Guía paso a paso** si se necesita ayuda.
2. Seleccionar fecha, hora, duración y número real de comensales. La disponibilidad se actualiza al cambiar esos datos.
3. Elegir una mesa o permitir la asignación automática. Solo se unen mesas compatibles del mismo restaurante y zona. El servidor rechaza conflictos y capacidad insuficiente.
4. Agregar los platillos de la preorden y sus cantidades. Esta selección se guarda en la reserva con su tarifa vigente; todavía no se envía a cocina.
5. Completar contacto y confirmar. Conservar el código y abrir **Ver mi reservación** para consultar el detalle y la cuenta.
6. Al llegar, el encargado confirma la llegada desde la gestión de mesas. La reserva debe estar vigente y dentro de la ventana de atención: desde 30 minutos antes y hasta el final del horario reservado.
7. El sistema abre la comanda con la preorden, conserva la cuenta original y sus abonos, y envía los platillos a cocina. Confirmar dos veces no debe abrir otra comanda.
8. Los consumos nuevos se agregan normalmente. Los platillos ya incluidos en la reserva no vuelven a cargarse.
9. Para retirar un platillo incluido o modificar un paquete, recepción debe ajustar la reserva y su cuenta; una anulación aislada de cocina no modifica el precio del paquete.

## Pedido a domicilio

1. Revisar carrito, cantidades y observaciones.
2. Completar nombre, correo, teléfono y dirección dentro de una zona habilitada.
3. Elegir efectivo o tarjeta, cuando esté disponible. El resumen previo es estimado; la cuenta final incluye envío e impuestos/cargos configurados.
4. **Efectivo:** se registra la comanda y se envía a cocina; el saldo sigue pendiente hasta que se registra el cobro.
5. **Tarjeta:** se crea el intento de pago después de verificar el stock. La cocina recibe la comanda cuando el servidor verifica el pago y lo aplica a la cuenta.
6. Conservar la pantalla **Tu pedido**. Permite consultar preparación, partidas, total, pagos y saldo. Si el pago quedó pendiente, se puede retomar allí.
7. Si se abandona la pantalla de tarjeta, se vuelve al seguimiento del mismo pedido. No se crea otro pedido desde esa operación.
8. Los invitados consultan desde la sesión que creó el pedido. Los pedidos creados con una cuenta de cliente también se consultan al iniciar sesión como su propietario.
9. Si cambia el stock entre iniciar y confirmar un pago, el pago queda registrado y cocina debe resolver el bloqueo. No se debe pedir al cliente que pague de nuevo.
10. Para cancelar un delivery sin pagos, el encargado cancela el pedido; se anulan sus partidas y el envío correspondiente. Si había un intento de tarjeta pendiente, se solicita su cancelación a Stripe. Los pagos existentes requieren el ajuste o reembolso autorizado antes de cancelar.

## Promociones

1. Abrir **Promociones → Reservar esta oferta**. La consulta por WhatsApp es opcional y no sustituye la reserva.
2. Revisar componentes, personas, noches, anticipación y vigencia.
3. Elegir fechas y recurso. La aplicación verifica nuevamente las condiciones y disponibilidad al confirmar.
4. Completar contacto y elegir cómo confirmar. Si se elige tarjeta, se continúa a la pantalla de pago; un registro pendiente no significa que el hotel haya recibido dinero.
5. Consultar la reserva con su código. En una oferta gastronómica, los platillos incluidos quedan como preorden y se preparan al confirmar la llegada a la mesa.
6. Una oferta de alojamiento con precio fijo debe tener configuradas las noches y los componentes; no puede usarse para reservar noches o recursos adicionales al mismo precio.
7. Los importes anunciados del paquete son precios base; el total de confirmación incorpora los cargos de facturación aplicables.
8. En paquetes de alojamiento con prestaciones gastronómicas, recepción debe coordinar la atención del menú registrado. Esta pantalla no programa automáticamente los horarios de comidas de una estancia.

## Cobro y cierre

1. Revisar la cuenta vinculada y los pagos aplicados.
2. Cobrar únicamente el saldo real pendiente, incluyendo los cargos configurados. El cambio entregado al cliente no se transforma automáticamente en propina.
3. El cierre directo se rechaza si falta registrar el cobro. Si la cuenta ya está pagada con tarjeta, después de marcar la entrega utiliza **Finalizar pedido pagado** en Pedidos o la operación de cobro de la mesa: genera la venta pendiente y cierra sin crear otro pago.
4. Conservar la trazabilidad del pago y gestionar factura o comprobante desde facturación según la operación autorizada.
5. El cobro del panel finaliza las comandas ya servidas. Las pendientes de entrega conservan su atención activa aunque el saldo esté pagado. Al finalizar el último pedido activo de una mesa, se genera su solicitud de limpieza.

## Guías en pantalla

El botón **Guía paso a paso** está disponible en reserva de mesas, checkout, seguimiento del pedido, listado y reserva de promociones. Los recorridos explican la operación sin pulsar acciones de negocio por el usuario.

El panel administrativo incorpora recorridos de **Promociones** y **Pedidos**, además de las guías operativas existentes de mesas, cocina y caja.

## Validación y activación

Las pruebas de integración se ejecutan con datos sintéticos en PostgreSQL local aislado, puerto 55448, base terminada en `_testing`. La base real no se modifica.

La migración `2026_11_02_000001_add_reglas_reserva_to_promociones_table.php` añade noches, personas y anticipación a las promociones. Antes de activar el cambio en otro entorno se debe aplicar esa migración con el procedimiento de despliegue y respaldo correspondiente, y revisar las reglas de cada oferta.

Validación reproducible:

```text
node scripts/project.mjs test tests/Feature/Restaurante tests/Feature/Reservas/UnirMesasReservaTest.php tests/Feature/Guias/GuiasOperativasTest.php tests/Unit/BusinessLogic/Reservas
node node_modules/typescript/bin/tsc --noEmit
```

Los hooks de Git conservan su ejecución local nativa; este trabajo no añade construcciones de Docker al pre-commit ni al pre-push.
