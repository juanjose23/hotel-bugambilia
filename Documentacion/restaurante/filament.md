# Filament del Módulo Restaurante

## Descripción General

El módulo Restaurante utiliza Filament 5 para la capa administrativa. Incluye Resources para CRUD de entidades principales y Pages para funcionalidades especiales como gestión de mesas, reportes y configuración.

## Resources

### 1. PlatoResource

**Ubicación**: `app/Filament/Resources/Restaurante/PlatoResource/`

**Responsabilidad**: CRUD completo de platos del menú.

#### Estructura

```
PlatoResource/
├── PlatoResource.php           # Definición del resource
├── Pages/
│   ├── ListPlatos.php          # Listado de platos
│   ├── CreatePlato.php         # Crear plato
│   ├── EditPlato.php           # Editar plato
│   └── ViewPlato.php           # Ver detalle de plato
├── Schemas/
│   ├── PlatoForm.php           # Formulario de creación/edición
│   └── PlatoInfolist.php        # Vista de detalle
└── Tables/
    └── PlatoTable.php          # Configuración de tabla
```

#### Características

- **Tabla**: Muestra código, nombre, categoría (badge), estado (badge), visibilidad web (ícono), fecha de creación
- **Filtros**: Por categoría, visibilidad web, estado
- **Formulario**:
  - Código auto-generado (PLT-XXXX)
  - Nombre, categoría, receta asociada
  - Descripción, tiempo de preparación
  - Toggle de visibilidad web
  - Galería de imágenes (máximo 3, reordenable)
- **Relation Managers**:
  - `PreciosRelationManager`: Precios polimórficos
  - `PoliticasRelationManager`: Políticas polimórficas

#### Acciones

- Crear plato (ejecuta `GenerarCodigoPlato`)
- Editar plato
- Eliminar plato (soft delete)
- Restaurar plato
- Ver detalle

#### Interactors Utilizados

- `GenerarCodigoPlato`: Genera código PLT-XXXX
- `SincronizarGaleriaPlatoImagenes`: Sincroniza imágenes

---

### 2. PedidoResource

**Ubicación**: `app/Filament/Resources/Restaurante/PedidoResource/`

**Responsabilidad**: CRUD de pedidos de restaurante.

#### Estructura

```
PedidoResource/
├── PedidoResource.php           # Definición del resource
├── Pages/
│   ├── ListPedidos.php          # Listado de pedidos
│   ├── CreatePedido.php         # Crear pedido
│   └── EditPedido.php           # Editar pedido
├── Schemas/
│   └── PedidoForm.php           # Formulario de creación/edición
└── Tables/
    └── PedidoTable.php          # Configuración de tabla
```

#### Características

- **Tabla**: Muestra código, mesa, mesero, estado (badge), total, fecha
- **Filtros**: Por estado, mesa, mesero, rango de fechas
- **Formulario**:
  - Código auto-generado (PED-YYYYMMDD-XXXX)
  - Mesa (select de espacios tipo 'mesa')
  - Mesero (select de colaboradores)
  - Estado (select enum EstadoPedido)
  - Total (auto-calculado, deshabilitado)
  - Notas
  - Repeater de items (plato, cantidad, precio unitario, notas)

#### Acciones

- Crear pedido
- Editar pedido
- Imprimir comanda (redirige a ruta de impresión)
- Cerrar pedido
- Aplicar descuento

#### Interactors Utilizados

- `AbrirPedidoMesa`: Abre nuevo pedido
- `CerrarPedidoMesa`: Cierra pedido y calcula totales

---

### 3. ProcesoCocinaResource

**Ubicación**: `app/Filament/Resources/Restaurante/ProcesoCocinaResource/`

**Responsabilidad**: CRUD de procesos de cocina (producción de platos).

#### Estructura

```
ProcesoCocinaResource/
├── ProcesoCocinaResource.php    # Definición del resource
├── Pages/
│   ├── ListProcesosCocina.php   # Listado de procesos
│   ├── CreateProcesoCocina.php  # Crear proceso
│   └── EditProcesoCocina.php    # Editar proceso
├── Schemas/
│   └── ProcesoCocinaForm.php    # Formulario de creación/edición
└── Tables/
    └── ProcesoCocinaTable.php   # Configuración de tabla
```

#### Características

- **Tabla**: Muestra código, nombre del plato, cantidad de platos, costo total, realizado por, fecha
- **Filtros**: Por plato, rango de fechas
- **Formulario**:
  - Código
  - Plato (select, solo platos con receta)
  - Cantidad de platos a producir
  - Observaciones
  - Sección de ingredientes (repeater, auto-generado desde receta)

#### Acciones

- Crear proceso (ejecuta `RegistrarProcesoCocina`)
- Editar proceso (para marcar merma)
- Eliminar proceso
- Ver detalle

#### Interactors Utilizados

- `RegistrarProcesoCocina`: Registra proceso y consume stock

---

### 4. AuditoriaRestauranteResource

**Ubicación**: `app/Filament/Resources/Restaurante/AuditoriaRestauranteResource/`

**Responsabilidad**: Visualización de auditoría de acciones críticas.

#### Estructura

```
AuditoriaRestauranteResource/
├── AuditoriaRestauranteResource.php
└── Pages/
    └── ListAuditoriaRestaurantes.php
```

#### Características

- **Tabla**: Muestra acción, mesa, pedido, usuario, detalles, IP, fecha
- **Filtros**: Por acción, usuario, rango de fechas
- **Solo lectura**: No permite crear ni editar registros

#### Policy

- `AuditoriaRestaurantePolicy`: Controla quién puede ver la auditoría

---

## Pages

### 1. GestionMesas

**Ubicación**: `app/Filament/Pages/Restaurante/GestionMesas.php`

**Responsabilidad**: Mapa interactivo de mesas con funcionalidad POS.

#### Características

- **Mapa de mesas**: Visualización gráfica de mesas por ambientes
- **Estados visuales**: Colores según estado (Disponible, Ocupado, Limpieza, Reservado, Mantenimiento)
- **Acciones por mesa**:
  - Cambiar estado manualmente
  - Ver pedido activo
  - Abrir nuevo pedido
  - Mover cuenta a otra mesa
  - Aplicar descuento
  - Imprimir/reimprimir comanda
- **Unión de mesas**: Seleccionar mesa principal y mesas secundarias
- **Separación de mesas**: Desvincular mesas unidas
- **Reservas**: Mostrar reservas del restaurante

#### Interactors Utilizados

- `CambiarEstadoMesa`: Cambia estado de mesa
- `UnirMesas`: Une múltiples mesas
- `SepararMesas`: Separa mesas unidas
- `MoverCuentaMesa`: Traslada cuenta entre mesas
- `AplicarDescuentoCuenta`: Aplica descuento a pedido
- `ReimprimirComanda`: Reimprime comanda

#### BusinessLogic Utilizados

- `RegistrarAuditoriaRestaurante`: Registra acciones
- `VerificarRestauranteActivo`: Verifica que el restaurante esté activo

#### Queries Utilizados

- `ObtenerMapaMesasQuery`: Obtiene mapa de mesas y ambientes
- `ObtenerReservasRestauranteQuery`: Obtiene reservas del restaurante

#### Vista

- `resources/views/filament/pages/gestion-mesas.blade.php`

---

### 2. ConfiguracionRestaurante

**Ubicación**: `app/Filament/Pages/Restaurante/ConfiguracionRestaurante.php`

**Responsabilidad**: Configuración general del restaurante y POS.

#### Características

- **Formulario**:
  - Propina sugerida (%)
  - Impuesto porcentaje (%)
  - Impresora cocina (nombre)
  - Impresora bar (nombre)
  - Impresora postres (nombre)
  - Impresora parrilla (nombre)
  - Impresión automática (toggle)
  - Copias de ticket (número)

#### Almacenamiento

- Configuración se guarda en `meta_datos` del Espacio principal del restaurante

#### Interactors Utilizados

- `RegistrarAuditoriaRestaurante`: Registra cambios de configuración

#### Vista

- `resources/views/filament/pages/configuracion-restaurante.blade.php`

---

### 3. ReportesRestaurante

**Ubicación**: `app/Filament/Pages/Restaurante/ReportesRestaurante.php`

**Responsabilidad**: Dashboard de métricas y reportes del restaurante.

#### Características

- **Selector de rango de fechas**
- **KPIs**:
  - Total de pedidos
  - Total facturado
  - Pedidos pagados
  - Pedidos pendientes
- **Ranking Top 10**: Platos más vendidos
- **Ingresos por categoría**: Barras de progreso por categoría
- **Tabla de pedidos**: Listado completo con polling cada 30 segundos

#### BusinessLogic Utilizados

- `CalcularReportesRestaurante`: Calcula métricas

#### Queries Utilizados

- `ObtenerReportesRestauranteQuery`: Obtiene datos para reportes

#### Vista

- `resources/views/filament/pages/reportes-restaurante.blade.php`

---

### 4. CocinaPedidos

**Ubicación**: `app/Filament/Pages/Restaurante/CocinaPedidos.php`

**Responsabilidad**: KDS (Kitchen Display System) para visualización de pedidos en cocina.

#### Características

- **Visualización de pedidos activos**: Agrupados por área de cocina
- **Estados de items**: Pendiente, En preparación, Listo
- **Acciones**:
  - Marcar item como listo
  - Marcar pedido como servido
- **Filtros**: Por área de cocina (Cocina, Bar, Postres, Parrilla)
- **Polling**: Actualización automática cada 10 segundos

#### Interactors Utilizados

- `MarcarItemPedidoListo`: Marca item listo y consume ingredientes
- `ConsumirIngredientesPedido`: Consume stock de cocina

---

### 5. AutoPedido

**Ubicación**: `app/Filament/Pages/Restaurante/AutoPedido.php`

**Responsabilidad**: Interfaz para pedidos desde kiosko auto-servicio.

#### Características

- **Selección de platos**: Desde catálogo visual
- **Carrito de compras**: Items seleccionados
- **Confirmación**: Crea pedido sin mesa asignada
- **Cliente temporal**: Asigna cliente genérico

#### Interactors Utilizados

- `ConfirmarPedidoKiosko`: Confirma pedido de kiosko
- `AsignarClienteTemporal`: Asigna cliente temporal

---

### 6. PantallaPedidos

**Ubicación**: `app/Filament/Pages/Restaurante/PantallaPedidos.php`

**Responsabilidad**: Pantalla pública para mostrar pedidos en TV/monitor.

#### Características

- **Visualización simplificada**: Solo información esencial
- **Ciclo automático**: Rotación entre pedidos
- **Sin interacción**: Solo visualización

---

## Relation Managers

### PreciosRelationManager

**Ubicación**: `app/Filament/Shared/RelationManagers/PreciosRelationManager.php`

**Responsabilidad**: Gestión de precios polimórficos (usado por Platos).

#### Características

- Repeater para agregar múltiples precios
- Campos: moneda, tipo de precio, monto, fecha inicio/fin, estado
- Validación de precios duplicados activos

---

### PoliticasRelationManager

**Ubicación**: `app/Filament/Shared/RelationManagers/PoliticasRelationManager.php`

**Responsabilidad**: Gestión de políticas polimórficas (usado por Platos).

#### Características

- Select para asociar políticas existentes
- Attach/detach con soft deletes en pivot

---

## Permisos (Filament Shield)

### Resource Permissions

- `view_any_platos`: Ver listado de platos
- `view_platos`: Ver detalle de plato
- `create_platos`: Crear platos
- `edit_platos`: Editar platos
- `delete_platos`: Eliminar platos
- `restore_platos`: Restaurar platos eliminados
- `force_delete_platos`: Eliminar permanentemente

- `view_any_pedidos`: Ver listado de pedidos
- `view_pedidos`: Ver detalle de pedido
- `create_pedidos`: Crear pedidos
- `edit_pedidos`: Editar pedidos
- `delete_pedidos`: Eliminar pedidos
- `restore_pedidos`: Restaurar pedidos eliminados

- `view_any_procesos_cocina`: Ver listado de procesos
- `view_procesos_cocina`: Ver detalle de proceso
- `create_procesos_cocina`: Crear procesos
- `edit_procesos_cocina`: Editar procesos
- `delete_procesos_cocina`: Eliminar procesos

- `view_any_auditoria_restaurante`: Ver auditoría

### Page Permissions

- `page_GestionMesas`: Acceder a gestión de mesas
- `page_ConfiguracionRestaurante`: Configurar restaurante
- `page_ReportesRestaurante`: Ver reportes
- `page_CocinaPedidos`: Acceder a KDS
- `page_AutoPedido`: Acceder a auto-pedido

## Reglas de Filament en el Módulo

### Separación de Responsabilidades

**Filament NO debe contener**:
- Lógica de negocio compleja
- Queries directas a base de datos
- Cálculos complejos
- Reglas de aprobación

**Filament DEBE**:
- Capturar datos del usuario
- Mostrar información
- Ejecutar Interactors
- Manejar validaciones de formulario

### Ejemplo Correcto

```php
// En Filament Resource
public static function table(Table $table): Table
{
    return $table
        ->actions([
            Action::make('cerrar')
                ->action(function (Pedido $pedido) {
                    app(CerrarPedidoMesa::class)->ejecutar($pedido->id);
                }),
        ]);
}
```

### Ejemplo Incorrecto

```php
// NO HACER ESTO EN FILAMENT
Action::make('cerrar')
    ->action(function (Pedido $pedido) {
        $pedido->update(['estado' => 'pagado']);
        $pedido->mesa->update(['estado' => 'limpieza']);
        // Lógica de negocio en Filament
    });
```

## Componentes Personalizados

### Badge de Estado

```php
Badge::make('estado')
    ->color(fn ($state) => $state->getColor())
    ->icon(fn ($state) => $state->getIcon());
```

### Select de Categorías

```php
Select::make('categoria_id')
    ->options(CategoriaPlato::options())
    ->default(CategoriaPlato::General->value);
```

### FileUpload de Imágenes

```php
FileUpload::make('imagenes')
    ->multiple()
    ->maxFiles(3)
    ->directory('restaurante/platos')
    ->reorderable();
```

## Vistas Blade

### Comanda Térmica

**Ubicación**: `resources/views/restaurante/comanda.blade.php`

**Descripción**: Ticket térmico de 80mm para impresión de comandas.

**Características**:
- Formato para impresora térmica
- Auto-imprime al cargar (`window.print()`)
- Muestra: código, mesa, fecha/hora, items, total
- Excluye items cancelados

**Ruta**: `GET /admin/restaurante/pedidos/{pedido}/comanda`

---

## Estilos y UX

### Colres de Estados

- **Disponible**: Verde (success)
- **Ocupado**: Rojo (danger)
- **Limpieza**: Amarillo (warning)
- **Reservado**: Azul (info)
- **Mantenimiento**: Gris (gray)

### Iconos

- **Mesa**: heroicon-o-table-cells
- **Pedido**: heroicon-o-receipt
- **Cocina**: heroicon-o-fire
- **Comanda**: heroicon-o-printer
- **Reporte**: heroicon-o-chart-bar

### Responsive

- Todas las páginas son responsive
- Mapa de mesas adapta a tamaño de pantalla
- Tablas con scroll horizontal en móviles
