# Flujos Publicos de Reservas y Actualizaciones Recientes

## Alcance

Este documento complementa `arquitectura-reservaciones.md` con el comportamiento publico de reservas en Inertia, los pagos por Stripe, la cuenta de estancia, promociones y la disponibilidad de habitaciones ocupadas.

## 1. Reserva Publica de Habitacion

- **Descripcion de la Pantalla / Vista:** `/habitaciones/{slug}/reservar`, renderizada por `habitaciones/HabitacionReservar`.
- **Disparador (Trigger):** Boton de reserva desde el detalle de una habitacion.
- **Flujo Paso a Paso:**
    1. `HabitacionController::mostrarReserva` obtiene el detalle de la habitacion con `ObtenerHabitacionDetalleLanding`.
    2. Carga opciones publicas de reserva desde `ObtenerOpcionesReservaPublicaQuery`.
    3. Carga disponibilidad de la habitacion exacta con `ObtenerDiasAgotadosHabitacionQuery::porHabitacion`.
    4. Inertia entrega a React: `room`, `opcionesReserva`, `diasAgotadosHabitacion`, `ocupacionHabitacionPorDia` y `totalHabitacionesCategoria`.
    5. `HabitacionReservar` inicializa `useFormularioReservaHabitacion` y `useCalculoFechasDisponibilidad`.
    6. El usuario completa el flujo por pasos:
        - Fechas y titular.
        - Huespedes.
        - Complementos y cuenta de estancia.
        - Confirmacion y metodo de pago.
    7. Al confirmar, el formulario envia `POST /reservas`.
    8. `ReservaController::crear` normaliza datos publicos y delega en `CrearReserva`.
    9. `CrearReserva` valida fechas, resuelve habitacion, calcula periodo, unidades, subtotal, promociones y totales.
    10. La reserva se crea con su detalle principal y los detalles adicionales seleccionados.

## 2. Bloqueo de Dias Ocupados en Calendario

- **Descripcion de la Pantalla / Vista:** Calendario de disponibilidad dentro de `SelectorFechasEstancia`.
- **Disparador (Trigger):** Carga inicial de la pantalla de reserva o seleccion de fechas.
- **Flujo Paso a Paso:**
    1. El backend calcula ocupacion desde `reserva_detalles` activos y reservas legacy compatibles.
    2. Para la pantalla de reserva de una habitacion, se usa `porHabitacion`, no el total de la categoria.
    3. Cada noche ocupada se marca en `diasAgotadosHabitacion`.
    4. `useCalculoFechasDisponibilidad` convierte esos dias en un `Set`.
    5. `esFechaDeshabilitada` bloquea fechas pasadas, fechas ocupadas y rangos que atraviesan una noche ocupada.
    6. `fechaEstaAgotada` aplica estilos de no disponible en el calendario.
    7. Si el usuario intenta seleccionar un rango con noches no disponibles, el sistema limpia el checkout y muestra alerta.

## 3. Reserva Publica de Espacio

- **Descripcion de la Pantalla / Vista:** `/espacios/{slug}/reservar`, renderizada por `EspacioReservar`.
- **Disparador (Trigger):** Boton de reserva desde el detalle de un espacio.
- **Flujo Paso a Paso:**
    1. El formulario usa componentes compartidos de `resources/js/modules/reservations`.
    2. El usuario selecciona fecha, hora, datos del titular y complementos.
    3. El backend resuelve el recurso con `ResolverIdEntidadPrincipal`.
    4. Para restaurante/espacios, `ReservaDisponibilidadQuery` valida conflicto por fecha y hora.
    5. `CalcularResumenRestauranteLogica` completa espacios sugeridos cuando aplica.
    6. La reserva se guarda con detalles normalizados, adicionales y estado segun politica de pago.

## 4. Pago de Reserva Publica

- **Descripcion de la Pantalla / Vista:** Paso de confirmacion en reserva y pagina de pago publica.
- **Disparador (Trigger):** Reserva con `canal_pago_reserva = stripe` o seleccion de pago con tarjeta.
- **Flujo Paso a Paso:**
    1. El frontend envia tipo y canal de pago.
    2. `ReservaController` asegura valores por defecto: `abono_50`, `stripe` y origen `publico`.
    3. `CrearReserva` crea la reserva y deja metadata de politica de pago.
    4. Si corresponde Stripe, se genera intento de pago con `CrearIntentoPagoStripeReserva`.
    5. La transaccion queda registrada como pendiente.
    6. Stripe confirma por webhook.
    7. `ConfirmarPagoStripeReserva` aplica pago a cuenta, concilia y confirma la reserva.

## 5. Promociones y Complementos

- **Descripcion de la Pantalla / Vista:** Paso `Complementos & Experiencias` del flujo de reserva.
- **Disparador (Trigger):** Usuario selecciona servicios, espacios adicionales o promocion.
- **Flujo Paso a Paso:**
    1. `ObtenerOpcionesReservaPublicaQuery` carga servicios, espacios y promociones disponibles.
    2. `SeccionesAdicionalesReserva` permite seleccionar complementos.
    3. El payload envia `servicios_adicionales`, `espacios_adicionales` y `promocion_id`.
    4. `ValidarSeleccionAdicionales` resuelve entidades y tarifas.
    5. `AplicarPromocionReserva` calcula descuento o precio de paquete.
    6. Los adicionales se guardan como detalles hijos del detalle principal.

## 6. Cuenta de Estancia

- **Descripcion de la Pantalla / Vista:** Seccion `SolicitudCuentaEstancia` dentro del paso de complementos.
- **Disparador (Trigger):** El cliente solicita una cuenta para cargar consumos durante su estancia.
- **Flujo Paso a Paso:**
    1. El usuario activa `solicita_cuenta`.
    2. Puede indicar `limite_cuenta_solicitado`.
    3. El backend guarda la intencion en la reserva.
    4. Los Interactors de cuentas pueden abrir, sincronizar o cerrar cuenta segun avance operativo.
    5. Los pagos de pasarela quedan vinculados a la cuenta cuando la reserva ya tiene una cuenta abierta.

## Componentes Frontend Principales

```
resources/js/pages/habitaciones/HabitacionReservar.tsx
resources/js/modules/reservations/components/PlantillaProcesoReserva.tsx
resources/js/modules/reservations/components/PasosReservaHabitacion.tsx
resources/js/modules/reservations/components/SelectorFechasEstancia.tsx
resources/js/modules/reservations/components/SelectorHuespedes.tsx
resources/js/modules/reservations/components/SeccionesAdicionalesReserva.tsx
resources/js/modules/reservations/components/ResumenConfirmacionReserva.tsx
resources/js/modules/reservations/hooks/useFormularioReservaHabitacion.ts
resources/js/modules/reservations/hooks/useCalculoFechasDisponibilidad.ts
resources/js/modules/reservations/stores/useAlmacenReserva.ts
```

## Backend Principal

```
app/Http/Controllers/Habitaciones/HabitacionController.php
app/Http/Controllers/ReservaController.php
app/Interactors/Reservas/Gestion/CrearReserva.php
app/BusinessLogic/Reservas/ParsearPayloadReserva.php
app/BusinessLogic/Reservas/ResolverHabitacionDisponibleLogica.php
app/BusinessLogic/Reservas/ValidarFechasReserva.php
app/BusinessLogic/Reservas/AplicarPromocionReserva.php
app/Repository/Queries/Reservas/ObtenerDiasAgotadosHabitacionQuery.php
app/Repository/Queries/Reservas/ReservaDisponibilidadQuery.php
app/Repository/Queries/Reservas/DisponibilidadRecursoQuery.php
```

## Actualizaciones Recientes

- Se agrego flujo publico Inertia por pasos para habitaciones.
- Se agrego persistencia de borrador de reserva en frontend.
- Se agregaron complementos: servicios adicionales, espacios adicionales y promociones.
- Se agrego solicitud de cuenta de estancia desde la reserva publica.
- Se integro pago publico con Stripe y confirmacion por webhook.
- Se agrego normalizacion de politica de pago para reservas publicas.
- Se agrego disponibilidad por habitacion exacta para bloquear dias ocupados en el calendario.
- Se mantiene compatibilidad con `reservas` legacy y `reserva_detalles` normalizado para calcular ocupacion.
