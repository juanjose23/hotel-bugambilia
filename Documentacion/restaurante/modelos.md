# Modelos del Módulo Restaurante

## Descripción General

Los modelos del módulo Restaurante representan las entidades del dominio y residen en `app/Repository/Models/Restaurante/`. Siguen las convenciones de Laravel con soft deletes, auditoría (OwenIt\Auditing) y relaciones Eloquent.

## Modelos Principales

### 1. CuentaRestaurante

**Ubicación**: `app/Repository/Models/Restaurante/CuentaRestaurante.php`

**Tabla**: `cuentas_restaurante`

**Descripción**: Representa una cuenta propia del restaurante para clientes que no son huéspedes. Permite agrupar pedidos y gestionar pagos independientes de las cuentas de habitación.

#### Propiedades

| Propiedad              | Tipo                         | Descripción                            |
| ---------------------- | ---------------------------- | -------------------------------------- |
| `id`                   | int                          | Identificador único                    |
| `codigo`               | string                       | Código de la cuenta (CTA-XXXX)         |
| `cliente_id`           | int                          | FK a personas (cliente opcional)       |
| `mesa_id`              | int                          | FK a espacios (mesa asociada opcional) |
| `estado`               | EstadoCuentaRestaurante enum | Estado de la cuenta                    |
| `subtotal`             | decimal                      | Subtotal de la cuenta                  |
| `descuento_monto`      | decimal                      | Monto de descuento                     |
| `descuento_porcentaje` | decimal                      | Porcentaje de descuento                |
| `impuesto_monto`       | decimal                      | Monto de impuesto                      |
| `impuesto_porcentaje`  | decimal                      | Porcentaje de impuesto                 |
| `propina_monto`        | decimal                      | Monto de propina                       |
| `propina_porcentaje`   | decimal                      | Porcentaje de propina                  |
| `total`                | decimal                      | Total de la cuenta                     |
| `abierta_en`           | datetime                     | Hora de apertura                       |
| `cerrada_en`           | datetime                     | Hora de cierre                         |
| `notas`                | text                         | Observaciones de la cuenta             |

#### Relaciones

- **cliente**: BelongsTo → Persona
- **pedido**: BelongsTo → Pedido (pedido principal asociado)
- **pagos**: HasMany → PagoRestaurante

#### Casts

- `estado`: EstadoCuentaRestaurante enum
- `subtotal`: decimal:2
- `descuento_monto`: decimal:2
- `descuento_porcentaje`: decimal:2
- `impuesto_monto`: decimal:2
- `impuesto_porcentaje`: decimal:2
- `propina_monto`: decimal:2
- `propina_porcentaje`: decimal:2
- `total`: decimal:2
- `abierta_en`: datetime
- `cerrada_en`: datetime

---

### 2. PagoRestaurante

**Ubicación**: `app/Repository/Models/Restaurante/PagoRestaurante.php`

**Tabla**: `pagos_restaurante`

**Descripción**: Representa un pago realizado a una cuenta de restaurante.

#### Propiedades

| Propiedad               | Tipo            | Descripción                                      |
| ----------------------- | --------------- | ------------------------------------------------ |
| `id`                    | int             | Identificador único                              |
| `codigo`                | string          | Código del pago (PAG-XXXX)                       |
| `cuenta_restaurante_id` | int             | FK a cuentas_restaurante                         |
| `metodo_pago`           | MetodoPago enum | Método de pago                                   |
| `monto`                 | decimal         | Monto del pago                                   |
| `referencia`            | string          | Referencia del pago (ej: número de autorización) |
| `recibido_por`          | int             | FK a personas (quien recibió el pago)            |
| `fecha_pago`            | datetime        | Fecha del pago                                   |
| `notas`                 | text            | Observaciones del pago                           |

#### Relaciones

- **cuenta**: BelongsTo → CuentaRestaurante
- **recibidoPor**: BelongsTo → Persona

#### Casts

- `metodo_pago`: MetodoPago enum
- `monto`: decimal:2
- `fecha_pago`: datetime

---

### 3. Plato

**Ubicación**: `app/Repository/Models/Restaurante/Plato.php`

**Tabla**: `platos`

**Descripción**: Representa un plato del menú del restaurante con su información, receta asociada, precios e imágenes.

#### Propiedades

| Propiedad            | Tipo            | Descripción                                     |
| -------------------- | --------------- | ----------------------------------------------- |
| `id`                 | int             | Identificador único                             |
| `codigo`             | string          | Código del plato (PLT-XXXX)                     |
| `nombre`             | string          | Nombre del plato                                |
| `categoria_id`       | int             | FK a catalogos (categoría del plato)            |
| `producto_receta_id` | int             | FK a productos (receta asociada)                |
| `descripcion`        | text            | Descripción del plato                           |
| `web`                | boolean         | Visible en portal web                           |
| `estado`             | int             | 1=Activo, 0=Inactivo                            |
| `tiempo_preparacion` | string          | Ej: "15 - 25 min"                               |
| `area_cocina`        | AreaCocina enum | Área de cocina (Cocina, Bar, Postres, Parrilla) |

#### Relaciones

- **categoria**: BelongsTo → Catalogo
- **receta**: BelongsTo → Producto (receta base)
- **ingredientes**: HasManyThrough → ProductoKit (ingredientes de la receta)
- **precios**: MorphMany → Precio (precios polimórficos)
- **imagenes**: MorphMany → Imagen (galería de imágenes)
- **politicas**: MorphToMany → Politica (políticas asociadas)
- **itemsPedido**: HasMany → PedidoItem

#### Scopes

- **activos()**: Filtra platos con estado = 1

#### Casts

- `estado`: integer
- `web`: boolean
- `area_cocina`: AreaCocina enum

---

### 2. Pedido

**Ubicación**: `app/Repository/Models/Restaurante/Pedido.php`

**Tabla**: `pedidos`

**Descripción**: Representa un pedido de restaurante asociado a una mesa, con items, totales y estado.

#### Propiedades

| Propiedad               | Tipo              | Descripción                                                     |
| ----------------------- | ----------------- | --------------------------------------------------------------- |
| `id`                    | int               | Identificador único                                             |
| `codigo`                | string            | Código del pedido (PED-YYYYMMDD-XXXX)                           |
| `mesa_id`               | int               | FK a espacios (mesa asignada)                                   |
| `mesero_id`             | int               | FK a colaboradores (mesero)                                     |
| `cliente_id`            | int               | FK a personas (cliente)                                         |
| `cuenta_estancia_id`    | int               | FK a cuentas de estancia (opcional, para huéspedes)             |
| `cuenta_restaurante_id` | int               | FK a cuentas_restaurante (opcional, para clientes no huéspedes) |
| `padre_pedido_id`       | int               | FK a pedidos (para sub-cuentas)                                 |
| `estado`                | EstadoPedido enum | Estado del pedido                                               |
| `total`                 | decimal           | Total del pedido                                                |
| `propina_monto`         | decimal           | Monto de propina                                                |
| `propina_porcentaje`    | decimal           | Porcentaje de propina                                           |
| `impuesto_monto`        | decimal           | Monto de impuesto                                               |
| `impuesto_porcentaje`   | decimal           | Porcentaje de impuesto                                          |
| `descuento_monto`       | decimal           | Monto de descuento                                              |
| `descuento_porcentaje`  | decimal           | Porcentaje de descuento                                         |
| `consecutivo_comanda`   | int               | Consecutivo para impresión                                      |
| `abierto_en`            | datetime          | Hora de apertura                                                |
| `cerrado_en`            | datetime          | Hora de cierre                                                  |
| `notas`                 | text              | Observaciones del pedido                                        |

#### Relaciones

- **mesa**: BelongsTo → Espacio
- **mesero**: BelongsTo → Colaborador
- **cliente**: BelongsTo → Persona
- **cuentaEstancia**: BelongsTo → CuentaEstancia (para huéspedes)
- **cuentaRestaurante**: BelongsTo → CuentaRestaurante (para clientes no huéspedes)
- **pedidoPadre**: BelongsTo → Pedido (self)
- **subCuentas**: HasMany → Pedido (self)
- **items**: HasMany → PedidoItem

#### Casts

- `estado`: EstadoPedido enum
- `total`: decimal:2
- `propina_monto`: decimal:2
- `propina_porcentaje`: decimal:2
- `impuesto_monto`: decimal:2
- `impuesto_porcentaje`: decimal:2
- `descuento_monto`: decimal:2
- `descuento_porcentaje`: decimal:2
- `consecutivo_comanda`: integer
- `abierto_en`: datetime
- `cerrado_en`: datetime

---

### 3. PedidoItem

**Ubicación**: `app/Repository/Models/Restaurante/PedidoItem.php`

**Tabla**: `pedido_items`

**Descripción**: Representa un item individual dentro de un pedido (plato, cantidad, precio).

#### Propiedades

| Propiedad               | Tipo                  | Descripción                                                          |
| ----------------------- | --------------------- | -------------------------------------------------------------------- |
| `id`                    | int                   | Identificador único                                                  |
| `pedido_id`             | int                   | FK a pedidos (pedido padre)                                          |
| `plato_id`              | int                   | FK a platos                                                          |
| `cantidad`              | decimal               | Cantidad ordenada                                                    |
| `precio_unitario`       | decimal               | Precio unitario al momento del pedido                                |
| `subtotal`              | decimal               | Subtotal (cantidad × precio_unitario)                                |
| `estado`                | EstadoItemPedido enum | Estado del item                                                      |
| `bloqueo_stock_detalle` | json                  | Detalle de ingredientes faltantes cuando no se puede enviar a cocina |
| `bloqueado_stock_en`    | datetime              | Fecha de bloqueo por falta de stock                                  |
| `notas`                 | text                  | Notas especiales (ej: "Sin cebolla")                                 |

#### Relaciones

- **pedido**: BelongsTo → Pedido
- **plato**: BelongsTo → Plato

#### Estados Posibles

- `pendiente`: Item agregado, no enviado a cocina
- `preparacion`: En preparación en cocina
- `listo`: Listo para servir
- `servido`: Entregado al cliente
- `anulado`: Item cancelado
- `bloqueado_stock`: Item pendiente de confirmación del cliente por falta de materia prima

---

### 4. ProcesoCocina

**Ubicación**: `app/Repository/Models/Restaurante/ProcesoCocina.php`

**Tabla**: `procesos_cocina`

**Descripción**: Representa un proceso de producción en cocina basado en la receta de un plato. Registra el consumo de ingredientes y costos.

#### Propiedades

| Propiedad            | Tipo    | Descripción                            |
| -------------------- | ------- | -------------------------------------- |
| `id`                 | int     | Identificador único                    |
| `codigo`             | string  | Código del proceso                     |
| `plato_id`           | int     | FK a platos                            |
| `cantidad_platos`    | int     | Número de platos a producir            |
| `producto_origen_id` | int     | FK a productos (receta origen)         |
| `variante_origen_id` | int     | FK a variantes (opcional)              |
| `cantidad_procesada` | decimal | Cantidad procesada (= cantidad_platos) |
| `costo_total`        | decimal | Costo total calculado                  |
| `realizado_por`      | int     | FK a users (usuario)                   |
| `observaciones`      | text    | Notas del proceso                      |

#### Relaciones

- **plato**: BelongsTo → Plato
- **productoOrigen**: BelongsTo → Producto (receta)
- **varianteOrigen**: BelongsTo → ProductoVariante
- **realizadoPor**: BelongsTo → User
- **items**: HasMany → ProcesoItem

#### Casts

- `cantidad_procesada`: decimal:3
- `cantidad_platos`: integer
- `costo_total`: decimal:2

---

### 5. ProcesoItem

**Ubicación**: `app/Repository/Models/Restaurante/ProcesoItem.php`

**Tabla**: `proceso_items`

**Descripción**: Representa un ingrediente dentro de un proceso de cocina con su costo asignado.

#### Propiedades

| Propiedad              | Tipo    | Descripción                  |
| ---------------------- | ------- | ---------------------------- |
| `id`                   | int     | Identificador único          |
| `proceso_id`           | int     | FK a procesos_cocina         |
| `producto_destino_id`  | int     | FK a productos (ingrediente) |
| `variante_destino_id`  | int     | FK a variantes               |
| `cantidad`             | decimal | Cantidad (receta × platos)   |
| `peso_unitario`        | decimal | Peso unitario de la variante |
| `peso_total`           | decimal | Peso total del ingrediente   |
| `costo_asignado`       | decimal | Costo asignado               |
| `es_merma`             | boolean | Si es merma/pérdida          |
| `ubicacion_destino_id` | int     | FK a ubicaciones             |

#### Relaciones

- **proceso**: BelongsTo → ProcesoCocina

---

### 6. TransformacionMateriaPrima

**Ubicación**: `app/Repository/Models/Restaurante/TransformacionMateriaPrima.php`

**Tabla**: `restaurante_transformaciones_materia_prima`

**Descripción**: Registra una operación de transformación de material bruto en materia prima lista para cocina.

#### Relaciones

- **productoOrigen**: BelongsTo → Producto
- **varianteOrigen**: BelongsTo → ProductoVariante
- **ubicacionOrigen**: BelongsTo → Ubicacion
- **realizadoPor**: BelongsTo → User
- **items**: HasMany → TransformacionMateriaPrimaItem

---

### 7. RecetaTransformacionMateriaPrima

**Ubicación**: `app/Repository/Models/Restaurante/RecetaTransformacionMateriaPrima.php`

**Tabla**: `restaurante_recetas_transformacion_materia_prima`

**Descripción**: Define qué material bruto produce una materia prima y con qué rendimiento esperado.

#### Relaciones

- **productoMateriaPrima**: BelongsTo → Producto
- **varianteMateriaPrima**: BelongsTo → ProductoVariante
- **productoBruto**: BelongsTo → Producto
- **varianteBruta**: BelongsTo → ProductoVariante
- **unidadMedida**: BelongsTo → Catalogo

---

### 8. AuditoriaRestaurante

**Ubicación**: `app/Repository/Models/Restaurante/AuditoriaRestaurante.php`

**Tabla**: `auditoria_restaurante`

**Descripción**: Registra acciones críticas en el restaurante para trazabilidad y control.

#### Propiedades

| Propiedad    | Tipo                            | Descripción                       |
| ------------ | ------------------------------- | --------------------------------- |
| `id`         | int                             | Identificador único               |
| `accion`     | AccionAuditoriaRestaurante enum | Tipo de acción                    |
| `mesa_id`    | int                             | FK a espacios (mesa relacionada)  |
| `pedido_id`  | int                             | FK a pedidos (pedido relacionado) |
| `user_id`    | int                             | FK a users (usuario que ejecutó)  |
| `detalles`   | json                            | Detalles adicionales de la acción |
| `ip_address` | string                          | Dirección IP del usuario          |
| `created_at` | datetime                        | Fecha de registro                 |

#### Relaciones

- **mesa**: BelongsTo → Espacio
- **pedido**: BelongsTo → Pedido
- **user**: BelongsTo → User

#### Acciones Auditadas

- `CAMBIO_ESTADO_MESA`: Cambio de estado de mesa
- `MOVER_CUENTA_MESA`: Traslado de cuenta entre mesas
- `APLICAR_DESCUENTO`: Aplicación de descuento
- `IMPRIMIR_COMANDA`: Impresión de comanda
- `REIMPRIMIR_COMANDA`: Reimpresión de comanda
- `GUARDAR_CONFIGURACION_RESTAURANTE`: Cambios en configuración

---

## Relaciones Entre Modelos

```
Plato
 ├── categoria (Catalogo)
 ├── receta (Producto)
 ├── ingredientes (ProductoKit)
 ├── precios (Precio) [polimórfico]
 ├── imagenes (Imagen) [polimórfico]
 ├── politicas (Politica) [polimórfico]
 └── itemsPedido (PedidoItem)

Pedido
 ├── mesa (Espacio)
 ├── mesero (Colaborador)
 ├── cliente (Persona)
 ├── cuenta (CuentaEstancia)
 ├── pedidoPadre (Pedido) [self]
 ├── subCuentas (Pedido) [self]
 └── items (PedidoItem)

PedidoItem
 ├── pedido (Pedido)
 └── plato (Plato)

ProcesoCocina
 ├── plato (Plato)
 ├── productoOrigen (Producto)
 ├── varianteOrigen (ProductoVariante)
 ├── realizadoPor (User)
 └── items (ProcesoItem)

ProcesoItem
 └── proceso (ProcesoCocina)

TransformacionMateriaPrima
 ├── productoOrigen (Producto)
 ├── varianteOrigen (ProductoVariante)
 ├── ubicacionOrigen (Ubicacion)
 ├── realizadoPor (User)
 └── items (TransformacionMateriaPrimaItem)

RecetaTransformacionMateriaPrima
 ├── varianteMateriaPrima (ProductoVariante)
 └── varianteBruta (ProductoVariante)

AuditoriaRestaurante
 ├── mesa (Espacio)
 ├── pedido (Pedido)
 └── user (User)
```

## Soft Deletes y Auditoría

Todos los modelos principales usan:

- **SoftDeletes**: Para permitir restauración de registros eliminados
- **Auditable (OwenIt\Auditing)**: Para registro automático de cambios en modelos

## Índices de Base de Datos

### pedidos

- `mesa_id`: Para consultas por mesa
- `estado`: Para filtrar pedidos activos

### pedido_items

- `pedido_id`: Para cargar items de un pedido
- `plato_id`: Para consultas por plato

### procesos_cocina

- `plato_id`: Para procesos por plato
- `realizado_por`: Para procesos por usuario

### proceso_items

- `proceso_id`: Para items de un proceso
