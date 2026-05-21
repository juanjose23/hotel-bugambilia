# Módulo de Compras (P2P) - Hotel Bugambilias

Este documento detalla la arquitectura, el flujo operativo y los estándares de codificación del módulo de Compras (Procure-to-Pay).

## 1. Estándares de Codificación de Documentos

El sistema utiliza un formato de codificación alfanumérico estandarizado para garantizar la trazabilidad y facilitar la auditoría. Todos los códigos se generan automáticamente en el momento de la creación del registro.

**Formato General:** `[PREFIJO]-[AÑO]-[CORRELATIVO]`

| Documento | Prefijo | Ejemplo | Lógica de Generación |
| :--- | :--- | :--- | :--- |
| **Solicitud de Compra** | `SOL` | `SOL-2026-001` | Año actual + Conteo de solicitudes del año + 1. |
| **Cotización** | `COT` | `COT-123` | Basado en el ID autoincremental de la base de datos. |
| **Orden de Compra** | `OC` | `OC-2026-005` | Año actual + Conteo de órdenes del año + 1. |
| **Recepción de Mercancía** | `REC` | `REC-2026-012` | Año actual + Conteo de recepciones del año + 1. |

> [!NOTE]
> Los correlativos se reinician cada 1 de enero. El relleno de ceros (`001`) asegura que los documentos se ordenen correctamente de forma alfanumérica.

---

## 2. Flujo Operativo (Workflow)

### Paso 1: Solicitud de Compra (`Solicitud`)
- **Origen**: Cualquier departamento crea una solicitud de necesidad.
- **Estados**: `Borrador` -> `Pendiente` -> `Aprobada` / `Rechazada`.
- **Automatización**: Al aprobarse, los ítems quedan disponibles para ser cotizados.

### Paso 2: Gestión de Cotizaciones (`Cotizacion`)
- **Vinculación**: Se debe seleccionar una `Solicitud Aprobada`.
- **Automatización**: Al seleccionar la solicitud, el sistema carga automáticamente los productos y las cantidades aprobadas.
- **Comparativa**: El sistema permite comparar múltiples cotizaciones para una misma solicitud, recomendando al proveedor más eficiente (Precio vs. Tiempo de Entrega).

### Paso 3: Orden de Compra (`OrdenCompra`)
- **Generación**: Se puede generar desde la Comparativa o manualmente vinculando una `Cotización Ganadora`.
- **Automatización**: Al elegir la cotización, la OC hereda: Proveedor, Condición de Pago, Ítems, Cantidades y Precios Negociados.
- **Estados**: `Borrador` -> `Emitida` -> `En Tránsito` -> `Recibida`.

### Paso 4: Recepción de Mercancía (`RecepcionCompra`)
- **Acción**: Se genera desde una Orden de Compra `Emitida`.
- **Automatización**: Carga todos los productos de la OC indicando la cantidad pendiente por recibir.
- **Inventario**: Al guardar la recepción, el sistema marca los ítems como ingresados al almacén.

---

## 3. Reglas de Integridad y Seguridad

Para evitar inconsistencias financieras y de inventario, se aplican las siguientes reglas:

1.  **Bloqueo de Edición**: Una Orden de Compra (OC) deja de ser editable una vez que cambia al estado `Emitida`.
2.  **Protección de Eliminación**: 
    - Las OC solo pueden eliminarse si están en estado `Borrador`. 
    - Una vez emitidas, solo pueden ser `Canceladas` (si no tienen recepciones).
3.  **No Duplicidad**: No se puede generar una cotización para una solicitud que ya tiene una Orden de Compra vinculada.
4.  **Recepción Parcial**: El sistema permite múltiples recepciones para una misma OC hasta completar la cantidad pedida.
5.  **Autorización Estricta (Shield)**: El acceso a acciones críticas (Imprimir, Ver Comparativa, etc.) está restringido por permisos granulares en **PascalCase**. El sistema NO permite bypass para el rol `super_admin` en las condiciones `visible()`, forzando la validación explícita del permiso.

> [!IMPORTANT]
> Consulta la [Matriz de Acciones y Permisos](./seguridad/MATRIZ_ACCIONES.md) para ver el detalle de qué permisos se requieren para cada estado del flujo.

---

## 4. Automatización de Formularios (UX)

El sistema implementa **Reactividad en Cascada**:
- **Limpieza Dinámica**: Al cambiar de documento de origen (Solicitud/Cotización), el formulario limpia los ítems anteriores antes de cargar los nuevos para evitar contaminación de datos.
- **Carga de Proveedores**: Al seleccionar una cotización, el sistema detecta y selecciona automáticamente al proveedor asociado.
- **Cálculo de Totales**: Todos los subtotales, IVA y Totales se recalculan en tiempo real al modificar cualquier cantidad o precio.

---

## 5. Reportes y Auditoría de Documentos

El sistema cuenta con un motor de reportes PDF de alta fidelidad basado en **Spatie PDF (Browsershot)** para formalizar las operaciones.

### Ubicación de Componentes:
- **Controlador Maestro**: `App\Http\Controllers\Compras\CompraReportController`
- **Plantillas (Blade)**: `resources/views/reports/compras/`
- **Rutas**: Prefijo `admin/compras/reportes/` protegidas por middleware `can:Compras:Imprimir*`.

### Reportes Disponibles (Serie HTB-COM):
| Reporte | Permiso (Shield) | Descripción |
| :--- | :--- | :--- |
| **Solicitud** | `Compras:ImprimirSolicitud` | Detalle de artículos pedidos y justificación. |
| **Cotización** | `Compras:ImprimirCotizacion` | Oferta formal del proveedor con precios y vigencia. |
| **Orden de Compra** | `Compras:ImprimirOrdenCompra` | Contrato legal de compra con totales e impuestos. |
| **Recepción** | `Compras:ImprimirRecepcion` | Comprobante de ingreso a almacén. |
| **Comparativa** | `Compras:ImprimirComparativa` | Cuadro técnico de precios, variantes y plazos. |

### Sistema de Auditoría:
Cada vez que un usuario genera o descarga un PDF, el sistema registra la acción en la tabla `report_audits`, capturando al usuario, la fecha y el documento referenciado. Esto es obligatorio para el cumplimiento de las normas de control interno del hotel.

---

## 6. Motor de Comparativa y Algoritmo TCO

El sistema del Hotel Bugambilias utiliza un motor de **Abastecimiento Estratégico** que va más allá del simple "precio más bajo". Aplica un modelo de **Costo Total de Propiedad (TCO)** para recomendar la mejor opción logística.

### 🧮 La Fórmula del TCO
El costo real de una compra se calcula mediante la siguiente función:

`TCO = Precio_Bruto + Penalización_Tiempo + Costo_Administrativo`

Donde:
1.  **Precio_Bruto**: Suma de productos e impuestos.
2.  **Penalización_Tiempo**: `(Precio_Bruto * 0.005) * Días_de_Entrega`. Representa el costo de oportunidad y la falta de insumos en la operación hotelera.
3.  **Costo_Administrativo**: `$25.00` fijos por cada proveedor involucrado. Gestionar 3 proveedores para una misma solicitud es más caro que gestionar solo 1.

### 🛡️ Estrategias de Recomendación

El sistema evalúa tres escenarios y recomienda el que minimice el TCO:

| Estrategia | Escenario de Aplicación | Beneficio Principal |
| :--- | :--- | :--- |
| **Proveedor Único (Urgencia)** | Un proveedor entrega en ≤ 2 días y su sobrecosto es < 12%. | **Velocidad Crítica**: Evita paros operativos. |
| **Proveedor Único (Eficiencia)** | Centralizar la compra en un proveedor es solo un 3% más caro que dividirla. | **Simplicidad**: Menos facturas, una sola descarga. |
| **Compra Dividida (Ahorro)** | El ahorro financiero al comprar a varios es tan grande que compensa los costos logísticos. | **Máximo Ahorro**: Optimización del presupuesto. |
---

## 7. Registro de Historial de Estados (`HasStatusHistory`)

- **Archivo:** `app/Traits/HasStatusHistory.php`
- **Propósito:** Registra automáticamente en `compras_historial` cada cambio de estado en los modelos de compras.

### Comportamiento

| Evento | Acción |
|--------|--------|
| `updated` (estado modificado) | Crea un registro con `estado_anterior`, `estado_nuevo`, `usuario_id` y comentario. |
| `created` | Crea un registro inicial con `estado_anterior = null`. |

> [!NOTE]
> El trait verifica si el valor de estado es un objeto (`is_object()`) antes de llamar a `method_exists('label')` para evitar TypeError cuando el valor original desde BD es un entero crudo.

## 8. Arquitectura de Casos de Uso
 
Para mantener un código limpio y desacoplado, toda la lógica de negocio se ha organizado en **Use Cases** agrupados por dominio:
 
- **Solicitudes**: Aprobación, rechazo, cancelación y obtención de datos.
- **Cotizaciones**: Selección de ganadores (ítem por ítem o total), scoring multicriterio y recomendaciones logísticas.
- **OrdenesCompra**: Generación (desde comparativa o directa), emisión y validación de integridad.
- **Recepciones**: Gestión de ingresos a almacén y transiciones de estado.
- **Proveedores**: Altas, actualizaciones y gestión de contactos.
 
> [!TIP]
> Al invocar un caso de uso desde un componente de Filament o Controller, use siempre la inyección de dependencias `app(UseCase::class)` para mantener el desacoplamiento.
 
---
 
*Hotel Bugambilias*
### Paso 1: Solicitud de Compra (Solicitud)

- **Origen**: Cualquier departamento crea una solicitud de necesidad.
- **Estados**: Borrador → Pendiente → Aprobada / Rechazada / Cancelada.
- **Aprobación**: Se realiza desde una página dedicada (/admin/compras/solicitudes/{record}/aprobar) que extiende EditRecord. Los items se muestran con campo editable cantidad_aprobada.
- **Automatización**: Aprobada → los ítems aprobados quedan disponibles para ser cotizados.
- **Casos de Uso**: AprobarSolicitud, RechazarSolicitud, CancelarSolicitud, GenerarCodigoSolicitud, ObtenerSolicitudConItems, ObtenerSolicitudParaComparativa, ObtenerSolicitudesParaComparar.

### Paso 2: Gestión de Cotizaciones (Cotizacion)

- **Vinculación**: Requiere una Solicitud Aprobada.
- **Automatización**: Al seleccionar la solicitud, el sistema carga los productos y cantidades aprobadas.
- **Comparativa**: Permite comparar múltiples cotizaciones, recomendando al proveedor más eficiente (TCO: Precio vs. Tiempo de Entrega).
- **Selección**: Se elige un ganador por ítem (SeleccionarItemGanador) o por cotización completa (ElegirCotizacionGanadora). Las demás cotizaciones se marcan como Rechazadas automáticamente vía OrdenCompraObserver.
- **Casos de Uso**: ElegirCotizacionGanadora, SeleccionarItemGanador, AnalizarScoringCotizaciones, ObtenerRecomendacionLogistica, ActualizarEstadosCotizacionesSolicitud.

### Paso 3: Orden de Compra (OrdenCompra)

- **Generación**: Desde la comparativa (GenerarOrdenesDesdeComparativa) o directa desde cotización (GenerarOrdenDesdeCotizacion).
- **Herencia**: Proveedor, condición de pago, ítems, cantidades y precios negociados.
- **Estados**: Borrador → Emitida → EnTránsito → Recibida. Terminales: Cancelada, DevueltaParcialmente, DevueltaTotalmente.
- **Observer (OrdenCompraObserver)**: Al crear la OC, marca su cotización como Aceptada y las demás como Rechazadas.
- **Verificación**: VerificarEstadoOrdenCompra se ejecuta cada vez que una recepción cambia a un estado activo. Si la suma de cantidad_recibida ≥ cantidad ordenada, la OC pasa a Recibida.
- **Columna progreso**: En la tabla, se muestra ecibido/total (ej. 6/10) con badge gris (pendiente), amarillo (parcial) o verde (completa).
- **Casos de Uso**: GenerarOrdenDesdeCotizacion, GenerarOrdenesDesdeComparativa, EmitirOrdenCompra, CancelarOrdenCompra, VerificarEstadoOrdenCompra, GenerarCodigoOrdenCompra, ObtenerOrdenCompraConItems.
### Paso 4: Recepción de Mercancía (RecepcionCompra)

- **Acción**: Se crea desde una Orden de Compra Emitida, precargando los items con saldo pendiente por recibir.
- **Automatización**: Carga todos los productos de la OC indicando la cantidad pendiente por recibir.
- **Protección de cantidad (3 capas)**:
  1. **Frontend**: maxValue(fn \ => \('cantidad_pendiente')) en el campo cantidad_recibida.
  2. **Backend**: Validación en CreateRecepcion::mutateFormDataBeforeCreate() — verifica que lo ya recibido + lo actual ≤ lo ordenado.
  3. **Base de datos**: Trigger PostgreSQL 	rg_chk_cantidad_recepcion en ecepcion_items.
- **Estados**:

| Estado | Descripción | Color |
|--------|-------------|-------|
| Pendiente | Creada, sin confirmar | gray |
| Completa | Entrega completa OK | success |
| Parcial | Entrega incompleta vs lo esperado | warning |
| ConDiscrepancia | Diferencias calidad/cantidad | orange |
| Rechazada | Todo rechazado, OC vuelve a Emitida | danger |
| EnCuarentena | Retenido para análisis | info |

- **Transiciones permitidas**:

`
Pendiente       → {Completa, Parcial, ConDiscrepancia, Rechazada, EnCuarentena}
Parcial         → {Completa, ConDiscrepancia, EnCuarentena}
ConDiscrepancia → {Completa, Rechazada, EnCuarentena}
EnCuarentena    → {Completa, Rechazada, Parcial}
Completa        → {}  (terminal)
Rechazada       → {}  (terminal)
`

- **Integración con Inventario**: Al cambiar a Completa|Parcial|ConDiscrepancia|EnCuarentena, el RecepcionInventoryObserver ejecuta UC-01 (RegistrarEntradaRecepcion) que crea los Lotes y Movimientos de Stock. Ver §2.6.
- **Conversión a Sub-Ubicaciones Jerárquicas (P2L - UC-05)**: Los ítems de mercancías recibidas que funcionan como contenedores físicos (ej. Estantes, gabinetes, armarios) se pueden convertir interactivamente en estructuras físicas recursivas (Estante ➔ Niveles ➔ Posiciones) usando la acción de cabecera **"Convertir a Estructura Física"**. Esto elimina por completo la necesidad de un módulo rígido de Activos Fijos, unificando todo el almacén bajo un único árbol recursivo. Ver [Procesos: Ubicaciones Jerárquicas, Compras e Integridad Operativa](./PROCESO_UBICACIONES_RECURSIVAS.md).
- **Rechazada**: El RecepcionObserver revierte la OC a Emitida. No se crean lotes.
- **Items**: Cada RecepcionItem registra lote_proveedor y fecha_vencimiento del producto recibido.
- **Casos de Uso**: GenerarCodigoRecepcion, GestionarTransicionRecepcion, ConvertirItemAUbicaciones (UC-05).
- **Observers**: RecepcionObserver (Compras), RecepcionInventoryObserver (Inventario).

### Paso 5: Devolución a Proveedor (DevolucionCompra)

- **Propósito**: Devolver mercancía al proveedor contra una OC previamente recibida. Descuenta stock físico y libera saldo del PO para futuras recepciones.
- **Vinculación**: Requiere una OrdenCompra en estado Recibida o Emitida.
- **Estados**:

| Estado | Descripción | Color | Transiciones |
|--------|-------------|-------|-------------|
| Borrador | Creada, pendiente de envío | gray | → Confirmada |
| Confirmada | Stock descontado, PO liberado | success | (terminal) |

> El estado Emitida está definido en el enum como reservado para uso futuro (flujo Borrador → Emitida → Confirmada).

- **Items**: Se seleccionan lotes de inventario (Lote) con stock disponible (cantidad_disponible > 0) o lotes rechazados (EstadoLote::Rechazado) en zona de merma.
- **Confirmación** (DevolverMercanciaProveedor, UC-05):
  1. Lotes activos: descuenta cantidad_disponible, marca como Agotado si llega a 0.
  2. Lotes rechazados: no descuenta stock (ya está en merma), solo registra salida.
  3. Crea MovimientoStock tipo DEVOLUCION_PROVEEDOR (origen = ubicación del lote, destino = null).
  4. Ajusta RecepcionItem.cantidad_recibida hacia abajo, liberando saldo del PO.
  5. Actualiza la OC a DevueltaParcialmente o DevueltaTotalmente.
  6. Envía notificación vía NotificadorCompras::devolucionConfirmada().
- **Casos de Uso**: DevolverMercanciaProveedor, GenerarCodigoDevolucion.

---

## 3. Integración con Inventario (Lotes y Movimientos)

### 3.1 Recepción → Inventario

Cuando una recepción cambia a un estado "activo" (`Completa`, `Parcial`, `ConDiscrepancia`, `EnCuarentena`), el `RecepcionInventoryObserver` ejecuta UC-01 (`RegistrarEntradaRecepcion`):

1. Por cada `RecepcionItem`, se crea **uno o dos** `Lote`(s):
   - `Completa` / `Parcial`: un lote `Disponible`.
   - `ConDiscrepancia`: dos sublotes (DISP = disponible, CUAR = cuarentena).
   - `EnCuarentena`: un lote `Cuarentena`.
2. Se registra un `MovimientoStock` tipo `MOV_ENTRADA` con `documento_tipo = 'recepcion_item'`.
3. `PutawayPolicy` asigna la ubicación (primer almacén activo con `tipo = 'almacen'`).

### 3.2 Devolución → Inventario

Al confirmar una devolución (`DevolverMercanciaProveedor`, UC-05):

1. Lote activo: decrementa `cantidad_disponible`, opcionalmente marca `Agotado`.
2. Lote rechazado: solo registra movimiento (no hay stock que deducir).
3. Crea `MovimientoStock` tipo `DEVOLUCION_PROVEEDOR`, `ubicacion_origen_id` = lote.ubicacion, `ubicacion_destino_id` = null.
4. Reduce `RecepcionItem.cantidad_recibida` para liberar saldo del PO.

### 3.3 Consumo FEFO (UC-03)

`ConsumirStock` usa `FEFOStrategy` para seleccionar lotes por fecha de vencimiento más próxima (First-Expiry-First-Out), decrementando `cantidad_disponible` y registrando `MovimientoStock` tipo `MOV_SALIDA`.

### 3.4 Caducidades (UC-04)

Programación diaria (`routes/console.php`, 06:00): `VerificarCaducidades` escanea lotes próximos a vencer, marca como `Vencido` los vencidos, y envía notificación `CaducidadProxima`.

---

## 4. Observers

| Observer | Modelo | Evento | Acción |
|----------|--------|--------|--------|
| `OrdenCompraObserver` | `OrdenCompra` | `created` | Marca cotización como `Aceptada`, demás como `Rechazadas` |
| `RecepcionObserver` | `RecepcionCompra` | `updated(estado)` | Activo --> ejecuta `VerificarEstadoOrdenCompra`. Rechazada --> OC vuelve a `Emitida` |
| `RecepcionInventoryObserver` | `RecepcionCompra` | `updated(estado)` | Activo --> ejecuta `RegistrarEntradaRecepcion` (UC-01) |

Todos registrados en `AppServiceProvider::boot()`.

---

## 5. Estados de Orden de Compra (Completos)

| Valor | Estado | Descripción |
|:----:|--------|-------------|
| 1 | `Borrador` | Editable, sin enviar |
| 2 | `Emitida` | Enviada al proveedor, pendiente de recepción |
| 3 | `EnTránsito` | Mercancía en camino (opcional) |
| 4 | `Recibida` | Ocurre vía `VerificarEstadoOrdenCompra` cuando suma recibido >= suma ordenado |
| 5 | `Cancelada` | Anulada sin recepciones |
| 6 | `DevueltaParcialmente` | Ocurre vía `DevolverMercanciaProveedor` cuando queda saldo > 0 |
| 7 | `DevueltaTotalmente` | Ocurre vía `DevolverMercanciaProveedor` cuando saldo <= 0 |

---

## 6. Reglas de Integridad y Seguridad

1. **Bloqueo de Edición**: OC deja de ser editable al pasar a `Emitida`.
2. **Protección de Eliminación**: OC solo se elimina en `Borrador`. Emitidas solo se cancelan.
3. **No Duplicidad**: No se puede cotizar una solicitud que ya tiene OC vinculada.
4. **Recepción Parcial**: Múltiples recepciones hasta completar la cantidad pedida.
5. **Autorización Estricta (Shield)**: Acciones críticas protegidas por permisos PascalCase.
6. **Devolución**: Solo procede si la OC está en estado `Recibida`.
7. **Tres capas de protección**: Frontend (maxValue), Backend (mutateFormDataBeforeCreate), DB (PostgreSQL trigger).

> [!IMPORTANT]
> Consulta la [Matriz de Acciones y Permisos](./seguridad/MATRIZ_ACCIONES.md) para el detalle de permisos requeridos.

---

## 7. Automatización de Formularios (UX)

- **Reactividad en Cascada**: Al cambiar de documento origen, se limpian los ítems anteriores.
- **Carga de Proveedores**: Al seleccionar cotización, se auto-selecciona el proveedor.
- **Cálculo de Totales**: Subtotales, IVA y totales recalculados en tiempo real.
- **Lote Selector (Devoluciones)**: Al seleccionar un lote, auto-rellena `producto_id`, `producto_variante_id`, `unidad_medida_id`, `recepcion_item_id` en campos ocultos.
- **Recepcion Items**: Campos `lote_proveedor` y `fecha_vencimiento` en el Repeater de items.
- **Redirección**: `CreateRecepcion` redirige a `view` (no index) para que el usuario pueda cambiar el estado inmediatamente.

---

## 8. Reportes y Auditoría de Documentos

Motor basado en **Spatie PDF (Browsershot)** para formalizar las operaciones.

### Ubicación de Componentes:
- **Controlador Maestro**: `App\Http\Controllers\Compras\CompraReportController`
- **Plantillas (Blade)**: `resources/views/reports/compras/`
- **Rutas**: Prefijo `admin/compras/reportes/`, middleware `can:Compras:Imprimir*`.

### Reportes Disponibles (Serie HTB-COM):

| Reporte | Permiso (Shield) | Descripción |
| :--- | :--- | :--- |
| **Solicitud** (HTB-COM-001) | `Compras:ImprimirSolicitud` | Detalle de artículos y justificación |
| **Cotización** (HTB-COM-002) | `Compras:ImprimirCotizacion` | Oferta formal con precios y vigencia |
| **Orden de Compra** (HTB-COM-003) | `Compras:ImprimirOrdenCompra` | Contrato legal con totales e impuestos |
| **Recepción** (HTB-COM-004) | `Compras:ImprimirRecepcion` | Comprobante de ingreso a almacén |
| **Resumen Departamentos** (HTB-COM-005) | `Compras:ImprimirReportesCompras` | Conteo y gasto por departamento |
| **Comparativa** (HTB-COM-006) | `Compras:ImprimirComparativa` | Cuadro técnico de precios y plazos |
| **Devolución** (HTB-COM-007) | `Compras:ImprimirDevolucion` | Detalle de items devueltos y motivo |

### Sistema de Auditoría:
Cada descarga de PDF registra en `report_audits`: usuario, fecha y documento.

---

## 9. Motor de Comparativa y Algoritmo TCO

El sistema aplica un modelo de **Costo Total de Propiedad (TCO)** para recomendar la mejor opción.

### Fórmula del TCO

`TCO = Precio_Bruto + Penalización_Tiempo + Costo_Administrativo`

Donde:
1. **Precio_Bruto**: Suma de productos e impuestos.
2. **Penalización_Tiempo**: `(Precio_Bruto * 0.005) * Días_de_Entrega`.
3. **Costo_Administrativo**: `$25.00` fijos por proveedor.

### Estrategias

| Estrategia | Cuándo aplica | Beneficio |
|:---|:---|:---|
| **Proveedor Único (Urgencia)** | Entrega <= 2 días, sobrecosto < 12% | Velocidad Crítica |
| **Proveedor Único (Eficiencia)** | Centralizar es solo 3% más caro | Simplicidad |
| **Compra Dividida (Ahorro)** | Ahorro financiero compensa costos logísticos | Máximo Ahorro |

---

## 10. Historial de Estados (`HasStatusHistory`)

- **Archivo:** `app/Traits/HasStatusHistory.php`
- **Propósito:** Registra cada cambio de estado en `compras_historial`.

| Evento | Acción |
|--------|--------|
| `updated` (estado modificado) | Crea registro: `estado_anterior`, `estado_nuevo`, `usuario_id`, comentario |
| `created` | Crea registro inicial con `estado_anterior = null` |

> [!NOTE]
> El trait usa `is_object()` antes de `method_exists('label')` para evitar TypeError con valores enteros crudos desde BD.

---

## 11. Arquitectura de Casos de Uso

Toda la lógica de negocio organizada en Use Cases con clasificación `Queries` (lectura) / `Mutations` (escritura):

### Solicitudes
- `AprobarSolicitud`, `RechazarSolicitud`, `CancelarSolicitud`, `GenerarCodigoSolicitud` (Mutations)
- `ObtenerSolicitudConItems`, `ObtenerSolicitudParaComparativa`, `ObtenerSolicitudesParaComparar` (Queries)

### Cotizaciones
- `ElegirCotizacionGanadora`, `SeleccionarItemGanador`, `ActualizarEstadosCotizacionesSolicitud` (Mutations)
- `AnalizarScoringCotizaciones`, `ObtenerCotizacionConItemsProveedor`, `ObtenerCotizacionesPorSolicitud`, `ObtenerRecomendacionLogistica` (Queries)

### Ordenes de Compra
- `GenerarOrdenDesdeCotizacion`, `GenerarOrdenesDesdeComparativa`, `EmitirOrdenCompra`, `CancelarOrdenCompra`, `GenerarCodigoOrdenCompra` (Mutations)
- `ObtenerOrdenCompraConItems`, `VerificarEstadoOrdenCompra` (Queries)

### Recepciones
- `GenerarCodigoRecepcion`, `GestionarTransicionRecepcion` (Mutations)

### Devoluciones
- `DevolverMercanciaProveedor`, `GenerarCodigoDevolucion` (Mutations)

### Proveedores
- `CrearProveedor`, `ActualizarProveedor`, `GenerarCodigoProveedor` (Mutations)

> [!TIP]
> Al invocar un caso de uso desde un componente Filament, use `app(UseCase::class)->execute(...)`.

---

*Hotel Bugambilias*
