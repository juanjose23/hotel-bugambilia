# Lógica de Negocio y Funcionalidades — Inventario v2.3

Este documento detalla los algoritmos críticos, reglas de negocio y flujos de integración del módulo de inventario. Complementa el catálogo de casos de uso con explicaciones conceptuales y técnicas de cada proceso clave.

---

## 🧠 1. La Distinción Fundamental del v2.2

El cambio más importante del rediseño es la **separación conceptual de dos tipos de inventario** que el v1 mezclaba incorrectamente:

```
┌───────────────────────────────────────┬───────────────────────────────────────┐
│         ACTIVO FIJO                   │         CONSUMIBLE                    │
├───────────────────────────────────────┼───────────────────────────────────────┤
│ Tabla: hab_inventario_fijo            │ Tablas: inv_lotes + inv_stock         │
│ Ejemplo: Camas, TV, A/C               │ Ejemplo: Champú, Agua, Toallas        │
│ Se asigna permanentemente             │ Se consume en cada estadía            │
│ a Habitación o Área                   │ y se repone desde la bodega           │
│ NO sale del hotel                     │ FEFO determina qué lote usar primero  │
│ Estado: operativo / en_reparacion     │ Estado: Disponible / Cuarentena       │
│ UC: AsignarActivoAEspacio             │ UC: PrepararEspacio / ConsumirStock   │
└───────────────────────────────────────┴───────────────────────────────────────┘
```

> **Regla de Oro**: Si algo tiene número de serie, se deprecia o se registra en activos fijos → va a `hab_inventario_fijo`. Si algo se abre, se usa y se tira → va a `inv_stock`.

---

## 📅 2. Estrategia FEFO (First-Expiry-First-Out)

Para prevenir mermas financieras por caducidad de insumos, el hotel usa **FEFO obligatoriamente** en todos los consumos de stock.

### Reglas de Selección de Lotes

Cuando `ConsumirStock::execute()` recibe una solicitud:

1. **Filtro de estado estricto**: Solo se consideran lotes con `estado = Disponible`
2. **Filtro de vencimiento en tiempo real**: Se excluyen lotes con `fecha_vencimiento <= hoy`, incluso si no han sido marcados por `VerificarCaducidades` todavía
3. **Ordenación de prioridades**:

```
┌──────────────────────────────────────────────────────┐
│                  ORDEN DE CONSUMO FEFO               │
├─────┬──────────────────────────────────────────────┤
│  1  │ Lotes con fecha_vencimiento más próxima       │
│  2  │ Lotes sin fecha_vencimiento (al final)        │
└─────┴──────────────────────────────────────────────┘
```

### Query FEFO en ConsumirStock

```php
$stocksOrdenados = Stock::where('producto_id', $productoId)
    ->where('ubicacion_id', $ubicacionId)
    ->where('cantidad', '>', 0)
    ->whereHas('lote', function ($q) {
        $q->where('estado', EstadoLote::Disponible)
          ->where(function ($inner) {
              $inner->whereNull('fecha_vencimiento')
                    ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
          });
    })
    ->with('lote')
    ->get()
    ->sortBy(fn ($stock) =>
        $stock->lote?->fecha_vencimiento?->format('Y-m-d') ?? '9999-12-31'
    );
```

> [!NOTE]
> La ordenación por FEFO se realiza **en memoria** después de la consulta (no con `orderBy`), para evitar problemas de compatibilidad con JOINs complejos y para permitir lógica de NULL handling más explícita.

### Consumo Multi-Lote

Un consumo puede abarcar varios lotes si ninguno tiene suficiente cantidad por sí solo:

```
Ejemplo: Se requieren 5 unidades de Champú

Lote A (vence 2026-06-01): 2 unidades disponibles  → Consume 2 → Agotado
Lote B (vence 2026-08-15): 2 unidades disponibles  → Consume 2 → Agotado
Lote C (sin vencimiento):  4 unidades disponibles  → Consume 1 → Quedan 3

Resultado: 3 registros en inv_movimientos, 1 por lote
```

---

## 📥 3. Política de Ubicación Sugerida (PutawayPolicy)

Cuando llega mercancía y no se especifica una bodega destino explícita, `PutawayPolicy::sugerirUbicacion()` determina automáticamente dónde colocarla.

### Algoritmo de Selección

```
CRITERIOS (en orden de prioridad):
  1. Tipo de ubicación: 'zona' o 'almacen'
  2. Estado activo: estado = 1

RESULTADO: Primera ubicación que cumpla ambos criterios
```

> [!IMPORTANT]
> **El Almacén General debe ser la primera ubicación de tipo `almacen` registrada.** Si hay múltiples almacenes activos, `PutawayPolicy` elegirá el de menor `id`. Organiza las ubicaciones con este comportamiento en mente.

### Cache Estático

`PutawayPolicy` usa `private static ?Ubicacion $cache` para evitar múltiples consultas durante el procesamiento de una recepción con muchos ítems. El cache se mantiene durante toda la request (ciclo de vida del proceso PHP).

**⚠️ Problema potencial en tests**: El cache persiste entre tests en el mismo proceso. Para evitar contaminación:
```php
// En tearDown() o setUp() del test
$ref = new ReflectionClass(PutawayPolicy::class);
$ref->setStaticPropertyValue('cache', null);
```

---

## 🏷️ 4. Generación de Códigos

### Código de Lote (`inv_lotes.codigo_lote`)

**Fuente A — Código del Proveedor** (preferido cuando existe):
```php
// Si el proveedor envió su propio número de lote:
$codigoBase = $item['lote_proveedor'];
// Ejemplo: 'LT-2026-0042'
```

**Fuente B — Código Generado Automáticamente**:
```php
// Si no hay código del proveedor:
$codigoBase = sprintf('LOTE-%d-%s', $productoId, now()->format('Ymd'));
// Ejemplo: 'LOTE-15-20260520'
```

**Sufijos para Discrepancias**:
```php
// Cuando la recepción es ConDiscrepancia, se crean dos lotes:
$codigoDisponible = $codigoBase . '-DISP';  // Ej: 'LOTE-15-20260520-DISP'
$codigoCuarentena = $codigoBase . '-CUAR';  // Ej: 'LOTE-15-20260520-CUAR'
```

---

### Código de Inventario Físico (`inv_inventarios_fisicos.codigo`)

```php
// Generado sugerido por el formulario Filament (editable por el auditor):
'INV-FIS-' . now()->format('Ymd')
// Ejemplo: 'INV-FIS-20260520'
```

---

## 🔄 5. Flujo de Dotación de Habitaciones

El proceso de preparación de habitaciones conecta los tres submódulos del inventario:

```
1. CONFIGURACIÓN (una sola vez por categoría de habitación):
   Plantilla de Dotación: "Suite Estándar"
   ├── 2x Champú (es_reposicion_diaria = true)
   ├── 2x Acondicionador (es_reposicion_diaria = true)
   ├── 1x Kit de Costura (es_reposicion_diaria = false)
   └── 1x Zapatillas (es_reposicion_diaria = false)

2. CHECK-IN / PREPARACIÓN COMPLETA:
   PrepararEspacio(habitacion, 101, plantilla_id, bodega_piso1)
   → ConsumirStock FEFO × 4 productos
   → 4x MOV: SALIDA_DOTACION en inv_movimientos

3. REPOSICIÓN DIARIA:
   ReponerEspacio(habitacion, 101, plantilla_id, bodega_piso1)
   → ConsumirStock FEFO × 2 productos (solo reposicion_diaria=true)
   → 2x MOV: REPOSICION_DIARIA en inv_movimientos

4. CHECK-OUT / SOBRANTES:
   RegistrarDevolucion(producto=Champú, cantidad=1, bodega_piso1)
   → Lote recuperado, inv_stock incrementado
   → 1x MOV: DEVOLUCION_BODEGA en inv_movimientos
```

---

---

## 🔍 6. Toma Física de Inventario y Ajustes

### Estructura del JSON `datos_hoja`

```json
[
  {
    "lote_id": 42,
    "cantidad_contada": 15.5
  },
  {
    "lote_id": 43,
    "cantidad_contada": 0
  }
]
```

### Lógica de Ajustes

```
Para cada entrada en datos_hoja:

  diferencia = cantidad_contada - lote.cantidad_disponible

  Si diferencia > 0 (EXCEDENTE):
    → lote.cantidad_disponible += diferencia
    → MOV tipo: AJUSTE_ENTRADA (cantidad positiva)

  Si diferencia < 0 (FALTANTE):
    → lote.cantidad_disponible -= abs(diferencia)
    → Si lote.cantidad_disponible = 0 → estado = Agotado
    → MOV tipo: AJUSTE_SALIDA (cantidad negativa)

  Si diferencia = 0 (OK):
    → Sin cambios, sin movimiento

Una vez procesado todo:
  → InventarioFisico.estado = 'procesado'  (inmutable)
```

---

## 🔌 7. Integración con el Módulo de Compras (P2P)

El módulo de inventario se activa automáticamente cuando el módulo de compras cambia el estado de una recepción:

```
RecepcionCompra.estado → 'Completa' | 'Parcial' | 'EnCuarentena' | 'ConDiscrepancia'
                                        ↓
                             RecepcionInventoryObserver::updated()
                                        ↓
                             RegistrarEntradaRecepcion::execute()
                                        ↓
                    inv_lotes (creados) + inv_stock (inicializados) +
                    inv_movimientos (MOV_ENTRADA)
```

### Observer Registrado

```php
// app/Providers/AppServiceProvider.php → boot()
RecepcionCompra::observe(RecepcionInventoryObserver::class);
```

---

## 🌳 8. Jerarquía de Ubicaciones y Bodegas

El módulo de inventario reutiliza la tabla `ubicaciones` del hotel para definir las bodegas físicas. La relación entre tipos de ubicación y el inventario es:

```
ubicaciones.tipo:
  'pais'     → No relacionada con inventario
  'region'   → No relacionada con inventario
  'ciudad'   → No relacionada con inventario
  'hotel'    → No relacionada con inventario
  'zona'     → PutawayPolicy puede sugerir como bodega temporal
  'almacen'  → ✅ Bodega principal (PutawayPolicy prefiere este tipo)
  'estante'  → Subdivisión de almacén (generada por P2L)
  'nivel'    → Subdivisión de estante (generada por P2L)
  'posicion' → Posición específica (generada por P2L)
  'merma'    → Destino de lotes rechazados o vencidos
```

---

## 🧪 9. Estrategia de Testing

Todos los tests del módulo de inventario están en `tests/Feature/Inventario/`.

### Casos Cubiertos (80 tests)

| Caso de Uso | Test File |
| :--- | :--- |
| RegistrarEntradaRecepcion | `RegistrarEntradaRecepcionTest.php` |
| ConsumirStock (FEFO) | `ConsumirStockTest.php` |
| LiberarLotesCuarentena | `LiberarLotesCuarentenaTest.php` |
| RechazarLotesCuarentena | `RechazarLotesCuarentenaTest.php` |
| VerificarCaducidades | `VerificarCaducidadesTest.php` |
| PrepararEspacio | `PrepararEspacioTest.php` |
| RegistrarDevolucion | `RegistrarDevolucionTest.php` |
| Flujo completo integrado | `FlujoInventarioCompletoTest.php` |

### Convenciones de Test

```php
// Siempre usar RefreshDatabase
uses(RefreshDatabase::class);

// Crear ubicación tipo almacen para PutawayPolicy
$almacen = Ubicacion::factory()->create(['tipo' => 'almacen', 'estado' => 1]);

// Limpiar cache de PutawayPolicy entre tests
setUp(function () {
    $ref = new ReflectionClass(PutawayPolicy::class);
    $ref->setStaticPropertyValue('cache', null);
});

// Verificar stock resultante
expect(Stock::where('producto_id', $productoId)->sum('cantidad'))->toBe(10.0);
expect(MovimientoStock::where('tipo', 'MOV_ENTRADA')->count())->toBe(1);
```

---

## ⚡ 10. Consideraciones de Rendimiento

### Bloqueos Transaccionales

Todos los casos de uso que modifican stock usan `DB::transaction()` con **bloqueos pesimistas** (`lockForUpdate()`) para prevenir condiciones de carrera en entornos concurrentes:

```php
// Ejemplo en ConsumirStock:
DB::transaction(function () use ($productoId, $cantidadRequerida) {
    $stocks = Stock::where(...)
        ->lockForUpdate()   // Bloqueo pesimista
        ->get();
    // ...
});
```

### Índices Críticos

| Índice | Tabla | Uso |
| :--- | :--- | :--- |
| `(producto_id, estado)` | `inv_lotes` | Búsqueda de lotes disponibles por producto |
| `(estado, fecha_vencimiento)` | `inv_lotes` | Barrido diario de caducidades (UC-04) |
| `(producto_id, ubicacion_id)` | `inv_stock` | Consulta de stock en bodega específica |
| `(producto_id, created_at)` | `inv_movimientos` | Reportes de rotación e historial |

### Eliminación de Registros en Cero

Un principio de diseño del módulo es que **`inv_stock` no conserva filas con `cantidad = 0`**. Cuando un consumo agota el stock de un lote en una bodega, el registro se **elimina** (`DELETE`). Esto mantiene la tabla compacta y hace que los `SUM()` y `WHERE cantidad > 0` sean más eficientes.
