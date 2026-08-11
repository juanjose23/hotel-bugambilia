# Arquitectura del Módulo Restaurante

## Descripción General

El módulo Restaurante sigue la arquitectura por capas definida en AGENTS.md, con separación clara de responsabilidades y bajo acoplamiento entre capas.

## Diagrama de Arquitectura

```
┌─────────────────────────────────────────────────────────────┐
│                     PRESENTACIÓN                              │
├─────────────────────────────────────────────────────────────┤
│  Filament Resources                                           │
│  ├── PlatoResource                                            │
│  ├── PedidoResource                                           │
│  ├── ProcesoCocinaResource                                    │
│  ├── AuditoriaRestauranteResource                             │
│  └── RelationManagers (Precios, Imágenes, Políticas)         │
│                                                               │
│  Filament Pages                                               │
│  ├── GestionMesas (Mapa de mesas POS)                         │
│  ├── ConfiguracionRestaurante                                │
│  ├── ReportesRestaurante                                      │
│  ├── CocinaPedidos (KDS)                                     │
│  ├── AutoPedido                                               │
│  └── PantallaPedidos                                          │
│                                                               │
│  Controllers                                                  │
│  ├── RestauranteController (Landing web)                     │
│  └── ComandaController (Impresión térmica)                    │
│                                                               │
│  React / Inertia                                              │
│  └── Restaurante.tsx (Página pública)                        │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                     INTERACTORS                               │
├─────────────────────────────────────────────────────────────┤
│  Gestión de Pedidos                                           │
│  ├── AbrirPedidoMesa                                          │
│  ├── CerrarPedidoMesa                                         │
│  ├── CargarPedidoACuenta                                      │
│  ├── MoverCuentaMesa                                          │
│  └── AplicarDescuentoCuenta                                   │
│                                                               │
│  Gestión de Items                                             │
│  ├── EnviarPedidoACocina                                      │
│  ├── MarcarItemPedidoListo                                    │
│  └── ConsumirIngredientesPedido                               │
│                                                               │
│  Gestión de Mesas                                             │
│  ├── CambiarEstadoMesa                                        │
│  ├── UnirMesas                                                │
│  └── SepararMesas                                             │
│                                                               │
│  Procesos de Cocina                                           │
│  └── RegistrarProcesoCocina                                   │
│                                                               │
│  Gestión de Platos                                            │
│  ├── GenerarCodigoPlato                                       │
│  └── SincronizarGaleriaPlatoImagenes                          │
│                                                               │
│  Impresión                                                     │
│  └── ReimprimirComanda                                        │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                   BUSINESS LOGIC                               │
├─────────────────────────────────────────────────────────────┤
│  Cálculos                                                     │
│  ├── CalcularCostoPlato                                       │
│  ├── CalcularTotalesCuenta                                    │
│  └── CalcularReportesRestaurante                              │
│                                                               │
│  Validaciones                                                  │
│  ├── ValidarDisponibilidadMesa                                │
│  ├── ValidarCapacidadMesasRestaurante                         │
│  └── ValidarDisponibilidadIngredientes                        │
│                                                               │
│  Auditoría                                                     │
│  └── RegistrarAuditoriaRestaurante                             │
│                                                               │
│  Verificaciones                                                │
│  ├── VerificarRestauranteActivo                               │
│  └── AsignarClienteTemporal                                   │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                     REPOSITORY                                 │
├─────────────────────────────────────────────────────────────┤
│  Queries (Lectura)                                            │
│  ├── ObtenerCatalogoPlatoQuery                                │
│  ├── ObtenerDatosPedidoFormQuery                              │
│  ├── ObtenerIngredientesPedidoQuery                           │
│  ├── ObtenerMapaMesasQuery                                    │
│  ├── ObtenerPedidosCocinaQuery                                │
│  ├── ObtenerReportesRestauranteQuery                          │
│  ├── ObtenerReservasRestauranteQuery                          │
│  └── ContarPedidosPorEstadoQuery                              │
│                                                               │
│  Persistencia (Escritura)                                     │
│  ├── RestauranteRepositorioInterface                          │
│  └── RestauranteRepositorio                                   │
│                                                               │
│  Policies                                                     │
│  └── AuditoriaRestaurantePolicy                               │
│                                                               │
│  Observers                                                    │
│  └── PedidoObserver (mesa + limpieza automática)             │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                       MODELOS                                  │
├─────────────────────────────────────────────────────────────┤
│  ├── Plato (platos)                                           │
│  ├── Pedido (pedidos)                                         │
│  ├── PedidoItem (pedido_items)                                │
│  ├── ProcesoCocina (procesos_cocina)                          │
│  ├── ProcesoItem (proceso_items)                              │
│  └── AuditoriaRestaurante (auditoria_restaurante)             │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                   BASE DE DATOS                                │
├─────────────────────────────────────────────────────────────┤
│  PostgreSQL (producción) / SQLite (pruebas)                   │
└─────────────────────────────────────────────────────────────┘
```

## Flujo de Datos

### Flujo de Creación de Pedido

```
Filament (GestionMesas)
    ↓
AbrirPedidoMesa (Interactor)
    ↓
ValidarDisponibilidadMesa (BusinessLogic)
    ↓
RestauranteRepositorio (Repository)
    ↓
Pedido (Model)
```

### Flujo de Proceso de Cocina

```
Filament (ProcesoCocinaResource)
    ↓
RegistrarProcesoCocina (Interactor)
    ↓
RestauranteRepositorio (Repository)
    ├── Obtener ingredientes receta
    ├── Obtener stock cocina
    ├── Obtener costo lote
    ├── Crear ProcesoCocina
    ├── Crear ProcesoItems
    ├── Decrementar stock
    └── Registrar movimientos
    ↓
ProcesoCocina (Model)
```

### Flujo de Cálculo de Costo

```
Filament (PlatoResource)
    ↓
CalcularCostoPlato (BusinessLogic)
    ↓
RestauranteRepositorio (Repository)
    ├── Obtener ingredientes receta
    └── Obtener stock con lote
    ↓
Cálculo de margen y precio sugerido
    ↓
Retorno de desglose de costos
```

## Patrones de Diseño

### 1. Repository Pattern

**Propósito**: Abstraer el acceso a datos y permitir testing.

**Implementación**:

- `RestauranteRepositorioInterface`: Define contrato
- `RestauranteRepositorio`: Implementación concreta
- Queries: Para operaciones de lectura complejas

**Ejemplo**:

```php
// Interactor
public function ejecutar(int $pedidoId): Pedido
{
    $pedido = $this->repositorio->obtenerPedidoPorId($pedidoId);
    // Lógica...
}

// Repository Interface
public function obtenerPedidoPorId(int $id): ?Pedido;

// Repository Implementation
public function obtenerPedidoPorId(int $id): ?Pedido
{
    return Pedido::with(['items.plato', 'mesa'])->find($id);
}
```

### 2. Interactor Pattern

**Propósito**: Orquestar casos de uso completos del sistema.

**Características**:

- Coordinan múltiples BusinessLogic
- Manejan transacciones
- Disparan eventos
- Registran auditoría
- No contienen lógica de presentación

**Ejemplo**:

```php
final class AbrirPedidoMesa
{
    public function ejecutar(...$params): Pedido
    {
        return DB::transaction(function () use ($params) {
            $this->validarMesa->validar($mesa);

            $pedido = new Pedido([...]);
            $this->repositorio->guardarPedido($pedido);

            $this->repositorio->actualizarEspacio($mesa, [
                'estado' => EstadoEspacio::Ocupado,
            ]);

            return $pedido;
        });
    }
}
```

### 3. Business Logic Pattern

**Propósito**: Contener reglas de negocio puras y reutilizables.

**Características**:

- Independientes de framework
- Sin dependencias HTTP
- Sin persistencia directa
- Reutilizables desde múltiples contextos

**Ejemplo**:

```php
final class CalcularCostoPlato
{
    public function ejecutar(int $productoRecetaId): array
    {
        $ingredientes = $this->repositorio->obtenerIngredientesReceta($id);

        $costoTotal = 0.0;
        foreach ($ingredientes as $ingrediente) {
            $costoTotal += $ingrediente->cantidad * $costoUnitario;
        }

        return [
            'costo_ingredientes' => $costoTotal,
            'margen_sugerido_pct' => $this->calcularMargen($costoTotal),
            'precio_sugerido' => $costoTotal / (1 - $margen/100),
        ];
    }
}
```

### 4. Observer Pattern

**Propósito**: Ejecutar lógica automática ante eventos del modelo.

**Implementación**: `PedidoObserver`

**Eventos**:

- `created`: Cambia mesa a Ocupado, crea solicitud de limpieza
- `updated`: Si estado = Pagado/Cancelado, cambia mesa a Limpieza

**Ejemplo**:

```php
class PedidoObserver
{
    public function created(Pedido $pedido): void
    {
        if ($pedido->mesa) {
            $pedido->mesa->update(['estado' => EstadoEspacio::Ocupado]);
            app(RegistrarSolicitudLimpieza::class)->ejecutar($pedido->mesa);
        }
    }
}
```

### 5. Enum Pattern

**Propósito**: Definir estados y tipos con type safety.

**Características**:

- Backed enums de Laravel
- Implementan interfaces de Filament
- Métodos para label, color, icon
- Evitan magic numbers

**Ejemplo**:

```php
enum EstadoPedido: string implements HasColor, HasLabel
{
    case ABIERTO = 'abierto';
    case PAGADO = 'pagado';

    public function getLabel(): string
    {
        return match ($this) {
            self::ABIERTO => 'Abierto',
            self::PAGADO => 'Pagado',
        };
    }
}
```

## Separación de Responsabilidades

### Filament (Presentación)

- **Responsabilidad**: Capturar datos, mostrar información
- **NO**: Lógica de negocio, queries directas, cálculos complejos
- **Ejemplo**: `GestionMesas` page muestra mapa, ejecuta interactors

### Interactors (Casos de Uso)

- **Responsabilidad**: Coordinar acciones completas
- **NO**: HTML, Filament, queries complejas
- **Ejemplo**: `AbrirPedidoMesa` valida, crea pedido, actualiza mesa

### BusinessLogic (Reglas)

- **Responsabilidad**: Reglas de negocio puras
- **NO**: HTTP, Filament, persistencia directa
- **Ejemplo**: `CalcularCostoPlato` calcula costos desde stock

### Repository (Datos)

- **Responsabilidad**: Acceso a datos
- **NO**: Lógica de negocio
- **Ejemplo**: `RestauranteRepositorio` abstrae queries Eloquent

### Models (Dominio)

- **Responsabilidad**: Entidades, relaciones, casts
- **NO**: Procesos complejos, flujos completos
- **Ejemplo**: `Pedido` define relaciones con mesa, items, mesero

## Inyección de Dependencias

### Constructor Injection (Recomendado)

```php
final class AbrirPedidoMesa
{
    public function __construct(
        private readonly ValidarDisponibilidadMesa $validarMesa,
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}
}
```

### Service Locator (Evitar)

```php
// NO RECOMENDADO
$repositorio = app(RestauranteRepositorioInterface::class);
```

## Transacciones

Todos los Interactors que modifican datos usan transacciones:

```php
return DB::transaction(function () use ($params) {
    // Múltiples operaciones
    // Si falla una, se hace rollback de todas
    return $resultado;
});
```

## Eventos y Listeners

### Eventos

- `PedidoCreado`: Al crear un pedido
- `PedidoEnviadoACocina`: Al enviar a cocina
- `ProcesoCocinaRegistrado`: Al registrar proceso de cocina

### Listeners

- `CrearProcesosCocina`: Crea procesos de cocina al enviar pedido
- `NotificarCocina`: Envía notificación a cocina

## Auditoría

### Registro Automático (OwenIt\Auditing)

- Todos los modelos principales usan trait `Auditable`
- Cambios en modelos se registran automáticamente

### Auditoría Manual (AccionAuditoriaRestaurante)

- Acciones críticas se registran manualmente
- Usa `RegistrarAuditoriaRestaurante` (BusinessLogic)
- Registra: acción, mesa, pedido, usuario, IP, detalles

## Testing

### Feature Tests

- `FlujoRestauranteTest`: Flujo completo de restaurante
- `RestauranteLandingTest`: Página pública

### Unit Tests

- BusinessLogic de Restaurante
- Cálculos de costos
- Validaciones

## Configuración

La configuración del restaurante se almacena en `meta_datos` del Espacio principal:

```php
$restaurante->meta_datos = [
    'propina_sugerida' => 10.0,
    'impuesto_porcentaje' => 15.0,
    'impresora_cocina' => 'Termica_Cocina',
    'impresora_bar' => 'Termica_Bar',
    'impresora_postres' => 'Termica_Postres',
    'impresora_parrilla' => 'Termica_Parrilla',
    'impresion_automatica' => true,
    'copias_ticket' => 1,
];
```

## Integración con Otros Módulos

### Inventario

- Stock en "Cocina Restaurante"
- ProductoKit para recetas
- Lotes para costos
- Movimientos tipo CONSUMO

### Espacios

- Mesas (tipo 'mesa')
- Ambientes
- Estados de espacio

### Limpieza

- SolicitudLimpieza automática
- Prioridades: Normal/Urgente

### Habitaciones

- Carga de pedidos a cuenta
- Estado CARGADO_A_HABITACION
