# Documentación de Flujos de Procesos: Módulo Compras

## 1. Submódulo / Funcionalidad: Solicitudes de Compra

- **Descripción de la Pantalla / Vista:** Tabla con solicitudes de compra. Formulario con: código auto-generado, departamento, fecha, estado, y Repeater de items (producto, variante, cantidad, unidad, observaciones).
- **Disparador (Trigger):** Acceso desde `Compras > Solicitudes` en el panel admin.
- **Flujo Paso a Paso:**
    1. El usuario accede a la interfaz de Solicitudes y visualiza la tabla con estados.
    2. El sistema valida los permisos (Filament Shield) y muestra los datos disponibles.
    3. El usuario hace clic en "Crear" y completa los campos: departamento, fecha.
    4. El usuario agrega items mediante el Repeater: selecciona producto, variante, cantidad, unidad de medida.
    5. El sistema valida las restricciones (producto requerido, cantidad > 0).
    6. El usuario hace clic en "Crear".
    7. El sistema procesa la solicitud con estado inicial, registra el historial de estados vía `TieneHistorialEstado`, y dispara el evento `SolicitudCreada`.

---

## 2. Submódulo / Funcionalidad: Cotizaciones y Selección de Ganador

- **Descripción de la Pantalla / Vista:** Tabla de cotizaciones por solicitud. Formulario con proveedor, fechas, condiciones de pago, subtotal, impuestos, descuento, costo envío, total. Items cotizados con precio unitario.
- **Disparador (Trigger):** Click en "Cotizar" desde una Solicitud en estado aprobada.
- **Flujo Paso a Paso:**
    1. El usuario selecciona la opción de "Agregar Cotización" desde la solicitud.
    2. El sistema despliega el formulario con proveedor, fechas, condiciones de pago.
    3. El usuario completa los campos obligatorios y agrega items con precio unitario.
    4. El sistema calcula automáticamente subtotales y total (`CalcularTotalesOrden`).
    5. El sistema valida vía `ScoringCotizaciones`: calcula el ganador por precio + tiempo de entrega.
    6. El comprador selecciona la cotización ganadora (`es_elegida = true`, restricción unique parcial).
    7. ¿Se cumple con las validaciones?
        - Si es **No**, el sistema muestra errores (cotización sin items, totales inconsistentes).
        - Si es **Sí**, el sistema marca la cotización como elegida y permite generar Orden de Compra.

---

## 3. Submódulo / Funcionalidad: Órdenes de Compra

- **Descripción de la Pantalla / Vista:** Tabla de órdenes generadas desde cotizaciones ganadoras. Estados: Emitida, Parcial, Completada, Cancelada.
- **Disparador (Trigger):** Click en "Generar OC" desde una cotización elegida.
- **Flujo Paso a Paso:**
    1. El sistema genera la Orden de Compra tomando los datos de la cotización ganadora.
    2. Copia proveedor, moneda, tasa de cambio, items seleccionados.
    3. La OC queda en estado "Emitida".
    4. El usuario puede cancelar la OC solo si no tiene recepciones (`ValidarCancelacionOrden`).
    5. El sistema emite notificación `OrdenEmitida` vía el listener `EnviarNotificacionOrdenEmitida`.

---

## 4. Submódulo / Funcionalidad: Recepciones de Compra

- **Descripción de la Pantalla / Vista:** Tabla de recepciones. Formulario con orden de compra, fecha, items recibidos (cantidad, lote, fecha vencimiento).
- **Disparador (Trigger):** Click en "Registrar Recepción" desde una OC emitida o parcial.
- **Flujo Paso a Paso:**
    1. El usuario registra la recepción de productos contra una OC.
    2. El sistema valida que solo se pueda recepcionar desde estado "Pendiente" (`ValidarTransicionRecepcion`).
    3. El usuario ingresa cantidades recibidas, números de lote, fechas de vencimiento.
    4. El sistema ejecuta `CreadorLoteRecepcion`: crea registros en inventario (lote, stock, movimiento).
    5. La recepción actualiza el estado de la OC (Parcial o Completada).
    6. Si hay discrepancias entre cantidad solicitada y recibida, se dispara `RecepcionConDiscrepancia`.

---

## 5. Submódulo / Funcionalidad: Devoluciones a Proveedor

- **Descripción de la Pantalla / Vista:** Formulario de devolución con recepción origen, items a devolver, motivo.
- **Disparador (Trigger):** Click en "Registrar Devolución" desde una recepción completada.
- **Flujo Paso a Paso:**
    1. El usuario selecciona la recepción origen y los items a devolver.
    2. El sistema valida que el item exista en la recepción y tenga stock suficiente.
    3. El usuario ingresa cantidad, motivo y observaciones.
    4. El sistema ajusta el stock (reduce), registra movimiento de salida por devolución.
    5. Dispara evento `DevolucionCreada` y notificación.

---

## 6. Submódulo / Funcionalidad: Estrategia de Compra

- **Descripción de la Pantalla / Vista:** Lógica de negocio que analiza cotizaciones y recomienda la mejor estrategia (proveedor único vs compra dividida).
- **Disparador (Trigger):** Al evaluar cotizaciones de una solicitud.
- **Flujo Paso a Paso:**
    1. El sistema ejecuta `ResolverEstrategiaCompra` con todas las cotizaciones de la solicitud.
    2. Calcula TCO (Costo Total de Propiedad) para compras divididas.
    3. Evalúa ofertas de proveedor único.
    4. Considera urgencia para recomendar estrategia.
    5. Genera mensaje en español con la recomendación para el comprador.

---

## Arquitectura del Módulo

```
app/
├── Repository/Models/Compras/
│   ├── Solicitud.php, OrdenCompra.php, Cotizacion.php
│   ├── RecepcionCompra.php, Devolucion.php
│   └── CotizacionItem.php
├── BusinessLogic/Compras/
│   ├── ScoringCotizaciones.php              ← Algoritmo de puntuación
│   ├── ResolverEstrategiaCompra.php         ← Estrategia de compra
│   ├── ValidarTransicionRecepcion.php       ← Máquina de estados
│   ├── ValidarCancelacionOrden.php          ← Regla de cancelación
│   ├── CalcularTotalesOrden.php             ← Cálculos financieros
│   └── GeneradorReportesCompra.php          ← Ensamblador de reportes
├── Events/Compras/                          ← Eventos del dominio
├── Listeners/Compras/                       ← Notificaciones
└── Filament/Resources/Compras/
    ├── Solicitudes/                         ← CRUD + historial de estados
    ├── Cotizaciones/                        ← CRUD + selección de ganador
    ├── OrdenesCompra/                       ← CRUD + transiciones
    ├── Recepciones/                         ← CRUD + registro de lotes
    └── Devoluciones/                        ← CRUD + ajuste de inventario
```
