# Documentacion de Flujos de Procesos: Modulo Facturacion

## 1. Submodulo / Funcionalidad: Configuracion de Series Fiscales

- **Descripcion de la Pantalla / Vista:** Recursos Filament para administrar series, autorizaciones DGI y folios fiscales. Las pantallas principales son `FacturaSerieResource`, `FacturaAutorizacionDgiResource` y `FacturaFolioResource`.
- **Disparador (Trigger):** Acceso desde el grupo administrativo `Facturacion`.
- **Flujo Paso a Paso:**
    1. El administrador crea una serie fiscal activa con codigo, prefijo y `siguiente_numero`.
    2. El administrador registra una autorizacion DGI activa para esa serie, con rango autorizado y fecha de vencimiento cuando aplique.
    3. Cuando se emite una factura, el sistema ejecuta `ReservarFolioFactura`.
    4. `ReservarFolioFactura` valida que el correlativo este dentro del rango DGI.
    5. El sistema crea un registro en `factura_folios` con estado `Reservado`.
    6. Despues de crear la factura, el folio pasa a estado `Emitido`.

---

## 2. Submodulo / Funcionalidad: Emision de Factura desde Venta

- **Descripcion de la Pantalla / Vista:** Listado de facturas en `FacturaResource` y emision fiscal desde una venta/cuenta cerrada.
- **Disparador (Trigger):** Cierre de cuenta o accion administrativa que toma una `Venta` como fuente.
- **Flujo Paso a Paso:**
    1. El sistema recibe una `Venta` con sus detalles, moneda y cuenta.
    2. `EmitirFacturaDesdeVenta` bloquea la venta para evitar doble emision concurrente.
    3. Valida que la venta no tenga una factura emitida previamente.
    4. Resuelve la serie fiscal activa y su autorizacion DGI vigente.
    5. Reserva un folio mediante `ReservarFolioFactura`.
    6. Determina moneda, moneda base NIO y tasa de cambio vigente.
    7. Crea la cabecera en `facturas` con subtotales, impuestos, servicio, propina, recargos, total y datos del receptor.
    8. Copia cada `VentaDetalle` a `factura_detalles`, conservando concepto, cantidad, precio, descuento, IVA y metadata de origen.
    9. Marca el folio como `Emitido` y vinculado a la factura.

---

## 3. Submodulo / Funcionalidad: Anulacion de Factura Fiscal

- **Descripcion de la Pantalla / Vista:** Accion administrativa sobre una factura emitida.
- **Disparador (Trigger):** Solicitud del administrador con motivo de anulacion.
- **Flujo Paso a Paso:**
    1. El usuario solicita anular una factura e indica el motivo.
    2. `AnularFacturaFiscal` valida que el motivo no este vacio.
    3. El sistema bloquea la factura y sus folios dentro de una transaccion.
    4. Si la factura ya esta anulada, retorna sin duplicar efectos.
    5. Si la factura no esta emitida, detiene la operacion.
    6. Actualiza la factura a estado `Anulada`, guarda motivo, fecha y usuario.
    7. Actualiza sus folios a estado `Anulado`.

---

## 4. Submodulo / Funcionalidad: Pasarelas de Pago

- **Descripcion de la Pantalla / Vista:** `PasarelaPagoResource`, `PagoTransaccionResource` y `PagoConciliacionResource` para consultar configuracion, transacciones y conciliaciones.
- **Disparador (Trigger):** Creacion de intento de pago, webhook de pasarela o revision administrativa.
- **Flujo Paso a Paso:**
    1. El sistema resuelve o crea una pasarela activa, por ejemplo `stripe`.
    2. `RegistrarTransaccionPasarela` registra la solicitud de cobro con monto, moneda, referencia interna, referencia de pasarela e `idempotency_key`.
    3. Si ya existe una transaccion con la misma clave idempotente, retorna la existente.
    4. Al confirmarse el pago, `ConfirmarPagoPasarela` bloquea la transaccion.
    5. Registra un `PagoCuenta` aplicado en la cuenta vinculada.
    6. Marca la transaccion como `Capturada`.
    7. Crea o actualiza `PagoConciliacion` como `Conciliada`, comparando monto esperado contra monto recibido.

---

## 5. Submodulo / Funcionalidad: Pago Stripe de Reservas Publicas

- **Descripcion de la Pantalla / Vista:** Proceso publico de pago de reserva con Stripe Payment Element, usando `/pagos/stripe/reservas/intento` y `/stripe/webhook`.
- **Disparador (Trigger):** Reserva publica con canal `stripe` o solicitud explicita de intento de pago.
- **Flujo Paso a Paso:**
    1. El cliente crea una reserva publica.
    2. `ReservaController` normaliza el canal de pago a `stripe` cuando corresponde.
    3. `CrearReserva` deja la reserva en estado `Pendiente` si el cobro sera por pasarela.
    4. `CrearIntentoPagoStripeReserva` calcula el monto faltante segun `ValidarPoliticaPagoReserva`.
    5. El sistema crea un PaymentIntent en Stripe mediante `StripePaymentIntentClient`.
    6. Se registra una `PagoTransaccion` pendiente con referencia al PaymentIntent.
    7. El frontend recibe `client_secret`, `publishable_key`, monto, moneda y transaccion.
    8. Cuando Stripe envia `payment_intent.succeeded`, el webhook valida la firma con `VerificarFirmaWebhookStripe`.
    9. `ConfirmarPagoStripeReserva` vincula la transaccion a la cuenta de la reserva si aun no lo estaba.
    10. Se confirma el pago, se registra el pago en cuenta, se concilia la transaccion y la reserva queda `Confirmada`.
    11. Si el pago falla o se cancela, el webhook marca la transaccion como `Fallida`.

---

## 6. Submodulo / Funcionalidad: Reembolso Stripe por Cancelacion de Reserva

- **Descripcion de la Pantalla / Vista:** Accion de cancelacion de reserva pagada por Stripe.
- **Disparador (Trigger):** Cancelacion de una reserva con pago capturado y monto reembolsable mayor a cero.
- **Flujo Paso a Paso:**
    1. `CancelarReservaHabitacion` calcula la penalizacion vigente.
    2. `CalcularReembolsoCancelacion` determina el excedente a devolver.
    3. `ReembolsarPagoStripeReserva` busca transacciones Stripe capturadas de la reserva.
    4. Por cada transaccion, crea un refund en Stripe usando el `PaymentIntent` guardado en `referencia_pasarela`.
    5. El refund usa `idempotency_key` para evitar duplicados en reintentos.
    6. El payload de Stripe queda registrado en `pago_transacciones.response_payload.refunds`.
    7. Si el monto devuelto cubre toda la transaccion, la transaccion pasa a estado `Reembolsada`.
    8. Luego el flujo contable interno marca el pago de cuenta como reembolsado o parcialmente reembolsado.

---

## Arquitectura del Modulo

```
app/
├── Enums/Facturacion/
│   ├── EstadoFactura.php
│   ├── EstadoFolioFactura.php
│   ├── EstadoTransaccionPago.php
│   ├── EstadoConciliacionPago.php
│   ├── TipoFactura.php
│   └── TipoDocumentoAjuste.php
├── BusinessLogic/Facturacion/
│   └── VerificarFirmaWebhookStripe.php
├── Interactors/Facturacion/
│   ├── EmitirFacturaDesdeVenta.php
│   ├── ReservarFolioFactura.php
│   ├── AnularFacturaFiscal.php
│   ├── CrearIntentoPagoStripeReserva.php
│   ├── ConfirmarPagoStripeReserva.php
│   ├── ReembolsarPagoStripeReserva.php
│   ├── RegistrarTransaccionPasarela.php
│   └── ConfirmarPagoPasarela.php
├── Repository/Models/Facturacion/
│   ├── Factura.php
│   ├── FacturaDetalle.php
│   ├── FacturaSerie.php
│   ├── FacturaAutorizacionDgi.php
│   ├── FacturaFolio.php
│   ├── PasarelaPago.php
│   ├── PagoTransaccion.php
│   └── PagoConciliacion.php
├── Filament/Resources/Facturacion/
│   ├── FacturaResource/
│   ├── FacturaSerieResource/
│   ├── FacturaAutorizacionDgiResource/
│   ├── FacturaFolioResource/
│   ├── PasarelaPagoResource/
│   ├── PagoTransaccionResource/
│   └── PagoConciliacionResource/
├── Http/Controllers/Pagos/
│   └── StripeReservaPaymentController.php
└── Services/Stripe/
    └── StripePaymentIntentClient.php
```

## Tablas Principales

| Tabla                        | Responsabilidad                                                 |
| ---------------------------- | --------------------------------------------------------------- |
| `factura_series`             | Configuracion de series fiscales y siguiente correlativo.       |
| `factura_autorizaciones_dgi` | Rangos autorizados por DGI para una serie.                      |
| `factura_folios`             | Control de correlativos reservados, emitidos o anulados.        |
| `facturas`                   | Cabecera fiscal emitida desde una venta.                        |
| `factura_detalles`           | Lineas fiscales derivadas de los detalles de venta.             |
| `documentos_ajuste_fiscales` | Documentos de ajuste fiscal asociados a facturas.               |
| `pasarelas_pago`             | Configuracion administrativa y metadata de proveedores de pago. |
| `pago_transacciones`         | Intentos, capturas y fallos de pasarela.                        |
| `pago_conciliaciones`        | Resultado de conciliacion entre pasarela y pago aplicado.       |

## Reglas de Negocio

- Una venta solo puede tener una factura emitida activa.
- Los folios se reservan dentro de una transaccion y avanzan el `siguiente_numero` de la serie.
- No se emite factura si no hay serie activa o autorizacion DGI vigente.
- Una factura emitida solo se anula con motivo.
- Las transacciones de pasarela usan `idempotency_key` para evitar duplicados.
- Una transaccion capturada no se procesa dos veces.
- El webhook de Stripe debe pasar verificacion de firma antes de confirmar pagos.
- Los pagos capturados por pasarela se reflejan en cuenta y conciliacion.

## Configuracion Requerida

Variables esperadas en `config/services.php`:

```text
STRIPE_ENABLED
STRIPE_KEY
STRIPE_SECRET
STRIPE_WEBHOOK_SECRET
STRIPE_MODE
```

## Actualizaciones Recientes

- Se agrego el modulo fiscal con series, autorizaciones DGI, folios, facturas y detalles.
- Se agrego soporte de pasarelas y conciliacion.
- Se integro Stripe para pagos publicos de reservas.
- Se conecto el reembolso real de Stripe al cancelar reservas pagadas por pasarela.
- Se agregaron recursos Filament para consultar facturas, pasarelas, transacciones y conciliaciones.
- Se conecto el pago de reserva con cuenta, transaccion, conciliacion y estado de reserva.
