# Interactors del Módulo Restaurante

## Descripción General

Los Interactors representan casos de uso completos del sistema y coordinan validaciones, reglas de negocio, repositorios, eventos y auditoría. Residen en `app/Interactors/Restaurante/`.

## Lista de Interactors

### Gestión de Pedidos

#### AbrirPedidoMesa

**Ubicación**: `app/Interactors/Restaurante/AbrirPedidoMesa.php`

**Responsabilidad**: Abre un nuevo pedido en una mesa, validando disponibilidad y cambiando estado de la mesa.

**Parámetros**:
- `mesa`: Espacio|null (mesa donde se abre el pedido)
- `meseroId`: int|null (colaborador asignado)
- `clienteId`: int|null (cliente asociado)
- `notas`: string|null (observaciones)

**Flujo**:
1. Valida disponibilidad de la mesa (si se proporciona)
2. Genera código de pedido: `PED-YYYYMMDD-XXXX`
3. Crea pedido con estado ABIERTO
4. Cambia estado de mesa a Ocupado
5. Retorna el pedido creado

**Dependencias**:
- `ValidarDisponibilidadMesa` (BusinessLogic)
- `RestauranteRepositorioInterface` (Repository)

---

#### CerrarPedidoMesa

**Ubicación**: `app/Interactors/Restaurante/CerrarPedidoMesa.php`

**Responsabilidad**: Cierra un pedido, calcula totales y cambia estado de mesa a Limpieza.

**Parámetros**:
- `pedidoId`: int (identificador del pedido)

**Flujo**:
1. Carga el pedido con items
2. Calcula totales (subtotal, descuento, impuesto, propina, total)
3. Actualiza estado a PAGADO
4. Registra hora de cierre
5. Cambia estado de mesa a Limpieza
6. Crea solicitud de limpieza urgente

**Dependencias**:
- `CalcularTotalesCuenta` (BusinessLogic)
- `RestauranteRepositorioInterface`

---

#### CargarPedidoACuenta

**Ubicación**: `app/Interactors/Restaurante/CargarPedidoACuenta.php`

**Responsabilidad**: Carga un pedido a la cuenta de una habitación de huésped.

**Parámetros**:
- `pedidoId`: int
- `cuentaEstanciaId`: int

**Flujo**:
1. Valida que el pedido esté en estado LISTO o SERVIDO
2. Asocia el pedido a la cuenta de estancia
3. Cambia estado a CARGADO_A_HABITACION
4. Libera la mesa

**Dependencias**:
- `RestauranteRepositorioInterface`

---

#### MoverCuentaMesa

**Ubicación**: `app/Interactors/Restaurante/MoverCuentaMesa.php`

**Responsabilidad**: Traslada un pedido de una mesa a otra.

**Parámetros**:
- `mesaOrigenId`: int
- `mesaDestinoId`: int

**Flujo**:
1. Valida que la mesa origen tenga un pedido activo
2. Valida que la mesa destino esté disponible
3. Actualiza mesa_id del pedido
4. Libera mesa origen
5. Ocupa mesa destino
6. Registra auditoría

**Dependencias**:
- `ValidarDisponibilidadMesa`
- `RestauranteRepositorioInterface`
- `RegistrarAuditoriaRestaurante`

---

#### AplicarDescuentoCuenta

**Ubicación**: `app/Interactors/Restaurante/AplicarDescuentoCuenta.php`

**Responsabilidad**: Aplica un descuento (porcentaje o monto fijo) a un pedido.

**Parámetros**:
- `pedidoId`: int
- `descuentoPorcentaje`: float
- `descuentoMonto`: float
- `motivo`: string|null

**Flujo**:
1. Valida que el pedido esté en estado ABIERTO o EN_PREPARACION
2. Aplica descuento (porcentaje o monto directo)
3. Recalcula totales
4. Registra auditoría con motivo

**Dependencias**:
- `CalcularTotalesCuenta`
- `RestauranteRepositorioInterface`
- `RegistrarAuditoriaRestaurante`

---

### Gestión de Items de Pedido

#### EnviarPedidoACocina

**Ubicación**: `app/Interactors/Restaurante/EnviarPedidoACocina.php`

**Responsabilidad**: Envía un pedido a cocina cambiando su estado.

**Parámetros**:
- `pedidoId`: int

**Flujo**:
1. Valida que el pedido tenga items
2. Cambia estado del pedido a EN_PREPARACION
3. Cambia estado de items a PENDIENTE
4. Dispara evento para creación de procesos de cocina

**Dependencias**:
- `RestauranteRepositorioInterface`

---

#### MarcarItemPedidoListo

**Ubicación**: `app/Interactors/Restaurante/MarcarItemPedidoListo.php`

**Responsabilidad**: Marca un item de pedido como listo y consume ingredientes del stock.

**Parámetros**:
- `pedidoItemId`: int

**Flujo**:
1. Carga el item con plato y receta
2. Cambia estado a LISTO
3. Consume ingredientes del stock de cocina
4. Registra movimientos de stock tipo CONSUMO
5. Si todos los items están listos, cambia pedido a LISTO

**Dependencias**:
- `ConsumirIngredientesPedido`
- `RestauranteRepositorioInterface`

---

#### ConsumirIngredientesPedido

**Ubicación**: `app/Interactors/Restaurante/ConsumirIngredientesPedido.php`

**Responsabilidad**: Consume los ingredientes de un plato del stock de cocina.

**Parámetros**:
- `pedidoItem`: PedidoItem

**Flujo**:
1. Obtiene receta del plato
2. Lee ingredientes desde ProductoKit
3. Para cada ingrediente:
   - Calcula cantidad a consumir (receta × cantidad pedido)
   - Decrementa stock en "Cocina Restaurante"
   - Registra movimiento de stock tipo CONSUMO

**Dependencias**:
- `RestauranteRepositorioInterface`

---

### Gestión de Mesas

#### CambiarEstadoMesa

**Ubicación**: `app/Interactors/Restaurante/CambiarEstadoMesa.php`

**Responsabilidad**: Cambia el estado de una mesa manualmente.

**Parámetros**:
- `mesaId`: int
- `nuevoEstado`: EstadoEspacio

**Flujo**:
1. Carga la mesa
2. Valida transición de estado válida
3. Actualiza estado
4. Si el estado es LIMPIEZA, crea solicitud de limpieza

**Dependencias**:
- `RestauranteRepositorioInterface`

---

#### UnirMesas

**Ubicación**: `app/Interactors/Restaurante/UnirMesas.php`

**Responsabilidad**: Une múltiples mesas bajo una mesa principal para grupos grandes.

**Parámetros**:
- `mesaPrincipalId`: int
- `mesasSecundariasIds`: int[]
- `reservaId`: int|null
- `motivo`: string

**Flujo**:
1. Valida que todas las mesas estén disponibles
2. Valida capacidad total
3. Crea relación de unión en meta_datos
4. Ocupa todas las mesas
5. Si hay reserva, asocia a la reserva

**Dependencias**:
- `ValidarCapacidadMesasRestaurante`
- `RestauranteRepositorioInterface`

---

#### SepararMesas

**Ubicación**: `app/Interactors/Restaurante/SepararMesas.php`

**Responsabilidad**: Separa mesas que estaban unidas y las libera.

**Parámetros**:
- `mesaId`: int

**Flujo**:
1. Carga la mesa con relaciones de unión
2. Elimina relaciones de unión en meta_datos
3. Cambia estado de todas las mesas a Disponible
4. Si hay pedido activo, lo mantiene en la mesa principal

**Dependencias**:
- `RestauranteRepositorioInterface`

---

### Procesos de Cocina

#### RegistrarProcesoCocina

**Ubicación**: `app/Interactors/Restaurante/RegistrarProcesoCocina.php`

**Responsabilidad**: Registra un proceso de producción basado en la receta de un plato, consumiendo ingredientes y calculando costos.

**Parámetros**:
- `codigo`: string
- `plato_id`: int
- `cantidad_platos`: int
- `realizado_por`: int|null
- `observaciones`: string|null

**Flujo**:
1. Valida que el plato tenga receta asociada
2. Lee ingredientes de la receta
3. Para cada ingrediente:
   - Busca stock en "Cocina Restaurante"
   - Obtiene costo_unitario del lote
   - Calcula costo asignado (cantidad × costo_unitario)
4. Crea ProcesoCocina con costo total
5. Crea ProcesoItem por ingrediente
6. Decrementa stock de cada ingrediente
7. Registra movimientos de stock tipo CONSUMO

**Dependencias**:
- `RestauranteRepositorioInterface`

---

### Gestión de Platos

#### GenerarCodigoPlato

**Ubicación**: `app/Interactors/Restaurante/GenerarCodigoPlato.php`

**Responsabilidad**: Genera un código único para un plato (PLT-XXXX).

**Retorno**: string (ej: "PLT-0001")

**Flujo**:
1. Busca el último código existente
2. Incrementa el consecutivo
3. Formatea como PLT-XXXX con ceros a la izquierda

---

#### SincronizarGaleriaPlatoImagenes

**Ubicación**: `app/Interactors/Restaurante/SincronizarGaleriaPlatoImagenes.php`

**Responsabilidad**: Sincroniza las imágenes de un plato eliminando las no usadas y actualizando orden.

**Parámetros**:
- `platoId`: int
- `urls`: string[] (URLs de imágenes a mantener)

**Flujo**:
1. Obtiene URLs actuales del plato
2. Elimina imágenes que no están en la nueva lista
3. Actualiza orden de las imágenes restantes

**Dependencias**:
- `RestauranteRepositorioInterface`

---

### Impresión de Comandas

#### ReimprimirComanda

**Ubicación**: `app/Interactors/Restaurante/ReimprimirComanda.php`

**Responsabilidad**: Registra la reimpresión de una comanda y retorna el pedido.

**Parámetros**:
- `pedidoId`: int
- `area`: string|null (área de cocina)
- `userId`: int|null
- `ipAddress`: string|null

**Flujo**:
1. Carga el pedido
2. Registra auditoría de reimpresión
3. Retorna el pedido para generar la URL de impresión

**Dependencias**:
- `RestauranteRepositorioInterface`
- `RegistrarAuditoriaRestaurante`

---

### Kiosko

#### ConfirmarPedidoKiosko

**Ubicación**: `app/Interactors/Restaurante/ConfirmarPedidoKiosko.php`

**Responsabilidad**: Confirma un pedido realizado desde kiosko auto-servicio.

**Parámetros**:
- `datos`: array (datos del pedido)

**Flujo**:
1. Valida datos del pedido
2. Crea pedido sin mesa asignada
3. Asigna cliente temporal
4. Envía a cocina automáticamente

**Dependencias**:
- `AsignarClienteTemporal` (BusinessLogic)
- `RestauranteRepositorioInterface`

---

## Patrón de Diseño

Todos los Interactors siguen este patrón:

```php
final class NombreInteractor
{
    public function __construct(
        private readonly Dependencia1 $dep1,
        private readonly Dependencia2 $dep2,
    ) {}

    public function ejecutar(...$parametros): Resultado
    {
        return DB::transaction(function () use ($parametros) {
            // 1. Validaciones
            // 2. Lógica de negocio
            // 3. Persistencia
            // 4. Eventos
            // 5. Auditoría
            return $resultado;
        });
    }
}
```

## Reglas

- **Transacciones**: Todos los Interactors usan transacciones de base de datos
- **Constructor Injection**: Usan inyección por constructor, no `app()`
- **Final Classes**: Todos son clases finales
- **Strict Types**: Usan `declare(strict_types=1)`
- **Tipado**: Parámetros y retornos tipados
- **Sin HTML**: No contienen código HTML ni vistas
- **Sin Filament**: No dependen de Filament directamente
