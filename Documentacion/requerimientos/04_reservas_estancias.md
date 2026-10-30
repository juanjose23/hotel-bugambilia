# Requerimientos del Sistema: Reservas, Estancias y Pagos en Línea

### 3. Requerimientos del Sistema

#### 3.1. Requerimientos Funcionales (RF)

##### Requerimientos del Flujo Público de Reserva por Pasos (Inertia + React)
* **RF-29:** El software guiará al usuario a través de un proceso interactivo de reserva por pasos (`/habitaciones/{slug}/reservar`), mostrando en pantalla indicador de progreso (Fechas y Titular, Huéspedes, Complementos & Cuenta de Estancia, Confirmación y Pago) y resumen dinámico del costo total en tiempo real.
* **RF-30:** El S.I. permitirá registrar la reserva pública, solicitando rango de fechas (check-in y check-out), número de adultos y niños, datos del titular (nombre, email, teléfono, documento) y complementos seleccionados, verificando la disponibilidad en tiempo real sin solapamiento con otras reservas activas.
* **RF-31:** Se asignará un código único alfanumérico de reserva (ej. `RES-XXXXXX`) con el cual el huésped y el personal de recepción consultarán el estatus y confirmación de la reservación.
* *Excepción/Regla:* Se podrán modificar datos de contacto y notas especiales del huésped a excepción del código de reserva y el historial de transacciones de pago asociadas.

##### Requerimientos del Bloqueo de Días Ocupados en Calendario
* **RF-32:** El software calculará e inhabilitará interactivamente los días ocupados en el selector de fechas (`SelectorFechasEstancia`), mostrando en pantalla las noches reservadas en estilo visual deshabilitado ("Agotado").
* **RF-33:** El S.I. validará la selección de fechas en el calendario mediante `ObtenerDiasAgotadosHabitacionQuery::porHabitacion`, solicitando la habitación específica y verificando que el rango de entrada y salida no contenga ninguna noche ocupada o bloqueada internamente.
* *Excepción/Regla:* Si el usuario intenta seleccionar un rango que atraviese fechas no disponibles, el sistema limpiará automáticamente la fecha de check-out y mostrará una alerta visual impidiendo avanzar al siguiente paso.

##### Requerimientos de Complementos, Promociones y Cuenta de Estancia
* **RF-34:** El software permitirá agregar servicios adicionales (spa, traslados, cenas) y paquetes promocionales durante el flujo de reserva, mostrando en pantalla el desglose de cada ítem adicional y el recálculo automático del subtotal.
* **RF-35:** El S.I. permitirá al huésped solicitar la apertura de una Cuenta de Estancia para consumos durante su hospedaje, solicitando la confirmación de la opción y opcionalmente el límite de crédito deseado, verificando que la política de crédito del hotel permita cuentas con el método de pago seleccionado.
* **RF-36:** Se asignará una vinculación de cuenta de estancia (`cuenta_id`) con la cual se centralizarán todos los cargos de restaurante, minibar y servicios durante la estadía.
* *Excepción/Regla:* El límite de crédito solicitado estará sujeto a validación administrativa en el check-in antes de ser activado formalmente.

##### Requerimientos de Pasarela de Pagos en Línea (Stripe Webhook)
* **RF-37:** El software procesará pagos electrónicos en línea con tarjeta de crédito/débito vía Stripe (`CrearIntentoPagoStripeReserva`), mostrando en pantalla el formulario seguro de pago y el estado de la transacción (Pendiente, Procesando, Aprobada, Rechazada).
* **RF-38:** El S.I. verificará la política de pago requerida para la reserva (Abono del 50% o Pago Total 100%), solicitando la confirmación bancaria a través de webhooks seguros con firma criptográfica (`StripeWebhookHandler`), verificando la idempotencia para evitar cargos duplicados.
* **RF-39:** Se asignará un identificador de transacción y clave de idempotencia única con la cual se conciliará el cobro contra la cuenta bancaria del hotel.
* *Excepción/Regla:* Los pagos confirmados por webhook no podrán ser modificados manualmente en la base de datos, requiriéndose una operación formal de reembolso o reversión bancaria.

##### Requerimientos del Proceso de Check-in (Ingreso al Hotel)
* **RF-40:** Proceso de Check-in y Asignación de Habitación:
  1) Verificación de la reserva, pago del anticipo y estado de la habitación física:
     * i) Condición A (Reserva vigente con anticipo cubierto y habitación en estado "Disponible / Limpia"): El sistema ejecutará el cambio de estado de la reserva a "Activa / En Estancia", actualizará la habitación a "Ocupada", abrirá la cuenta de consumos del huésped y generará el registro de bienvenida.
     * ii) Condición B (Habitación no lista por limpieza o pago de garantía pendiente): El sistema impedirá la entrega de habitación, notificará al supervisor de recepción con prioridad urgente y presentará la opción de cobro en terminal POS o reasignación a otra habitación equivalente disponible.

##### Requerimientos del Proceso de Check-out, Facturación y Salida
* **RF-41:** Proceso de Check-out, Liquidación de Cuenta y Liberación de Habitación:
  1) Verificación de consumos pendientes, inventario de la habitación y hora de salida:
     * i) Condición A (Saldo total de la cuenta en cero, sin cargos pendientes de restaurante ni daños reportados en inventario): El sistema ejecutará el cierre definitivo de la estancia, generará la factura o recibo fiscal, marcará la habitación en estado "Sucia / Pendiente de Limpieza" y emitirá automáticamente la orden de trabajo para el módulo de limpieza.
     * ii) Condición B (Presencia de cargos impagos, consumos de minibar no declarados, roturas de activos o salida fuera del horario límite sin late check-out autorizado): El sistema aplicará los cargos adicionales a la cuenta de estancia, retendrá la garantía preautorizada en tarjeta o exigirá el pago inmediato en caja antes de permitir la liberación del check-out.
