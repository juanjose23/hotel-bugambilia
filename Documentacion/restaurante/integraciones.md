# Integraciones del Módulo Restaurante

## Descripción General

El módulo Restaurante se integra con varios otros módulos del sistema para funcionar correctamente. Este documento describe todas las integraciones, dependencias y puntos de conexión.

## Integración con Inventario

### Stock de Cocina

**Propósito**: Gestionar el stock de ingredientes en la cocina del restaurante.

**Ubicación**: Ubicación "Cocina Restaurante" en el módulo de Inventario.

**Uso**:

- **Procesos de Cocina**: Consume stock al producir platos
- **Pedidos**: Consume stock al marcar items como listos
- **Materia Prima Cocina**: Transforma material bruto en materia prima lista, registra entrada, salida y merma
- **Conciliación de Recetas**: Evalúa stock de materia prima y material bruto antes de servicio
- **Cálculo de Costos**: Obtiene costo_unitario desde Lotes

**Flujo**:

```
ProcesoCocina
    ↓
Buscar Stock en "Cocina Restaurante"
    ↓
Obtener Lote → costo_unitario
    ↓
Calcular costo del ingrediente
    ↓
Decrementar Stock
    ↓
Registrar MovimientoStock (tipo: CONSUMO)
```

**Modelos Relacionados**:

- `Stock`: Stock de variantes en ubicación
- `Lote`: Lote con costo_unitario
- `MovimientoStock`: Registro de movimientos

**Queries Utilizadas**:

- `ObtenerStockConLote`: Obtiene stock con lote asociado
- `ObtenerStockPorVariante`: Obtiene stock por variante

---

### ProductoKit (Recetas)

**Propósito**: Definir los ingredientes de una receta de plato.

**Relación**: Un Plato tiene una receta (Producto) que tiene ingredientes (ProductoKit).

**Estructura**:

```
Plato
    └── receta (Producto)
            └── ingredientes (ProductoKit)
                    ├── variante (ProductoVariante)
                    └── producto_padre (Producto)
```

**Uso**:

- **Cálculo de Costo**: Lee ingredientes para sumar costos
- **Procesos de Cocina**: Lee ingredientes para consumir stock
- **Consumo de Pedidos**: Lee ingredientes para consumir al cocinar

**Campos Importantes**:

- `producto_padre_id`: Producto receta
- `producto_variante_id`: Variante del ingrediente
- `cantidad`: Cantidad necesaria en la receta

---

### Lotes y Costos

**Propósito**: Obtener el costo unitario de los ingredientes para cálculos de pricing.

**Relación**: Stock → Lote → costo_unitario

**Uso**:

- **CalcularCostoPlato**: Obtiene costo_unitario del lote
- **RegistrarProcesoCocina**: Asigna costo a cada ingrediente
- **Reportes**: Calcula margen de ganancia

**Fórmula**:

```
costo_ingrediente = cantidad_receta × costo_unitario_lote
costo_total_plato = Σ costo_ingrediente
precio_sugerido = costo_total / (1 - margen/100)
```

---

### Movimientos de Stock

**Propósito**: Registrar el consumo de ingredientes en cocina.

**Tipo**: `CONSUMO`

**Cuándo se registra**:

- Al crear un ProcesoCocina (producción)
- Al marcar un item de pedido como listo (KDS)
- Al transformar materia prima se registran `TRANSFORMACION_SALIDA`, `TRANSFORMACION_ENTRADA` y `MERMA_COCINA`

**Datos registrados**:

- `producto_id`: Ingrediente consumido
- `producto_variante_id`: Variante consumida
- `tipo`: CONSUMO
- `cantidad`: Cantidad consumida
- `costo_unitario`: Costo unitario del lote
- `fecha`: Fecha del consumo

---

## Integración con Espacios

### Mesas del Restaurante

**Propósito**: Representar las mesas físicas del restaurante.

**Tipo**: `mesa` en el enum `TipoEspacio`

**Relación**: Pedido → mesa (Espacio)

**Estados de Mesa**:

- `Disponible`: Mesa libre para asignar
- `Ocupado`: Mesa con pedido activo
- `Limpieza`: Mesa pendiente de limpieza
- `Reservado`: Mesa reservada
- `Mantenimiento`: Mesa fuera de servicio
- `Inactivo`: Mesa desactivada

**Transiciones Automáticas**:

- **Abrir pedido**: Disponible → Ocupado
- **Cerrar pedido**: Ocupado → Limpieza
- **Unir mesas**: Disponible → Ocupado (todas)
- **Separar mesas**: Ocupado → Disponible (secundarias)

**Observer**: `PedidoObserver` gestiona cambios automáticos

---

### Ambientes

**Propósito**: Agrupar mesas por áreas del restaurante.

**Ejemplos**: Sala Principal, Terraza, Área VIP

**Relación**: Espacio (restaurante) → sub-espacios (ambientes) → mesas

**Uso en GestionMesas**:

- Agrupa mesas visualmente
- Permite filtrar por ambiente
- Muestra capacidad por ambiente

---

### Capacidad de Mesas

**Propósito**: Validar que las mesas tengan suficiente capacidad para grupos.

**Validación**: `ValidarCapacidadMesasRestaurante`

**Uso**:

- Al unir mesas para grupos grandes
- Al asignar reserva a mesas

**Cálculo**:

```
capacidad_total = Σ capacidad(mesas)
if capacidad_total < capacidad_requerida {
    throw new RuntimeException("Capacidad insuficiente");
}
```

---

## Integración con Limpieza

### Solicitud de Limpieza Automática

**Propósito**: Crear solicitudes de limpieza automáticamente al cambiar estado de mesas.

**Cuándo se crea**:

- **Al abrir pedido**: Prioridad `normal`
- **Al cerrar pedido**: Prioridad `urgente`

**Observer**: `PedidoObserver`

**Flujo**:

```
Pedido::created
    ↓
Cambiar mesa a Ocupado
    ↓
Crear SolicitudLimpieza (prioridad: normal)
```

```
Pedido::updated (estado = Pagado/Cancelado)
    ↓
Cambiar mesa a Limpieza
    ↓
Crear SolicitudLimpieza (prioridad: urgente)
```

**Interactor**: `RegistrarSolicitudLimpieza` (módulo Limpieza)

---

### Estados de Espacio

**Propósito**: Sincronizar estados de mesa con solicitudes de limpieza.

**Estados que generan limpieza**:

- `Limpieza`: Solicita limpieza normal
- `Sucio`: Solicita limpieza urgente

**Integración bidireccional**:

- Restaurante → Limpieza: Crea solicitudes
- Limpieza → Restaurante: Actualiza estado de mesa a Disponible

---

## Integración con Habitaciones

### Carga de Pedidos a Cuenta

**Propósito**: Cargar el total de un pedido a la cuenta de habitación de un huésped.

**Interactor**: `CargarPedidoACuenta`

**Flujo**:

```
Pedido (estado = Listo/Servido)
    ↓
Asociar a CuentaEstancia
    ↓
Cambiar estado a CARGADO_A_HABITACION
    ↓
Liberar mesa
```

**Estado Pedido**: `CARGADO_A_HABITACION`

**Relaciones**:

- `Pedido.cuenta_estancia_id` → `CuentaEstancia.id`
- `CuentaEstancia.estancia_id` → `Estancia.id`
- `Estancia.habitacion_id` → `Habitacion.id`

---

### Cuentas de Restaurante

**Propósito**: Gestionar cuentas propias del restaurante para clientes que no son huéspedes.

**Modelos**:

- `CuentaRestaurante`: Cuenta independiente del restaurante
- `PagoRestaurante`: Pagos realizados a cuentas de restaurante

**Flujo**:

```
Crear CuentaRestaurante
    ↓
Asociar pedidos (cuenta_restaurante_id)
    ↓
Agregar items a pedidos
    ↓
Calcular totales (subtotal, descuento, impuesto, propina, total)
    ↓
Registrar pagos (PagoRestaurante)
    ↓
Cambiar estado a PAGADA
```

**Estados de Cuenta**:

- `ABIERTA`: Cuenta activa, aceptando pedidos
- `EN_PROCESO`: Procesando pagos
- `PAGADA`: Cuenta pagada completamente
- `CANCELADA`: Cuenta cancelada

**Métodos de Pago**:

- Efectivo
- Tarjeta de Crédito
- Tarjeta de Débito
- Transferencia
- QR
- Cortesía

**Relaciones**:

- `Pedido.cuenta_restaurante_id` → `CuentaRestaurante.id`
- `CuentaRestaurante.cliente_id` → `Persona.id`
- `CuentaRestaurante.mesa_id` → `Espacio.id`
- `PagoRestaurante.cuenta_restaurante_id` → `CuentaRestaurante.id`
- `PagoRestaurante.recibido_por` → `Persona.id`

**Diferencia con CuentaEstancia**:

- `CuentaEstancia`: Para huéspedes del hotel, se carga a habitación
- `CuentaRestaurante`: Para clientes externos, pagos directos en restaurante

---

### Clientes de Habitaciones

**Propósito**: Asociar pedidos a huéspedes de habitaciones.

**Relación**: `Pedido.cliente_id` → `Persona.id`

**Uso**:

- Identificar huésped que realizó el pedido
- Historial de consumo por huésped
- Facturación a habitación

---

## Integración con Colaboradores

### Meseros

**Propósito**: Asignar meseros a pedidos para seguimiento y comisiones.

**Relación**: `Pedido.mesero_id` → `Colaborador.id`

**Uso**:

- Asignar responsable del pedido
- Cálculo de comisiones
- Reportes de desempeño por mesero

**Filtro en Filament**:

- Pedidos por mesero
- Reportes de ventas por mesero

---

### Cocineros

**Propósito**: Registrar quién realizó un proceso de cocina.

**Relación**: `ProcesoCocina.realizado_por` → `User.id`

**Uso**:

- Trazabilidad de producción
- Control de calidad
- Reportes de productividad

---

## Integración con Catálogos

## Integración con Compras / Abastecimiento

Cuando falta una materia prima usada por una receta, el sistema debe revisar si existe una regla de transformación:

```
Materia prima faltante
    ↓
¿Tiene regla de transformación?
    ├── Sí: revisar material bruto
    │       ├── Hay bruto: transformar en Materia Prima Cocina
    │       └── Falta bruto: solicitud de abastecimiento por material bruto
    └── No: crear regla o solicitar la materia prima directamente
```

La solicitud de cocina guarda `producto_variante_id`, por lo que compras/bodega puede saber exactamente qué variante se requiere.

### Categorías de Platos

**Propósito**: Clasificar platos en categorías del menú.

**Relación**: `Plato.categoria_id` → `Catalogo.id`

**Códigos de Catálogo**:

- `REST_ENTRADAS`: Entradas
- `REST_PLATOS`: Platos Fuertes
- `REST_POSTRES`: Postres
- `REST_BEBIDAS`: Bebidas
- `RESTAURANTE`: General

**Enum**: `CategoriaPlato`

**Uso**:

- Organización del menú
- Filtros en Filament
- Reportes por categoría
- Agrupación en portal web

---

### Tipos de Precio

**Propósito**: Definir tipos de precios para platos (ej: menú del día, carta, room service).

**Relación**: Polimórfica `Precio.priceable` → `Plato`

**Ejemplos**:

- `CARTA`: Precio de carta normal
- `MENU_DIA`: Precio menú del día
- `ROOM_SERVICE`: Precio room service
- `HAPPY_HOUR`: Precio happy hour

---

## Integración con Usuarios y Permisos

### Filament Shield

**Propósito**: Controlar acceso a recursos y páginas de Filament.

**Permisos por Resource**:

- `view_any_platos`, `create_platos`, `edit_platos`, `delete_platos`
- `view_any_pedidos`, `create_pedidos`, `edit_pedidos`, `delete_pedidos`
- `view_any_procesos_cocina`, `create_procesos_cocina`, `edit_procesos_cocina`
- `view_any_auditoria_restaurante`

**Permisos por Page**:

- `page_GestionMesas`
- `page_ConfiguracionRestaurante`
- `page_ReportesRestaurante`
- `page_CocinaPedidos`
- `page_AutoPedido`

**Roles típicos**:

- `Admin`: Acceso completo
- `Gerente Restaurante`: Acceso a gestión, reportes, configuración
- `Mesero`: Acceso a pedidos, gestión de mesas
- `Cocinero`: Acceso a KDS, procesos de cocina
- `Auditor`: Acceso a reportes y auditoría

---

### Auditoría de Usuarios

**Propósito**: Registrar qué usuario ejecutó cada acción crítica.

**Relación**: `AuditoriaRestaurante.user_id` → `User.id`

**Acciones auditadas**:

- Cambio de estado de mesa
- Mover cuenta entre mesas
- Aplicar descuentos
- Impresión/reimpresión de comandas
- Cambios de configuración

**Datos registrados**:

- Usuario (ID)
- Dirección IP
- Fecha y hora
- Detalles de la acción

---

## Integración con Reservas

### Reservas de Restaurante

**Propósito**: Asociar mesas a reservas de restaurante.

**Relación**: Reserva → mesas (via meta_datos o tabla pivot)

**Uso**:

- Unir mesas para una reserva
- Mostrar reservas en GestionMesas
- Priorizar mesas reservadas

**Flujo**:

```
Reserva creada
    ↓
Unir mesas necesarias
    ↓
Marcar mesas como Reservado
    ↓
Al llegar huésped → Cambiar a Ocupado
```

**Interactor**: `UnirMesas` (acepta `reservaId` opcional)

---

## Integración con Portal Web (Inertia/React)

### Página Pública del Restaurante

**Propósito**: Mostrar el menú del restaurante a huéspedes.

**Ruta**: `/restaurante`

**Interactor**: `ObtenerRestauranteLanding`

**Datos proporcionados**:

- Información del restaurante
- Ambientes y mesas disponibles
- Menú organizado por categorías
- Platos con precios e imágenes
- Horarios de operación

**Componente React**: `resources/js/pages/restaurante/Restaurante.tsx`

**Componentes del módulo**:

- `PortadaRestaurante`: Hero banner
- `AmbientesRestaurante`: Ambientes y mesas
- `MenuRestaurante`: Menú por categorías
- `HorariosRestaurante`: Horarios
- `SeccionReservaRestaurante`: Formulario de reserva

---

### Visibilidad Web

**Propósito**: Controlar qué platos se muestran en el portal web.

**Campo**: `Plato.web` (boolean)

**Filtro**: Solo platos con `web = true` se muestran en landing

**Uso**:

- Platos de temporada
- Promociones especiales
- Platos agotados (ocultar temporalmente)

---

## Integración con Impresión

### Impresoras Térmicas

**Propósito**: Imprimir comandas en diferentes áreas de cocina.

**Configuración**: Almacenada en `meta_datos` del Espacio restaurante

**Impresoras por área**:

- `impresora_cocina`: Cocina Principal
- `impresora_bar`: Barra / Tragos
- `impresora_postres`: Postres & Repostería
- `impresora_parrilla`: Parrilla & Carnes

**Determinación de impresora**:

```php
$impresora = match ($plato->area_cocina) {
    AreaCocina::COCINA => $config['impresora_cocina'],
    AreaCocina::BAR => $config['impresora_bar'],
    AreaCocina::POSTRES => $config['impresora_postres'],
    AreaCocina::PARRILLA => $config['impresora_parrilla'],
};
```

---

### Comanda Térmica

**Propósito**: Generar ticket de 80mm para cocina.

**Ruta**: `GET /admin/restaurante/pedidos/{pedido}/comanda`

**Controller**: `ComandaController@imprimir`

**Vista**: `resources/views/restaurante/comanda.blade.php`

**Características**:

- Auto-imprime al cargar (`window.print()`)
- Formato térmico 80mm
- Muestra: código, mesa, fecha, items, total
- Excluye items cancelados
- Soporta reimpresión

**Tipos de ticket**:

- `original`: Primera impresión
- `reimpresion`: Reimpresión
- `copia`: Copia adicional

---

## Integración con Eventos

### Eventos del Módulo

**PedidoCreado**: Se dispara al crear un pedido

- Listener: (opcional) Notificar a cocina

**PedidoEnviadoACocina**: Se dispara al enviar pedido a cocina

- Listener: `CrearProcesosCocina` (si aplica)

**ProcesoCocinaRegistrado**: Se dispara al registrar proceso de cocina

- Listener: (opcional) Notificar a inventario

**EstadoMesaCambiado**: Se dispara al cambiar estado de mesa

- Listener: (opcional) Actualizar dashboard en tiempo real

---

## Diagrama de Integraciones

```
┌─────────────────┐
│   Restaurante    │
└────────┬────────┘
         │
         ├──→ Inventario
         │    ├── Stock (Cocina Restaurante)
         │    ├── ProductoKit (Recetas)
         │    ├── Lotes (Costos)
         │    └── Movimientos (CONSUMO)
         │
         ├──→ Espacios
         │    ├── Mesas (tipo 'mesa')
         │    ├── Ambientes
         │    └── Estados de espacio
         │
         ├──→ Limpieza
         │    └── SolicitudLimpieza (automática)
         │
         ├──→ Habitaciones
         │    ├── CuentaEstancia (cargar pedido)
         │    └── Persona (cliente)
         │
         ├──→ Colaboradores
         │    ├── Mesero (asignado a pedido)
         │    └── User (proceso de cocina)
         │
         ├──→ Catálogos
         │    └── Categorías de platos
         │
         ├──→ Usuarios
         │    └── Permisos (Filament Shield)
         │
         ├──→ Reservas
         │    └── Asignación de mesas
         │
         ├──→ Portal Web
         │    └── Página /restaurante
         │
         └──→ Impresión
              └── Comandas térmicas
```

## Consideraciones de Diseño

### Acoplamiento

**Bajo acoplamiento**:

- Restaurant usa Repository para acceder a datos de otros módulos
- No hay dependencias directas a modelos de otros módulos en BusinessLogic
- Interactors coordinan integraciones

**Ejemplo**:

```php
// Correcto: Via Repository
$stock = $this->repositorio->obtenerStockConLote($ubicacionId, $varianteId);

// Incorrecto: Acceso directo
$stock = Stock::where('ubicacion_id', $ubicacionId)->first();
```

### Transacciones

**Transacciones distribuidas**:

- Procesos que afectan múltiples módulos usan transacciones
- Ejemplo: `RegistrarProcesoCocina` afecta Inventario y Restaurante

```php
return DB::transaction(function () {
    // Operaciones en Restaurante
    // Operaciones en Inventario
    // Si falla una, rollback de todas
});
```

### Observers

**Observers para automatización**:

- `PedidoObserver`: Automatiza cambios de mesa y limpieza
- Evita lógica duplicada en múltiples lugares

### Eventos

**Eventos para desacoplamiento**:

- Permiten que otros módulos reaccionen a acciones de Restaurante
- Ejemplo: Módulo de Reportes puede escuchar `PedidoCreado`

## Testing de Integraciones

### Feature Tests

**FlujoRestauranteTest**:

- Prueba flujo completo de pedido
- Valida integración con Inventario (consumo de stock)
- Valida integración con Limpieza (solicitudes automáticas)

**RestauranteLandingTest**:

- Prueba página pública
- Valida integración con Espacios (mesas y ambientes)
- Valida integración con Catálogos (categorías)

### Unit Tests

**BusinessLogic**:

- `CalcularCostoPlato`: Prueba cálculo con datos de Inventario
- `ValidarDisponibilidadMesa`: Prueba validación con Espacios

## Monitoreo y Logging

### Logs de Integración

**Errores de integración**:

- Fallos al consumir stock (Inventario)
- Fallos al crear solicitudes de limpieza (Limpieza)
- Fallos al cargar a cuenta (Habitaciones)

**Auditoría**:

- Todas las acciones críticas se registran en `AuditoriaRestaurante`
- Incluye usuario, IP, fecha y detalles
