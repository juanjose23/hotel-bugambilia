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

---

## 4. Automatización de Formularios (UX)

El sistema implementa **Reactividad en Cascada**:
- **Limpieza Dinámica**: Al cambiar de documento de origen (Solicitud/Cotización), el formulario limpia los ítems anteriores antes de cargar los nuevos para evitar contaminación de datos.
- **Carga de Proveedores**: Al seleccionar una cotización, el sistema detecta y selecciona automáticamente al proveedor asociado.
- **Cálculo de Totales**: Todos los subtotales, IVA y Totales se recalculan en tiempo real al modificar cualquier cantidad o precio.

---

## 5. Reportes y Auditoría de Documentos

El sistema cuenta con un motor de reportes PDF de alta fidelidad para formalizar las operaciones.

### Ubicación de Componentes:
- **Controlador Maestro**: `App\Http\Controllers\Compras\CompraReportController`
- **Plantillas (Blade)**: `resources/views/reportes/compras/`
- **Rutas**: Prefijo `admin/compras/reportes/`

### Reportes Disponibles:
| Reporte | Nombre de Ruta | Descripción |
| :--- | :--- | :--- |
| **Solicitud** | `reporte.solicitud` | Detalle de artículos pedidos y justificación. |
| **Cotización** | `reporte.cotizacion` | Oferta formal del proveedor con precios y vigencia. |
| **Orden de Compra** | `reporte.orden-compra` | Contrato legal de compra con totales e impuestos. |
| **Recepción** | `reporte.recepcion` | Comprobante de ingreso a almacén. |

### Sistema de Auditoría:
Cada vez que un usuario genera o descarga un PDF, el sistema registra la acción en la tabla `report_audits`, capturando:
- Usuario que generó el reporte.
- Fecha y hora exacta.
- Tipo de documento e ID referenciado.

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
> El trait verifica si el valor de estado es un objecto (`is_object()`) antes de llamar a `method_exists('label')` para evitar TypeError cuando el valor original desde BD es un entero crudo.

---

*Hotel Bugambilias*
