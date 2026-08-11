# Documentación de Flujos de Procesos: Módulo Restaurante

---

## 1. Submódulo / Funcionalidad: Gestión de Platos

- **Descripción de la Pantalla / Vista:** Tabla con todos los platos del menú. Muestra imagen circular, código (PLT-XXX), nombre, categoría (badge), estado (badge), visibilidad web (ícono), y fecha de creación. Filtros por categoría y visibilidad web. Acciones: Ver, Editar, Eliminar, Restaurar.
- **Disparador (Trigger):** Acceso desde `Restaurante > Platos` en el panel admin.
- **Flujo Paso a Paso:**
    1. El usuario accede a la interfaz de Platos y visualiza la tabla paginada.
    2. El sistema valida los permisos (Filament Shield) y carga los platos con relaciones `categoria`, `imagenes`.
    3. El usuario puede filtrar por categoría (Entradas, Platos, Postres, Bebidas) o visibilidad web.
    4. El usuario hace clic en el botón de acciones (⋮) para Ver, Editar o Eliminar un plato.
    5. El sistema redirige a la página correspondiente.

---

## 2. Submódulo / Funcionalidad: Crear / Editar Plato

- **Descripción de la Pantalla / Vista:** Formulario con secciones: Información General (código auto-generado PLT-XXXX, nombre, categoría, receta asociada, estado, web), Descripción, Tiempo de Preparación, Galería de Imágenes (upload múltiple, max 3, reordenable). Relation Managers: Precios (moneda, tipo, precio, vigencia, oferta) y Políticas (attach/detach polimórfico).
- **Disparador (Trigger):** Botón "Crear Plato" en la lista o "Editar" en el menú de acciones.
- **Flujo Paso a Paso:**
    1. El usuario hace clic en "Crear Plato".
    2. El sistema genera automáticamente el código `PLT-XXXX` mediante `GenerarCodigoPlato`.
    3. El usuario completa: nombre, categoría (select filtrado por `CategoriaPlato::codigos()`), estado, toggle web.
    4. El usuario opcionalmente asocia una receta (`producto_receta_id` → Producto existente).
    5. El usuario escribe la descripción y tiempo de preparación (ej: "15 - 25 min").
    6. El usuario sube hasta 3 imágenes para la galería (FileUpload polimórfico, directorio `restaurante/platos`).
    7. El usuario agrega precios desde el Relation Manager `PreciosRelationManager`:
        - Selecciona moneda (default NIO), tipo de precio, monto, fecha de inicio/fin, estado.
        - El sistema valida precio duplicado activo via `VerificarPrecioDuplicado`.
    8. El usuario opcionalmente asocia políticas desde `PoliticasRelationManager`.
    9. El usuario hace clic en "Crear" / "Guardar".
    10. El sistema valida restricciones (código único, nombre requerido, categoría requerida).
    11. ¿Se cumple con las validaciones?
        - Si es **No**, muestra errores en los campos correspondientes.
        - Si es **Sí**, crea el registro, sincroniza imágenes vía `SincronizarGaleriaPlatoImagenes`, y muestra notificación de éxito.

---

## 3. Submódulo / Funcionalidad: Cálculo de Costo de Plato

- **Descripción de la Pantalla / Vista:** Interactor `CalcularCostoPlato` que calcula el costo total de un plato sumando los costos de sus ingredientes. El costo de cada ingrediente se obtiene del Stock en "Cocina Restaurante" → Lote → `costo_unitario`.
- **Disparador (Trigger):** Invocado por otros procesos (pricing, reportes) o directamente desde código.
- **Flujo Paso a Paso:**
    1. Se recibe el `producto_receta_id` del plato.
    2. El sistema carga los ingredientes desde `ProductoKit` (tabla `producto_kit`) con relación `variante.producto`.
    3. Para cada ingrediente:
       a. Busca el Stock en la ubicación "Cocina Restaurante" para la variante del ingrediente.
       b. Valida que haya stock disponible (`cantidad_actual > 0`).
       c. Obtiene el `costo_unitario` del Lote asociado al Stock.
       d. Calcula: `costo_total_ingrediente = cantidad_necesaria × costo_unitario`.
    4. Suma todos los costos de ingredientes = costo total del plato.
    5. Calcula margen sugerido según tramo de costo:
        - Costo < C$50 → margen 70%
        - Costo < C$100 → margen 65%
        - Costo < C$200 → margen 60%
        - Costo ≥ C$200 → margen 55%
    6. Calcula precio sugerido: `costo_total / (1 - margen/100)`.
    7. Retorna desglose por ingrediente con `con_stock: true/false`.

---

## 4. Submódulo / Funcionalidad: Registro de Proceso de Cocina

- **Descripción de la Pantalla / Vista:** Formulario de trazabilidad de preparación. Registra plato, cantidad producida, observaciones e ingredientes/materia prima usada para costo técnico. No transforma materia prima ni debe sustituir al flujo de inventario.
- **Disparador (Trigger):** Acceso desde `Restaurante > Procesos Cocina > Crear`.
- **Flujo Paso a Paso:**
    1. El usuario hace clic en "Crear Proceso".
    2. El usuario selecciona un Plato (solo platos activos con receta asociada).
    3. El usuario ingresa la cantidad de platos a producir.
    4. El usuario escribe observaciones (opcional).
    5. El usuario hace clic en "Crear".
    6. El sistema ejecuta `ProcesarProcesoCocina::guardar()`:
       a. Valida que el plato tenga una receta asociada.
       b. Lee los ingredientes de la receta desde `ProductoKit`.
       c. Para cada ingrediente, busca el Stock en "Cocina Restaurante":
        - Obtiene el Lote asociado y su `costo_unitario`.
        - Calcula: `cantidad_receta × cantidad_platos × costo_unitario`.
          d. Crea el registro `ProcesoCocina` con costo total calculado.
          e. Crea un `ProcesoItem` por ingrediente con el costo asignado.
          f. Registra `ProcesoCocina` y `ProcesoItem` como trazabilidad de preparación.
    7. El consumo real de inventario del pedido ocurre al enviar/preparar la comanda desde KDS mediante `ConsumirIngredientesPedido`.

---

## 4.1. Submódulo / Funcionalidad: Materia Prima Cocina

- **Descripción de la Pantalla / Vista:** Página `Restaurante > Materia Prima Cocina`. Permite transformar material bruto en materia prima lista y registrar merma final.
- **Disparador (Trigger):** Acceso directo desde navegación Restaurante o desde el modal de faltantes del KDS.
- **Flujo Paso a Paso:**
    1. El usuario selecciona producto, variante y ubicación origen del material bruto.
    2. Ingresa la cantidad bruta a transformar.
    3. Registra uno o más resultados:
        - materia prima obtenida, con variante y ubicación destino;
        - merma final, marcada como `es_merma`.
    4. El sistema ejecuta `TransformarMateriaPrimaCocina::ejecutar()`.
    5. El sistema descuenta stock del bruto.
    6. El sistema crea o actualiza stock de la materia prima destino.
    7. El sistema registra movimientos:
        - `TRANSFORMACION_SALIDA`
        - `TRANSFORMACION_ENTRADA`
        - `MERMA_COCINA`

---

## 4.2. Submódulo / Funcionalidad: Conciliación de Recetas

- **Descripción de la Pantalla / Vista:** Página `Restaurante > Conciliación Recetas`. Muestra diagnóstico de platos activos, receta, stock de materia prima y reglas de transformación.
- **Disparador (Trigger):** Revisión operativa antes de servicio o cuando cocina reporta faltantes.
- **Flujo Paso a Paso:**
    1. El sistema lee platos activos y sus ingredientes desde `producto_kit`.
    2. Busca stock de cada materia prima en Cocina.
    3. Si falta materia prima, busca una regla en `restaurante_recetas_transformacion_materia_prima`.
    4. Si hay regla, calcula cuánto material bruto se necesita.
    5. Clasifica cada ingrediente como `ok`, `puede_transformarse`, `falta_bruto`, `sin_regla_transformacion`, `receta_incompleta` o `variante_invalida`.
    6. El usuario puede crear nuevas reglas de transformación desde la acción `Nueva regla`.

---

## 5. Submódulo / Funcionalidad: Listado de Procesos de Cocina

- **Descripción de la Pantalla / Vista:** Tabla con todos los procesos registrados. Muestra: código, nombre del plato, cantidad de platos, receta (producto origen), costo total (C$), realizado por, y fecha.
- **Disparador (Trigger):** Acceso desde `Restaurante > Procesos Cocina` en el panel admin.
- **Flujo Paso a Paso:**
    1. El usuario accede a la interfaz de Procesos Cocina y visualiza la tabla paginada.
    2. El sistema valida los permisos y carga los procesos con relaciones `plato`, `productoOrigen`, `realizadoPor`.
    3. El usuario puede buscar por código, plato o receta.
    4. El usuario hace clic en el botón de acciones (⋮) para Editar, Eliminar o Restaurar un proceso.

---

## 6. Submódulo / Funcionalidad: Crear / Editar Pedido

- **Descripción de la Pantalla / Vista:** Formulario con: código, mesa (select de Espacios tipo 'mesa'), mesero (select de Colaboradores), estado (select enum `EstadoPedido`), total (auto-calculado, deshabilitado), notas, y repeater de items del pedido (plato, cantidad, precio unitario, notas del item).
- **Disparador (Trigger):** Acceso desde `Restaurante > Pedidos > Crear` o desde el KDS.
- **Flujo Paso a Paso:**
    1. El usuario hace clic en "Crear Pedido".
    2. El usuario selecciona la mesa (select con espacios tipo 'mesa').
    3. El sistema muestra el estado por defecto: "Abierto".
    4. El usuario opcionalmente selecciona el mesero (colaborador con persona).
    5. El usuario agrega platos al pedido desde el repeater:
        - Selecciona el plato (solo activos).
        - Ingresa cantidad (default 1).
        - Ingresa precio unitario.
        - Agrega notas especiales (ej: "Sin cebolla, termino medio...").
    6. El sistema calcula el total automáticamente.
    7. El usuario hace clic en "Crear".
    8. ¿Se cumple con las validaciones?
        - Si es **No**, muestra errores en los campos correspondientes.
        - Si es **Sí**, crea el pedido y dispara el `PedidoObserver`:
            - `created`: Cambia estado de la mesa a `Ocupado`, crea solicitud de limpieza (prioridad normal).
    9. El usuario puede imprimir la comanda desde `GET /admin/restaurante/pedidos/{pedido}/comanda`.

---

## 7. Submódulo / Funcionalidad: Consumo de Ingredientes por Pedido (KDS)

- **Descripción de la Pantalla / Vista:** Página KDS (Kitchen Display System) que muestra pedidos activos. Al marcar un item como "Listo", se consumen los ingredientes del stock de cocina.
- **Disparador (Trigger):** Acceso desde `Restaurante > Cocina` o clic en "Listo" desde el KDS.
- **Flujo Paso a Paso:**
    1. El cocinero visualiza los pedidos activos en el KDS.
    2. Al preparar un plato, marca el item como "Listo".
    3. El sistema ejecuta `ConsumirIngredientesPedido::ejecutar($pedidoItem)`:
       a. Obtiene el Plato del item y su receta (`producto_receta_id`).
       b. Lee los ingredientes desde `ProductoKit`.
       c. Para cada ingrediente, calcula la cantidad a consumir: `(cantidad_ingrediente / rendimiento_porciones_receta) × cantidad_pedido`.
       d. Decrementa el Stock en "Cocina Restaurante" para cada ingrediente.
       e. Registra un `MovimientoStock` de tipo `CONSUMO`.
    4. El sistema actualiza el estado del item a "Listo".
    5. Cuando todos los items del pedido están listos, el pedido pasa a estado "Listo".
    6. Si falta materia prima al enviar o reenviar la comanda, `BloquearItemsPorFaltaStock` deja los items afectados en estado `Bloqueado por Stock` y registra `bloqueo_stock_detalle`.
    7. El mesero confirma con el cliente y usa la acción `Resolver Faltantes` en editar pedido para sustituir ingrediente, cambiar platillo, quitar item o cancelar el pedido.
    8. Si la falta debe resolverse con material bruto, se usa `Conciliación Recetas` y luego `Materia Prima Cocina` para transformar bruto a materia prima antes de reenviar el item.

---

## 8. Submódulo / Funcionalidad: Reportes del Restaurante

- **Descripción de la Pantalla / Vista:** Dashboard con: selector de rango de fechas, 4 tarjetas KPI (total pedidos, total facturado, pedidos pagados, pedidos pendientes), ranking Top 10 platos más vendidos, ingresos por categoría con barras de progreso, tabla completa de pedidos con polling cada 30 segundos.
- **Disparador (Trigger):** Acceso desde `Restaurante > Reportes` en el panel admin.
- **Flujo Paso a Paso:**
    1. El usuario accede a la página de reportes.
    2. El sistema carga los datos del mes actual por defecto.
    3. El usuario puede filtrar por rango de fechas (fecha inicio / fecha fin).
    4. El sistema consulta los pedidos en el rango seleccionado con relaciones `items.plato`, `mesa`.
    5. Calcula KPIs: total pedidos, total facturado, pagados (estado `Pagado`), pendientes (abierto + preparación).
    6. Genera ranking Top 10 platos por cantidad vendida.
    7. Agrupa ingresos por categoría del plato.
    8. Muestra la tabla completa de pedidos con código, mesa, mesero, estado (badge), total (C$), fecha.
    9. La página se actualiza automáticamente cada 30 segundos (polling Livewire).

---

## 9. Submódulo / Funcionalidad: Impresión de Comanda

- **Descripción de la Pantalla / Vista:** Ticket térmico de 80mm para cocina. Muestra: código del pedido, mesa, fecha/hora, items (cantidad × nombre del plato + notas), total. Se auto-imprime al cargar la página.
- **Disparador (Trigger):** Navegación a `GET /admin/restaurante/pedidos/{pedido}/comanda` (requiere autenticación).
- **Flujo Paso a Paso:**
    1. El usuario (mesero/cocinero) accede a la URL de comanda.
    2. El sistema ejecuta `ComandaController::imprimir($pedido)`.
    3. Carga el pedido con relaciones `items.plato`, `mesa`.
    4. Renderiza la vista Blade `restaurante.comanda` formateada para impresora térmica.
    5. La página ejecuta `window.print()` automáticamente al cargar.
    6. Excluye items cancelados del ticket.

---

## 10. Submódulo / Funcionalidad: Portal Web del Restaurante

- **Descripción de la Pantalla / Vista:** Página pública `/restaurante` con: hero banner del restaurante, ambientes con mesas disponibles, menú del día organizado por categorías (Entradas, Platos, Postres, Bebidas), precios y imágenes de cada plato.
- **Disparador (Trigger):** Navegación del huésped al menú "Restaurante" desde el header del sitio.
- **Flujo Paso a Paso:**
    1. El usuario navega a `/restaurante` desde el header del sitio público.
    2. El sistema ejecuta `ObtenerRestauranteLanding::ejecutar()`.
    3. El Interactor busca el Espacio de tipo `restaurante` con ambientes y mesas.
    4. Consulta platos activos con `web = true`, incluyendo precios, imágenes y categoría.
    5. Organiza el menú por categorías usando `CategoriaPlato` enum.
    6. Renderiza la página Inertia `restaurante/Restaurante` con todos los datos.
    7. El huésped visualiza: información del restaurante, ambientes, mesas disponibles, y menú completo.

---

## 11. Submódulo / Funcionalidad: Observer de Pedido (Automático)

- **Descripción de la Pantalla / Vista:** Proceso automático que gestiona el estado de las mesas y solicitudes de limpieza al crear o actualizar pedidos.
- **Disparador (Trigger):** Automático al crear o actualizar un Pedido.
- **Flujo Paso a Paso:**
    1. Al **crear** un Pedido (`PedidoObserver::created`):
       a. Cambia el estado de la mesa a `Ocupado` (Enum `EstadoEspacio`).
       b. Crea una solicitud de limpieza con prioridad `normal` via `RegistrarSolicitudLimpieza`.
    2. Al **actualizar** un Pedido (`PedidoObserver::updated`):
       a. Si el estado cambia a `Pagado` o `Cancelado`:
        - Cambia el estado de la mesa a `Limpieza`.
        - Crea una solicitud de limpieza con prioridad `urgente`.

---

## Base de Datos del Módulo

### Tablas Propias del Módulo

#### `platos`

| Columna              | Tipo         | Restricciones                  | Descripción                                    |
| -------------------- | ------------ | ------------------------------ | ---------------------------------------------- |
| `id`                 | bigint       | PK, auto                       | Identificador único                            |
| `codigo`             | varchar(20)  | UNIQUE                         | Código del plato (PLT-XXXX)                    |
| `nombre`             | varchar(100) | NOT NULL                       | Nombre del plato                               |
| `categoria_id`       | bigint       | FK → catalogos, NULL ON DELETE | Categoría (Entradas, Platos, Postres, Bebidas) |
| `producto_receta_id` | bigint       | FK → productos, NULL ON DELETE | Receta asociada (Producto padre)               |
| `descripcion`        | text         | NULLABLE                       | Descripción del plato                          |
| `web`                | boolean      | DEFAULT false                  | Visible en portal web                          |
| `estado`             | int          | DEFAULT 1                      | 1=Activo, 0=Inactivo                           |
| `tiempo_preparacion` | varchar(50)  | NULLABLE                       | Ej: "15 - 25 min"                              |
| `created_at`         | timestamp    |                                | Fecha de creación                              |
| `updated_at`         | timestamp    |                                | Fecha de actualización                         |
| `deleted_at`         | timestamp    | NULLABLE                       | Soft deletes                                   |

#### `pedidos`

| Columna      | Tipo          | Restricciones                      | Descripción                            |
| ------------ | ------------- | ---------------------------------- | -------------------------------------- |
| `id`         | bigint        | PK, auto                           | Identificador único                    |
| `codigo`     | varchar(20)   | UNIQUE                             | Código del pedido                      |
| `mesa_id`    | bigint        | FK → espacios, CASCADE ON DELETE   | Mesa asignada                          |
| `mesero_id`  | bigint        | FK → colaboradores, NULL ON DELETE | Mesero asignado                        |
| `cliente_id` | bigint        | FK → personas, NULL ON DELETE      | Cliente asociado                       |
| `estado`     | varchar(30)   | DEFAULT 'abierto'                  | Estado del pedido (ver `EstadoPedido`) |
| `total`      | decimal(10,2) | DEFAULT 0                          | Total del pedido                       |
| `abierto_en` | timestamp     | NULLABLE                           | Hora de apertura                       |
| `cerrado_en` | timestamp     | NULLABLE                           | Hora de cierre                         |
| `notas`      | text          | NULLABLE                           | Observaciones del pedido               |
| `created_at` | timestamp     |                                    | Fecha de creación                      |
| `updated_at` | timestamp     |                                    | Fecha de actualización                 |
| `deleted_at` | timestamp     | NULLABLE                           | Soft deletes                           |

**Índices:** `mesa_id`, `estado`

#### `pedido_items`

| Columna                 | Tipo          | Restricciones                   | Descripción                                                     |
| ----------------------- | ------------- | ------------------------------- | --------------------------------------------------------------- |
| `id`                    | bigint        | PK, auto                        | Identificador único                                             |
| `pedido_id`             | bigint        | FK → pedidos, CASCADE ON DELETE | Pedido padre                                                    |
| `plato_id`              | bigint        | FK → platos, NULL ON DELETE     | Plato pedido                                                    |
| `cantidad`              | decimal(10,2) | DEFAULT 1                       | Cantidad ordenada                                               |
| `precio_unitario`       | decimal(10,2) | NOT NULL                        | Precio unitario al momento del pedido                           |
| `subtotal`              | decimal(10,2) | NOT NULL                        | Subtotal (cantidad × precio_unitario)                           |
| `estado`                | varchar(20)   | DEFAULT 'pendiente'             | Estado del item (pendiente/preparacion/listo/servido/cancelado) |
| `bloqueo_stock_detalle` | json          | NULLABLE                        | Ingredientes faltantes que bloquean el item                     |
| `bloqueado_stock_en`    | timestamp     | NULLABLE                        | Fecha/hora del bloqueo por stock                                |
| `notas`                 | text          | NULLABLE                        | Notas especiales (ej: "Sin cebolla")                            |
| `created_at`            | timestamp     |                                 | Fecha de creación                                               |
| `updated_at`            | timestamp     |                                 | Fecha de actualización                                          |

#### `procesos_cocina`

| Columna              | Tipo          | Restricciones                           | Descripción                              |
| -------------------- | ------------- | --------------------------------------- | ---------------------------------------- |
| `id`                 | bigint        | PK, auto                                | Identificador único                      |
| `codigo`             | varchar(20)   | UNIQUE                                  | Código del proceso                       |
| `plato_id`           | bigint        | FK → platos, NULL ON DELETE             | Plato cuya receta se procesa             |
| `cantidad_platos`    | smallint      | NULLABLE                                | Número de platos a producir              |
| `producto_origen_id` | bigint        | FK → productos, CASCADE ON DELETE       | Producto receta (origen)                 |
| `variante_origen_id` | bigint        | FK → producto_variantes, NULL ON DELETE | Variante origen (opcional)               |
| `cantidad_procesada` | decimal(10,3) | NOT NULL                                | Cantidad procesada (= cantidad_platos)   |
| `costo_total`        | decimal(10,2) | NOT NULL                                | Costo total calculado desde Stock → Lote |
| `realizado_por`      | bigint        | FK → users, NULL ON DELETE              | Usuario que realizó el proceso           |
| `observaciones`      | text          | NULLABLE                                | Notas del proceso                        |
| `created_at`         | timestamp     |                                         | Fecha de creación                        |
| `updated_at`         | timestamp     |                                         | Fecha de actualización                   |
| `deleted_at`         | timestamp     | NULLABLE                                | Soft deletes                             |

#### `proceso_items`

| Columna                | Tipo          | Restricciones                           | Descripción                                     |
| ---------------------- | ------------- | --------------------------------------- | ----------------------------------------------- |
| `id`                   | bigint        | PK, auto                                | Identificador único                             |
| `proceso_id`           | bigint        | FK → procesos_cocina, CASCADE ON DELETE | Proceso padre                                   |
| `producto_destino_id`  | bigint        | FK → productos, CASCADE ON DELETE       | Ingrediente/resultante                          |
| `variante_destino_id`  | bigint        | FK → producto_variantes, NULL ON DELETE | Variante del ingrediente                        |
| `cantidad`             | decimal(10,3) | NOT NULL                                | Cantidad (receta × platos)                      |
| `peso_unitario`        | decimal(8,3)  | NULLABLE                                | Peso unitario de la variante                    |
| `peso_total`           | decimal(8,3)  | NULLABLE                                | Peso total del ingrediente                      |
| `costo_asignado`       | decimal(10,2) | NOT NULL                                | Costo asignado (cantidad × costo_unitario lote) |
| `es_merma`             | boolean       | DEFAULT false                           | Si es merma/pérdida                             |
| `ubicacion_destino_id` | bigint        | FK → ubicaciones, NULL ON DELETE        | Ubicación destino                               |
| `created_at`           | timestamp     |                                         | Fecha de creación                               |
| `updated_at`           | timestamp     |                                         | Fecha de actualización                          |

**Índice:** `proceso_id`

### Tablas Compartidas Utilizadas

| Tabla             | Relación                                                                     | Uso en Restaurante                                |
| ----------------- | ---------------------------------------------------------------------------- | ------------------------------------------------- |
| `precios`         | Polimórfica (`priceable`)                                                    | Precios de platos (TipoPrecioEspacio, moneda NIO) |
| `imagenes`        | Polimórfica (`imagenable`)                                                   | Galería de imágenes de platos                     |
| `politicaable`    | Pivot polimórfico                                                            | Políticas asociadas a platos                      |
| `stocks`          | Polimórfica (`stockable`)                                                    | Stock en "Cocina Restaurante" (variante + lote)   |
| `producto_kit`    | `producto_padre_id`                                                          | Ingredientes de la receta (variante + cantidad)   |
| `inv_movimientos` | `lote_id`, `producto_id`                                                     | Movimientos de tipo CONSUMO al cocinar            |
| `inv_lotes`       | `costo_unitario`                                                             | Costo unitario de ingredientes                    |
| `catalogos`       | `codigo` en ('REST_ENTRADAS', 'REST_PLATOS', 'REST_POSTRES', 'REST_BEBIDAS') | Categorías de platos                              |
| `espacios`        | `tipo = 'restaurante'`                                                       | Espacio del restaurante, ambientes y mesas        |
| `colaboradores`   | `persona_id`                                                                 | Meseros                                           |
| `personas`        | `id`                                                                         | Clientes del pedido                               |

### Enums

#### `EstadoPedido` (string backed)

| Case          | Value         | Label          | Color Badge |
| ------------- | ------------- | -------------- | ----------- |
| `Abierto`     | `abierto`     | Abierto        | warning     |
| `Preparacion` | `preparacion` | En Preparación | info        |
| `Listo`       | `listo`       | Listo          | success     |
| `Servido`     | `servido`     | Servido        | primary     |
| `Pagado`      | `pagado`      | Pagado         | gray        |
| `Cancelado`   | `cancelado`   | Cancelado      | danger      |

#### `CategoriaPlato` (string backed)

| Case       | Value           | Label          |
| ---------- | --------------- | -------------- |
| `Entradas` | `REST_ENTRADAS` | Entradas       |
| `Platos`   | `REST_PLATOS`   | Platos Fuertes |
| `Postres`  | `REST_POSTRES`  | Postres        |
| `Bebidas`  | `REST_BEBIDAS`  | Bebidas        |
| `General`  | `RESTAURANTE`   | General        |

---

## Arquitectura del Módulo

```
app/
├── Repository/Models/Restaurante/
│   ├── Plato.php                    ← Modelo (tabla platos)
│   ├── Pedido.php                   ← Modelo (tabla pedidos)
│   ├── PedidoItem.php               ← Modelo (tabla pedido_items)
│   ├── ProcesoCocina.php            ← Modelo (tabla procesos_cocina)
│   └── ProcesoItem.php              ← Modelo (tabla proceso_items)
├── Repository/Observers/Restaurante/
│   └── PedidoObserver.php           ← Observer: mesa + limpieza
├── Interactors/Restaurante/
│   ├── GenerarCodigoPlato.php       ← Genera PLT-XXXX
│   ├── RegistrarProcesoCocina.php   ← Crea proceso desde receta
│   ├── ConsumirIngredientesPedido.php ← Consume stock al cocinar
│   ├── CalcularCostoPlato.php       ← Costo desde Stock → Lote
│   └── SincronizarGaleriaPlatoImagenes.php ← Sync imágenes
├── Interactors/Landing/
│   └── ObtenerRestauranteLanding.php ← Datos para web pública
├── Enums/Restaurante/
│   ├── EstadoPedido.php             ← Enum estados de pedido
│   └── CategoriaPlato.php           ← Enum categorías del menú
├── Filament/Resources/Restaurante/
│   ├── PlatoResource/               ← CRUD Platos
│   │   ├── PlatoResource.php
│   │   ├── Schemas/PlatoForm.php
│   │   ├── Schemas/PlatoInfolist.php
│   │   ├── Tables/PlatoTable.php
│   │   └── Pages/{List,Create,Edit,View}Plato.php
│   ├── PedidoResource/              ← CRUD Pedidos
│   │   ├── PedidoResource.php
│   │   ├── Schemas/PedidoForm.php
│   │   ├── Tables/PedidoTable.php
│   │   └── Pages/{List,Create,Edit}Pedido.php
│   └── ProcesoCocinaResource/       ← CRUD Procesos Cocina
│       ├── ProcesoCocinaResource.php
│       ├── Schemas/ProcesoCocinaForm.php
│       ├── Tables/ProcesoCocinaTable.php
│       └── Pages/{List,Create,Edit}ProcesoCocina.php
├── Filament/Pages/Restaurante/
│   └── ReportesRestaurante.php      ← Dashboard de reportes
├── Http/Controllers/Restaurante/
│   └── ComandaController.php        ← Impresión térmica
├── Filament/Shared/RelationManagers/
│   ├── PreciosRelationManager.php   ← Precios polimórficos
│   └── PoliticasRelationManager.php ← Políticas polimórficas
├── resources/views/
│   ├── restaurante/comanda.blade.php ← Ticket térmico 80mm
│   └── filament/pages/reportes-restaurante.blade.php
├── database/migrations/Restaurante/
│   ├── 2026_07_21_003924_create_procesos_cocina_table.php
│   ├── 2026_07_21_003927_create_proceso_items_table.php
│   ├── 2026_07_21_003928_create_pedidos_table.php
│   ├── 2026_07_21_003930_create_pedido_items_table.php
│   ├── 2026_07_21_100000_create_platos_table.php
│   ├── 2026_07_21_100001_migrate_servicios_to_platos.php
│   └── 2026_07_21_220000_add_plato_and_cantidad_platos_to_procesos_cocina_table.php
├── database/seeders/
│   ├── RestauranteSeeder.php        ← Ubicación, espacios, mesas
│   ├── MenuRestauranteSeeder.php    ← Categorías, platos, recetas
│   └── PedidoRestauranteSeeder.php  ← Pedidos demo
└── tests/Feature/Espacios/
    └── RestauranteLandingTest.php   ← Test página pública
```

---

## Diagrama de Flujo: Registro de Proceso de Cocina

```
┌─────────────────────┐
│  Usuario selecciona  │
│  Plato + Cantidad    │
│  de platos           │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  RegistrarProcesoCocina│
│  ::ejecutar()        │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐     ┌──────────────────┐
│  Busca receta del    │────▶│  ProductoKit     │
│  plato               │     │  (ingredientes)  │
└──────────┬──────────┘     └──────────────────┘
           │
           ▼
┌─────────────────────┐     ┌──────────────────┐
│  Para cada ingrediente│────▶│  Stock en        │
│  busca Stock Cocina  │     │  "Cocina Rest."  │
└──────────┬──────────┘     └────────┬─────────┘
           │                         │
           ▼                         ▼
┌─────────────────────┐     ┌──────────────────┐
│  Obtiene Lote →      │     │  Valida          │
│  costo_unitario      │     │  cantidad > 0    │
└──────────┬──────────┘     └──────────────────┘
           │
           ▼
┌─────────────────────┐
│  Crea ProcesoCocina  │
│  + ProcesoItems      │
│  (costo calculado)   │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐     ┌──────────────────┐
│  Decrementa Stock    │────▶│  MovimientoStock │
│  Cocina Restaurante  │     │  tipo: CONSUMO   │
└──────────┬──────────┘     └──────────────────┘
           │
           ▼
┌─────────────────────┐
│  Redirige a Editar   │
│  (usuario marca      │
│   merma si aplica)   │
└─────────────────────┘
```
