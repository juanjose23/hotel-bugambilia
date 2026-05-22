# Casos de Uso — Inventario v2.3

Este documento cataloga todos los **Casos de Uso (Interactores)** del módulo de inventario, organizados por submódulo. Cada caso de uso contiene: namespace exacto, firma del método, trigger de ejecución y descripción detallada de su lógica interna.

---

## 📂 Estructura de Directorios (v2.3)

```
app/UseCases/Inventario/
├── Espacios/                           ← Activos Fijos en Habitaciones y Espacios
│   ├── AsignarActivoAEspacio.php
│   ├── RetirarActivoDeEspacio.php
│   └── ConsultarInventarioDeHabitacion.php
│
├── Dotacion/                           ← Consumibles y Preparación de Espacios
│   ├── PrepararEspacio.php
│   ├── ReponerEspacio.php
│   └── RegistrarDevolucion.php
│
├── Recepciones/
│   └── Mutations/
│       ├── RegistrarEntradaRecepcion.php   ← UC-01: Ingreso de mercancía
│       └── ConvertirItemAUbicaciones.php   ← P2L: Jerarquía física
│
├── Lotes/
│   └── Mutations/
│       ├── LiberarLotesCuarentena.php      ← UC-02: Liberar cuarentena
│       ├── RechazarLotesCuarentena.php     ← UC-02b: Rechazar a merma
│       └── VerificarCaducidades.php        ← UC-04: Barrido diario de vencimientos
│
├── Movimientos/
│   └── Mutations/
│       ├── ConsumirStock.php               ← UC-03: Consumo FEFO de bodega
│       └── TrasladarEntreBodegas.php       ← Traslado entre almacenes físicos
│
├── InventarioFisico/
│   └── Mutations/
│       └── ProcesarInventarioFisico.php    ← Ajustes por auditoría física
│
├── Services/
│   └── PutawayPolicy.php                  ← Servicio: Sugerir bodega destino
│
└── Queries/
    ├── ObtenerStockPorProducto.php
    └── ObtenerMovimientosInventario.php
```

---

## 🏠 Submódulo 1: Espacios (Activos Fijos)

Gestiona los **activos fijos** asignados permanentemente a habitaciones y espacios del hotel. Estos no se consumen ni se rastrean en bodegas; viven en `hab_inventario_fijo`.

---

### `AsignarActivoAEspacio`

Asigna o actualiza un activo fijo en una habitación o espacio. Si ya existe una asignación del mismo producto+variante en ese espacio, actualiza la cantidad y el estado. Si no existe, la crea.

**Namespace**: `App\UseCases\Inventario\Espacios`
**Trigger**: RelationManager `InventarioFijoRelationManager` en Filament (acción Crear/Editar)

```php
public function execute(
    string $espacioTipo,  // 'habitacion' | 'espacio'
    int    $espacioId,    // ID de la habitación o espacio
    int    $productoId,   // Activo a asignar
    ?int   $varianteId,   // Variante específica (o null)
    float  $cantidad,     // Cantidad de unidades
    int    $usuarioId     // Usuario que registra
): void
```

**Lógica**:
1. Valida que `$espacioTipo` sea `'habitacion'` o `'espacio'`
2. Valida que `$cantidad > 0`
3. Ejecuta `InventarioFijo::updateOrCreate()` con clave `(espacio_tipo, espacio_id, producto_id, producto_variante_id)` y establece `estado = 'operativo'`

---

### `RetirarActivoDeEspacio`

Marca un activo fijo como dado de baja (soft delete) de su espacio. El registro permanece en la base de datos para historial de auditoría.

**Namespace**: `App\UseCases\Inventario\Espacios`
**Trigger**: Acción "Retirar" en RelationManager de Filament

```php
public function execute(
    int    $inventarioFijoId,  // ID del registro en hab_inventario_fijo
    int    $usuarioId          // Usuario que autoriza el retiro
): void
```

**Lógica**:
1. Encuentra el `InventarioFijo` por ID
2. Marca `estado = 'dado_de_baja'`
3. Aplica `softDelete()` para conservar historial

---

### `ConsultarInventarioDeHabitacion`

Retorna la colección completa de activos fijos asignados a una habitación específica.

**Namespace**: `App\UseCases\Inventario\Espacios`
**Trigger**: Vista de detalle de habitación en Filament

```php
public function execute(
    string $espacioTipo,  // 'habitacion' | 'espacio'
    int    $espacioId     // ID del espacio
): Collection
```

---

## 🧴 Submódulo 2: Dotación (Consumibles)

Gestiona el ciclo de preparación y reposición de consumibles en habitaciones y espacios, consumiendo stock de las bodegas físicas.

---

### `PrepararEspacio`

Aplica una plantilla de dotación a un espacio (habitación o espacio común), consumiendo los ítems requeridos de la bodega física asignada usando la estrategia **FEFO**.

**Namespace**: `App\UseCases\Inventario\Dotacion`
**Trigger**: Acción de cabecera "Preparar Habitación" en Filament o evento del módulo de Reservas

```php
public function execute(
    string $espacioTipo,   // 'habitacion' | 'espacio'
    int    $espacioId,     // ID del espacio a preparar
    int    $plantillaId,   // ID de la plantilla de dotación a aplicar
    int    $ubicacionId,   // ID de la bodega física que surte los consumibles
    ?int   $usuarioId = null,
    ?string $notas = null
): void
```

**Lógica**:
1. Valida que `$espacioTipo` sea `'habitacion'` o `'espacio'`
2. Carga `PlantillaDotacion` con sus `items`
3. Valida que la plantilla esté activa (`$plantilla->activa === true`)
4. Para cada ítem de la plantilla con `cantidad > 0`:
   - Llama a `ConsumirStock::execute()` con `tipoMovimiento = 'SALIDA_DOTACION'`
   - Si no hay stock suficiente en la bodega, lanza `RuntimeException`
5. Todo dentro de una transacción de base de datos

---

### `ReponerEspacio`

Ejecuta la reposición diaria de amenidades, procesando únicamente los ítems de la plantilla que tienen `es_reposicion_diaria = true`.

**Namespace**: `App\UseCases\Inventario\Dotacion`
**Trigger**: Tarea programada diaria o acción manual de camarera en Filament

```php
public function execute(
    string $espacioTipo,
    int    $espacioId,
    int    $plantillaId,
    int    $ubicacionId,
    ?int   $usuarioId = null
): void
```

**Lógica**:
1. Igual que `PrepararEspacio` pero solo procesa ítems donde `es_reposicion_diaria = true`
2. Registra movimientos con `tipoMovimiento = 'REPOSICION_DIARIA'`

---

### `RegistrarDevolucion`

Registra el retorno de consumibles no utilizados de vuelta a una bodega física, incrementando el stock disponible.

**Namespace**: `App\UseCases\Inventario\Dotacion`
**Trigger**: Acción de camarera al finalizar una estadía o al registrar sobrantes

```php
public function execute(
    int    $productoId,
    float  $cantidad,
    int    $ubicacionId,          // Bodega destino de la devolución
    ?int   $productoVarianteId = null,
    ?int   $loteId = null,        // Si se conoce el lote específico
    ?int   $usuarioId = null,
    ?string $referencia = null,
    ?string $notas = null
): void
```

**Lógica**:
1. Valida `$cantidad > 0`
2. **Deducción de Lote**: Si no se provee `$loteId`:
   - Busca en `inv_stock` un registro existente del producto en esa bodega con `lote_id` no nulo
   - Si no encuentra, busca el lote más reciente del producto (`Lote::latest()->first()`)
3. **Incremento del Lote Global**: Si se resolvió un lote, incrementa `cantidad_disponible`. Si el lote estaba `Agotado`, lo reactiva a `Disponible`
4. **Actualización de Stock**: Busca en `inv_stock` la combinación exacta `(producto_id, variante_id, lote_id, ubicacion_id)`. Si existe, suma la cantidad. Si no, crea un nuevo registro
5. Registra movimiento `DEVOLUCION_BODEGA` en `inv_movimientos`

---

## 📥 Submódulo 3: Recepciones

---

### `RegistrarEntradaRecepcion` (UC-01)

Registra la entrada física de mercancía al inventario al completarse una Recepción de Compra. Crea los lotes, el stock inicial y los movimientos de entrada.

**Namespace**: `App\UseCases\Inventario\Recepciones\Mutations`
**Trigger**: Observer `RecepcionInventoryObserver` → método `updated()` cuando una `RecepcionCompra` cambia a estado `Completa`, `Parcial`, `ConDiscrepancia` o `EnCuarentena`

```php
public function execute(
    string $nuevoEstado,              // 'Completa' | 'Parcial' | 'EnCuarentena' | 'ConDiscrepancia'
    array  $items,                    // Array de ítems de recepción con sus datos
    ?int   $proveedorId = null,
    ?int   $creadoPorId = null,
    array  $decisionesDiscrepancia = [] // Para estado 'ConDiscrepancia'
): void
```

**Estructura de cada `$item`**:
```php
[
    'id'                    => int,         // ID del RecepcionItem
    'producto_id'           => int,
    'producto_variante_id'  => int|null,
    'cantidad_recibida'     => float,
    'cantidad_rechazada'    => float,
    'lote_proveedor'        => string|null, // Código de lote del proveedor
    'fecha_vencimiento'     => string|null, // Formato 'Y-m-d'
    'ubicacion_id'          => int|null,    // Bodega destino específica
]
```

**Lógica por estado**:

| Estado | Resultado |
| :--- | :--- |
| `Completa` | Un lote `Disponible` por ítem |
| `Parcial` | Un lote `Disponible` por ítem |
| `EnCuarentena` | Un lote `Cuarentena` por ítem |
| `ConDiscrepancia` | Dos lotes por ítem: uno `Disponible` + uno `Cuarentena` (sufijos `-DISP`/`-CUAR`) |

**Método privado `crearLote()`**:
1. **Resolución de Ubicación**: Intenta usar `$item['ubicacion_id']`. Si no está disponible o no existe, usa `PutawayPolicy::sugerirUbicacion()`
2. Crea el registro en `inv_lotes`
3. Crea el registro en `inv_stock` (existencia inicial en la bodega)
4. Registra movimiento `MOV_ENTRADA` en `inv_movimientos`

---

### `ConvertirItemAUbicaciones` (P2L)

Convierte un ítem de recepción (ej: un estante comprado) en una jerarquía recursiva de ubicaciones físicas en la base de datos.

**Namespace**: `App\UseCases\Inventario\Recepciones\Mutations`
**Trigger**: Acción de cabecera en panel Filament de Recepciones

```php
public function execute(array $data): array
// $data incluye: recepcion_item_id, parent_id, nombre_prefijo,
//                cantidad_a_convertir, niveles_por_unidad, posiciones_por_nivel
```

**Lógica**:
1. Lee el ítem de recepción
2. Por cada unidad a convertir, genera recursivamente:
   - Registro padre (ej: `Estante 1`)
   - `N` niveles hijos (ej: `Nivel 1`, `Nivel 2`)
   - `M` posiciones por cada nivel (ej: `Posición 1`, `Posición 2`, `Posición 3`)
3. Todo con bloqueo pesimista para creaciones concurrentes seguras

---

## 📦 Submódulo 4: Lotes

---

### `LiberarLotesCuarentena` (UC-02)

Libera lotes retenidos en cuarentena haciéndolos disponibles para consumo mediante FEFO.

**Namespace**: `App\UseCases\Inventario\Lotes\Mutations`
**Trigger**: Acción de fila o masiva en `LoteTable.php` de Filament

```php
public function execute(
    array $loteIds,         // IDs de los lotes a liberar
    ?int  $usuarioId = null
): array  // Retorna [{lote_id, codigo_lote}] de los liberados con éxito
```

**Lógica por cada lote**:
1. Verifica que el estado sea `Cuarentena`. Si no, agrega al array de errores y continúa
2. Llama a `PutawayPolicy::sugerirUbicacion()` para obtener la bodega de destino
3. Actualiza el lote: `estado = Disponible`, `ubicacion_id = nuevaUbicacion->id`
4. **Sincroniza `inv_stock`**:
   - Si existe un registro en `inv_stock` para la ubicación antigua, lo mueve a la nueva (o lo fusiona si ya hay existencia en el destino)
   - Si no existe ningún registro, crea uno nuevo en la bodega destino
5. Registra movimiento `MOV_TRANSFERENCIA` con origen (cuarentena) y destino (almacén)
6. Notifica via `NotificadorInventario::loteLiberado($lote)`

---

### `RechazarLotesCuarentena`

Marca lotes como rechazados por falla de inspección de calidad, vaciando su existencia y enviándolos a la Zona de Merma.

**Namespace**: `App\UseCases\Inventario\Lotes\Mutations`
**Trigger**: Acción de fila o masiva en `LoteTable.php` de Filament

```php
public function execute(
    array  $loteIds,
    string $motivo,          // Motivo del rechazo (obligatorio)
    ?int   $usuarioId = null
): array
```

**Lógica por cada lote**:
1. Valida que el estado sea `Cuarentena`. Si no, lanza `InvalidArgumentException`
2. Busca la ubicación con `tipo = 'merma'` activa. Si no existe, lanza `RuntimeException`
3. Establece `cantidad_disponible = 0`, `estado = Rechazado`, `ubicacion_id = zonaMerma->id`
4. Registra movimiento `MOV_AJUSTE` con notas = motivo del rechazo
5. Notifica via `NotificadorInventario::loteRechazado($lote, $motivo)`

---

### `VerificarCaducidades` (UC-04)

Proceso programado que audita diariamente el inventario, marca lotes vencidos y notifica sobre proximas caducidades.

**Namespace**: `App\UseCases\Inventario\Lotes\Mutations`
**Trigger**: Tarea programada en `routes/console.php` a las **06:00** diariamente

```php
public function execute(): void
```

**Lógica**:
1. **Vencidos**: Encuentra lotes con `estado = Disponible` y `fecha_vencimiento <= hoy`
   - Establece `cantidad_disponible = 0`, `estado = Vencido`
   - Busca zona de merma y reubica el lote
   - Registra movimiento `BAJA_CADUCIDAD`
2. **Próximos a vencer**: Encuentra lotes con `fecha_vencimiento` entre hoy y +30 días
   - Envía notificaciones por correo `CaducidadProxima` a los responsables de compras y almacén

---

## 🔄 Submódulo 5: Movimientos

---

### `ConsumirStock` (UC-03)

Consume stock de consumibles desde una bodega física específica usando la estrategia **FEFO** (First-Expiry-First-Out). Es el caso de uso más crítico del módulo.

**Namespace**: `App\UseCases\Inventario\Movimientos\Mutations`
**Trigger**: Llamado internamente por `PrepararEspacio`, `ReponerEspacio`

```php
public function execute(
    int     $productoId,
    float   $cantidadRequerida,
    int     $ubicacionId,                  // Bodega física de donde consumir
    string  $tipoMovimiento = 'CONSUMO',   // Ver tabla de tipos en BASE_DATOS.md
    ?int    $productoVarianteId = null,
    ?int    $documentoId = null,
    ?string $documentoTipo = null,
    ?int    $creadoPorId = null,
    ?string $referencia = null,
    ?string $notas = null
): array  // Retorna [{stock_id, lote_id, cantidad}] por lote consumido
```

**Algoritmo FEFO**:
```
1. Consultar inv_stock WHERE:
   - producto_id = $productoId
   - ubicacion_id = $ubicacionId
   - cantidad > 0
   - (lote_id IS NULL) OR (
       lote.estado = Disponible
       AND (lote.fecha_vencimiento IS NULL OR lote.fecha_vencimiento >= hoy)
     )

2. Ordenar por: fecha_vencimiento ASC, NULLs al final
   (fecha_vencimiento IS NULL → trata como '9999-12-31')

3. Si suma_total_disponible < cantidadRequerida → RuntimeException('Stock insuficiente')

4. Para cada registro de stock ordenado:
   a. aConsumir = min(stock.cantidad, restante)
   b. stock.cantidad -= aConsumir
   c. Si stock.cantidad <= 0 → DELETE el registro (limpia filas vacías)
   d. Si no es TRASLADO y tiene lote:
      - lote.cantidad_disponible -= aConsumir
      - Si lote.cantidad_disponible <= 0 → estado = Agotado
   e. Registrar MovimientoStock (cantidad negativa para salidas)
   f. restante -= aConsumir
```

**Nota sobre traslados**: Cuando `tipoMovimiento` es `'TRASLADO'` o `'MOV_TRANSFERENCIA'`, el algoritmo NO decrementa `lote.cantidad_disponible` porque el stock no sale del sistema, solo cambia de bodega.

---

### `TrasladarEntreBodegas`

Mueve una cantidad específica de un lote de una bodega a otra. Requiere conocer el lote exacto a trasladar.

**Namespace**: `App\UseCases\Inventario\Movimientos\Mutations`
**Trigger**: Acción manual en panel Filament o llamado programático

```php
public function execute(
    int     $productoId,
    int     $loteId,             // Lote específico a trasladar
    float   $cantidad,
    int     $origenId,           // Bodega origen
    int     $destinoId,          // Bodega destino
    ?int    $productoVarianteId = null,
    ?int    $creadoPorId = null,
    ?string $referencia = null,
    ?string $notas = null
): void
```

**Lógica**:
1. Valida `$cantidad > 0` y `$origenId !== $destinoId`
2. Obtiene `Stock` del origen. Si no existe o tiene cantidad insuficiente → `RuntimeException`
3. Descuenta del origen (elimina el registro si queda en cero)
4. Incrementa en el destino (usa `Stock::updateOrCreate()` con la clave única)
5. Registra movimiento `TRASLADO` en `inv_movimientos`

---

## 📝 Submódulo 6: Inventario Físico

---

### `ProcesarInventarioFisico`

Procesa y bloquea una toma de inventario físico, aplicando ajustes de stock por las discrepancias detectadas entre el conteo real y la base de datos lógica.

**Namespace**: `App\UseCases\Inventario\InventarioFisico\Mutations`
**Trigger**: Acción "Procesar Conteo" en Filament

```php
public function execute(
    int $inventarioFisicoId,
    int $creadoPorId
): void
```

**Lógica**:
1. Carga `InventarioFisico`. Valida que estado sea `'borrador'`
2. Lee `datos_hoja` (JSON): `[{lote_id, cantidad_contada}]`
3. Para cada lote en el JSON:
   - Compara `cantidad_contada` vs `lote.cantidad_disponible`
   - Si **excedente** (contado > lógico): ajusta lote, registra `AJUSTE_ENTRADA` (positivo)
   - Si **faltante** (contado < lógico): ajusta lote, registra `AJUSTE_SALIDA` (negativo)
   - Si `cantidad_contada == 0`: marca lote como `Agotado`
4. Actualiza `inv_stock` de la ubicación correspondiente para reflejar los ajustes
5. Marca el inventario físico como `procesado` (impide modificaciones posteriores)

---

## ⚙️ Servicio: `PutawayPolicy`

Servicio estático que determina automáticamente la bodega de destino para nuevos lotes cuando no se especifica una ubicación explícita.

**Namespace**: `App\UseCases\Inventario\Services`

```php
public static function sugerirUbicacion(): Ubicacion
```

**Algoritmo**:
```php
// Consulta con cache estático para evitar múltiples queries en la misma request:
$ubicacion = Ubicacion::whereIn('tipo', ['zona', 'almacen'])
    ->where('estado', 1)
    ->first();

if (!$ubicacion) {
    throw new RuntimeException(
        'No hay ubicaciones activas disponibles para asignar inventario.
         Crea al menos una ubicación de tipo "almacen" en Catálogos > Ubicaciones.'
    );
}
```

> [!IMPORTANT]
> La `PutawayPolicy` tiene un **cache estático** (`private static ?Ubicacion $cache`). En tests, se debe resetear entre pruebas para evitar contaminación:
> ```php
> $ref = new ReflectionClass(PutawayPolicy::class);
> $ref->setStaticPropertyValue('cache', null);
> ```

---

## 🗑️ Casos de Uso Eliminados (v1 — NO USAR)

Los siguientes casos de uso existieron en el v1 y fueron eliminados o reemplazados:

| Caso de Uso v1 | Reemplazado por |
| :--- | :--- |
| `DistribuirStock` | `TrasladarEntreBodegas` (usa bodegas reales, no ámbitos polimórficos) |
| `ConsumirStockUbicacion` | `ConsumirStock` (firma actualizada con `ubicacionId` explícito) |
| `ReservarStockUbicacion` | Eliminado. El flujo de dotación por plantilla lo hace innecesario |
| `GenerarReposiciones` | `GenerarReposicionesBodega` en v2.2, luego eliminado en v2.3 |
| `GenerarReposicionesBodega` | Eliminado (v2.2→v2.3). Módulo de reposiciones removido |
| `ProcesarReposicion` | Eliminado (v2.2→v2.3). Módulo de reposiciones removido |
