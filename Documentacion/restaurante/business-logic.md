# BusinessLogic del Módulo Restaurante

## Descripción General

Las clases de BusinessLogic contienen las reglas de negocio del módulo Restaurante. Son independientes de Laravel HTTP, Filament, Livewire y la base de datos. Residen en `app/BusinessLogic/Restaurante/`.

## Lista de BusinessLogic

### Cálculos

#### CalcularCostoPlato

**Ubicación**: `app/BusinessLogic/Restaurante/CalcularCostoPlato.php`

**Responsabilidad**: Calcula el costo total de un plato sumando los costos de sus ingredientes desde el stock de cocina.

**Parámetros**:
- `productoRecetaId`: int (ID del producto que representa la receta)

**Retorno**: array
```php
[
    'costo_ingredientes' => float,      // Costo total de ingredientes
    'margen_sugerido_pct' => int,        // Margen sugerido (55-70%)
    'precio_sugerido' => float,         // Precio de venta sugerido
    'items' => [                         // Desglose por ingrediente
        [
            'nombre' => string,
            'cantidad' => float,
            'costo_unitario' => float,
            'costo_total' => float,
            'con_stock' => bool,         // Si hay stock disponible
        ],
        ...
    ]
]
```

**Lógica**:
1. Obtiene ingredientes de la receta desde ProductoKit
2. Para cada ingrediente:
   - Busca stock en "Cocina Restaurante"
   - Obtiene costo_unitario del lote asociado
   - Calcula costo del ingrediente (cantidad × costo_unitario)
3. Suma todos los costos = costo total
4. Calcula margen sugerido según tramo:
   - Costo < C$50 → 70%
   - Costo < C$100 → 65%
   - Costo < C$200 → 60%
   - Costo ≥ C$200 → 55%
5. Calcula precio sugerido: `costo_total / (1 - margen/100)`

**Dependencias**:
- `RestauranteRepositorioInterface`

---

#### CalcularTotalesCuenta

**Ubicación**: `app/BusinessLogic/Restaurante/CalcularTotalesCuenta.php`

**Responsabilidad**: Calcula los totales de una cuenta (subtotal, descuento, impuesto, propina, total).

**Parámetros**:
- `pedido`: Pedido (con items precargados)

**Retorno**: array
```php
[
    'subtotal' => float,     // Suma de items no anulados
    'descuento' => float,    // Descuento aplicado
    'impuesto' => float,     // Impuesto calculado
    'propina' => float,      // Propina calculada
    'total' => float,        // Total final
]
```

**Lógica**:
1. Suma subtotales de items no anulados
2. Calcula descuento (porcentaje o monto directo)
3. Calcula base imponible (subtotal - descuento)
4. Calcula impuesto (porcentaje o monto directo)
5. Calcula propina (porcentaje o monto directo)
6. Total = base imponible + impuesto + propina

**Requisito**: Los items del pedido deben estar precargados (`loadMissing('items')`)

---

#### CalcularReportesRestaurante

**Ubicación**: `app/BusinessLogic/Restaurante/CalcularReportesRestaurante.php`

**Responsabilidad**: Calcula métricas y reportes del restaurante para el dashboard.

**Parámetros**:
- `fechaInicio`: Carbon
- `fechaFin`: Carbon

**Retorno**: array con métricas calculadas

**Lógica**:
1. Filtra pedidos en el rango de fechas
2. Calcula KPIs:
   - Total de pedidos
   - Total facturado
   - Pedidos pagados
   - Pedidos pendientes
3. Genera ranking Top 10 platos por cantidad vendida
4. Agrupa ingresos por categoría de plato
5. Calcula ticket promedio

**Dependencias**:
- `RestauranteRepositorioInterface`

---

### Validaciones

#### ValidarDisponibilidadMesa

**Ubicación**: `app/BusinessLogic/Restaurante/ValidarDisponibilidadMesa.php`

**Responsabilidad**: Valida que una mesa esté disponible para abrir un pedido.

**Parámetros**:
- `mesa`: Espacio

**Lanza**: `RuntimeException` si la mesa no está disponible

**Lógica**:
1. Valida que la mesa exista
2. Valida que el tipo sea 'mesa'
3. Valida que el estado sea Disponible
4. Valida que no tenga un pedido activo

---

#### ValidarCapacidadMesasRestaurante

**Ubicación**: `app/BusinessLogic/Restaurante/ValidarCapacidadMesasRestaurante.php`

**Responsabilidad**: Valida que la capacidad total de mesas unidas sea suficiente.

**Parámetros**:
- `mesasIds`: int[]
- `capacidadRequerida`: int

**Lanza**: `RuntimeException` si la capacidad es insuficiente

**Lógica**:
1. Suma la capacidad de todas las mesas
2. Compara con capacidad requerida
3. Si es insuficiente, lanza excepción

**Dependencias**:
- `RestauranteRepositorioInterface`

---

#### ValidarDisponibilidadIngredientes

**Ubicación**: `app/BusinessLogic/Restaurante/ValidarDisponibilidadIngredientes.php`

**Responsabilidad**: Valida que haya suficiente stock de ingredientes para un proceso de cocina.

**Parámetros**:
- `productoRecetaId`: int
- `cantidadPlatos`: int

**Retorno**: bool

**Lógica**:
1. Obtiene ingredientes de la receta
2. Para cada ingrediente:
   - Calcula cantidad necesaria (receta × platos)
   - Valida stock en "Cocina Restaurante"
3. Retorna true si todos tienen stock suficiente

---

### Auditoría

#### RegistrarAuditoriaRestaurante

**Ubicación**: `app/BusinessLogic/Restaurante/RegistrarAuditoriaRestaurante.php`

**Responsabilidad**: Registra una acción de auditoría en el módulo restaurante.

**Parámetros**:
- `accion`: AccionAuditoriaRestaurante
- `mesaId`: int|null
- `pedidoId`: int|null
- `detalles`: array|null
- `userId`: int|null
- `ipAddress`: string|null

**Lógica**:
1. Crea registro en AuditoriaRestaurante
2. Guarda todos los parámetros proporcionados
3. Timestamp automático en created_at

**Dependencias**:
- `RestauranteRepositorioInterface`

---

### Verificaciones

#### VerificarRestauranteActivo

**Ubicación**: `app/BusinessLogic/Restaurante/VerificarRestauranteActivo.php`

**Responsabilidad**: Verifica si el módulo de restaurante está activo en el hotel.

**Retorno**: bool

**Lógica**:
1. Busca espacio de tipo 'restaurante'
2. Valida que exista y esté activo
3. Retorna true si está configurado, false en caso contrario

**Dependencias**:
- `RestauranteRepositorioInterface`

---

#### AsignarClienteTemporal

**Ubicación**: `app/BusinessLogic/Restaurante/AsignarClienteTemporal.php`

**Responsabilidad**: Asigna un cliente temporal para pedidos de kiosko o sin registro.

**Parámetros**:
- `datos`: array (nombre, email opcional)

**Retorno**: int (ID de la persona creada)

**Lógica**:
1. Busca o crea persona con los datos proporcionados
2. Marca como cliente temporal
3. Retorna ID para asociar al pedido

---

## Patrón de Diseño

Todas las clases de BusinessLogic siguen este patrón:

```php
final class NombreBusinessLogic
{
    public function __construct(
        private readonly Dependencia $dependencia,
    ) {}

    public function ejecutar(...$parametros): Resultado
    {
        // Lógica de negocio pura
        // Sin dependencias de HTTP/Filament
        // Sin queries directas a BD (via Repository)
        return $resultado;
    }
}
```

## Reglas

- **Independencia**: No dependen de Laravel HTTP, Filament, Livewire
- **Repository Pattern**: Acceden a datos vía Repository, no queries directos
- **Constructor Injection**: Usan inyección por constructor
- **Final Classes**: Todas son clases finales
- **Strict Types**: Usan `declare(strict_types=1)`
- **Tipado**: Parámetros y retornos tipados
- **Sin HTML**: No contienen código HTML ni vistas
- **Sin Side Effects**: Solo cálculos y validaciones, no persistencia directa
- **Reutilizables**: Pueden ser usadas desde Interactors, Jobs, etc.

## Cuándo Usar BusinessLogic vs Interactor

**Usar BusinessLogic cuando**:
- Es una regla de negocio específica
- Es un cálculo complejo
- Es una validación reutilizable
- No implica persistencia directa
- No dispara eventos

**Usar Interactor cuando**:
- Es una acción completa del usuario
- Involucra múltiples pasos
- Requiere persistencia
- Dispara eventos
- Coordina múltiples BusinessLogic
