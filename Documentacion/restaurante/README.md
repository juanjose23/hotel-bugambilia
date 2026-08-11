# Módulo Restaurante - Documentación Completa

## Descripción General El módulo Restaurante gestiona todas las operaciones del restaurante del hotel, incluyendo:

- **Gestión de Platos**: Catálogo de platos del menú con precios, imágenes, recetas y categorías
- **Pedidos y Comandas**: Sistema de pedidos por mesa, impresión de comandas térmicas
- **Cuentas de Restaurante**: Cuentas propias para clientes no huéspedes con gestión de pagos
- **Procesos de Cocina**: Producción de platos basada en recetas con consumo de ingredientes
- **Materia Prima Cocina**: Transformación de material bruto en materia prima, con merma e inventario
- **Conciliación de Recetas**: Diagnóstico de recetas contra stock, reglas de transformación y abastecimiento
- **Gestión de Mesas**: Mapa interactivo de mesas, estados, unión/separación de mesas
- **Reportes**: Dashboard de ventas, ranking de platos, métricas de desempeño
- **Portal Web**: Página pública del restaurante con menú visible para huéspedes
- **Configuración POS**: Parámetros de impresión, propinas, impuestos

## Stack Tecnológico

- **Backend**: PHP 8.4+, Laravel 13, Filament 5
- **Frontend**: Inertia.js v3, React 19, TypeScript, TailwindCSS v4
- **Base de Datos**: PostgreSQL (producción), SQLite (pruebas)

## Arquitectura del Módulo

El módulo sigue la arquitectura por capas definida en AGENTS.md:

```
Filament / Controller
        |
        v
Interactor
        |
        v
BusinessLogic
        |
        v
Repository
        |
        v
Model
```

## Estructura de Directorios

```
app/
├── Repository/Models/Restaurante/          # Modelos de dominio
│   ├── AuditoriaRestaurante.php
│   ├── CuentaRestaurante.php
│   ├── PagoRestaurante.php
│   ├── Pedido.php
│   ├── PedidoItem.php
│   ├── Plato.php
│   ├── ProcesoCocina.php
│   └── ProcesoItem.php
├── Repository/Queries/Restaurante/        # Queries de lectura
│   ├── ContarPedidosPorEstadoQuery.php
│   ├── ObtenerCatalogoPlatoQuery.php
│   ├── ObtenerDatosPedidoFormQuery.php
│   ├── ObtenerIngredientesPedidoQuery.php
│   ├── ObtenerMapaMesasQuery.php
│   ├── ObtenerPedidosCocinaQuery.php
│   ├── ObtenerReportesRestauranteQuery.php
│   └── ...
├── Repository/Persistencia/Restaurante/    # Repositorios de escritura
│   ├── RestauranteRepositorioInterface.php
│   └── RestauranteRepositorio.php
├── Repository/Policies/Restaurante/        # Políticas de acceso
│   └── AuditoriaRestaurantePolicy.php
├── Repository/Observers/Restaurante/       # Observadores de modelos
│   └── PedidoObserver.php
├── Interactors/Restaurante/                # Casos de uso
│   ├── AbrirPedidoMesa.php
│   ├── AplicarDescuentoCuenta.php
│   ├── CerrarPedidoMesa.php
│   ├── ConsumirIngredientesPedido.php
│   ├── EnviarPedidoACocina.php
│   ├── MarcarItemPedidoListo.php
│   ├── MoverCuentaMesa.php
│   ├── RegistrarProcesoCocina.php
│   ├── SepararMesas.php
│   ├── UnirMesas.php
│   └── ...
├── BusinessLogic/Restaurante/              # Reglas de negocio
│   ├── CalcularCostoPlato.php
│   ├── CalcularReportesRestaurante.php
│   ├── CalcularTotalesCuenta.php
│   ├── RegistrarAuditoriaRestaurante.php
│   ├── ValidarCapacidadMesasRestaurante.php
│   ├── ValidarDisponibilidadMesa.php
│   ├── VerificarRestauranteActivo.php
│   └── ...
├── Enums/Restaurante/                      # Enums del dominio
│   ├── AccionAuditoriaRestaurante.php
│   ├── AreaCocina.php
│   ├── CategoriaPlato.php
│   ├── EstadoItemPedido.php
│   ├── EstadoPedido.php
│   └── TipoTicketComanda.php
├── Filament/Resources/Restaurante/         # Recursos Filament
│   ├── AuditoriaRestauranteResource/
│   ├── PedidoResource/
│   ├── PlatoResource/
│   └── ProcesoCocinaResource/
├── Filament/Pages/Restaurante/             # Páginas Filament
│   ├── AutoPedido.php
│   ├── CocinaPedidos.php
│   ├── ConfiguracionRestaurante.php
│   ├── GestionMesas.php
│   ├── PantallaPedidos.php
│   └── ReportesRestaurante.php
├── Http/Controllers/Restaurante/           # Controladores
│   ├── ComandaController.php
│   └── RestauranteController.php
├── Listeners/Restaurante/                  # Listeners de eventos
│   └── CrearProcesosCocina.php
├── Events/Restaurante/                     # Eventos del dominio
│   └── (eventos relacionados con pedidos)
└── Actions/Restaurante/                    # Acciones reutilizables
    └── ReimprimirComandaAction.php
```

## Documentación Disponible

- **[flujos.md](./flujos.md)**: Documentación detallada de flujos de procesos paso a paso
- **[arquitectura.md](./arquitectura.md)**: Arquitectura técnica y patrones de diseño
- **[modelos.md](./modelos.md)**: Descripción completa de modelos y relaciones
- **[interactors.md](./interactors.md)**: Casos de uso del módulo
- **[business-logic.md](./business-logic.md)**: Reglas de negocio
- **[enums.md](./enums.md)**: Enums y sus valores
- **[filament.md](./filament.md)**: Recursos y páginas de Filament
- **[integraciones.md](./integraciones.md)**: Integración con otros módulos

## Integración con Otros Módulos

### Inventario

- **Stock**: Consumo de ingredientes desde "Cocina Restaurante"
- **ProductoKit**: Recetas de platos (ingredientes medidos contra el rendimiento de porciones de la receta)
- **Lotes**: Costo unitario de ingredientes
- **Movimientos**: Registro de CONSUMO al cocinar

### Espacios

- **Espacios**: Mesas del restaurante (tipo 'mesa')
- **Ambientes**: Agrupación de mesas por área
- **Estados**: Disponible, Ocupado, Limpieza, Reservado, etc.

### Limpieza

- **SolicitudLimpieza**: Automática al crear/cerrar pedidos
- **Prioridades**: Normal (apertura), Urgente (cierre)

### Habitaciones

- **CuentaEstancia**: Carga de pedidos a cuenta de habitación (para huéspedes)
- **CuentaRestaurante**: Cuentas propias del restaurante (para clientes no huéspedes)
- **EstadoPedido**: CARGADO_A_HABITACION

## Flujos Principales

1. **Gestión de Platos**: Crear, editar, precios, imágenes, recetas
2. **Procesos de Cocina**: Trazabilidad de preparación basada en recetas
3. **Materia Prima Cocina**: Transformación de bruto a materia prima con merma e inventario
4. **Conciliación de Recetas**: Validación de stock de materia prima y material bruto
5. **Pedidos**: Abrir mesa, agregar items, enviar a cocina
6. **Cuentas de Restaurante**: Crear cuenta para cliente no huésped, agregar pedidos, registrar pagos
7. **KDS (Kitchen Display System)**: Visualización de pedidos en cocina
8. **Impresión de Comandas**: Tickets térmicos por área de cocina
9. **Gestión de Mesas**: Estados, unión, separación, mover cuentas
10. **Reportes**: Ventas, ranking de platos, métricas
11. **Portal Web**: Menú visible para huéspedes

## Regla Operativa de Cocina e Inventario

Las recetas de platillos deben consumir **materia prima lista**. La materia prima se produce en `Restaurante > Materia Prima Cocina` desde material bruto. Cuando una receta no tiene stock suficiente, `Restaurante > Conciliación Recetas` clasifica el problema:

- `ok`: hay materia prima suficiente.
- `puede_transformarse`: falta materia prima, pero hay material bruto suficiente.
- `falta_bruto`: existe regla de transformación, pero falta material bruto.
- `sin_regla_transformacion`: falta materia prima y no existe regla para saber desde qué bruto producirla.
- `receta_incompleta` / `variante_invalida`: la receta necesita corrección de datos maestros.

Cada producto usado como receta define `rendimiento_porciones`. La cantidad consumida por pedido se calcula como:

`(cantidad del ingrediente en la receta / rendimiento_porciones) × cantidad pedida`

Si al enviar o reenviar una comanda falta stock, los items afectados pasan a `Bloqueado por Stock` y guardan el detalle del faltante. El mesero debe abrir el pedido y usar `Resolver Faltantes` para registrar lo confirmado con el cliente:

- usar un ingrediente sustituto;
- cambiar el platillo;
- quitar el item;
- cancelar el pedido completo.

Después de resolver, el item vuelve a pendiente y puede reenviarse a cocina.

## Permisos (Filament Shield)

- `view_any_platos`: Ver listado de platos
- `create_platos`: Crear platos
- `edit_platos`: Editar platos
- `delete_platos`: Eliminar platos
- `view_any_pedidos`: Ver pedidos
- `create_pedidos`: Crear pedidos
- `edit_pedidos`: Editar pedidos
- `delete_pedidos`: Eliminar pedidos
- `page_GestionMesas`: Acceder a gestión de mesas
- `page_ConfiguracionRestaurante`: Configurar restaurante
- `page_ReportesRestaurante`: Ver reportes

## Auditoría

Todas las acciones críticas se registran en `AuditoriaRestaurante`:

- Cambio de estado de mesa
- Mover cuenta entre mesas
- Aplicar descuentos
- Impresión/reimpresión de comandas
- Cambios de configuración

## Configuración

La configuración del restaurante se almacena en `meta_datos` del Espacio principal:

- `propina_sugerida`: Porcentaje de propina sugerido (default 10%)
- `impuesto_porcentaje`: Porcentaje de impuesto (default 15%)
- `impresora_cocina`: Nombre de impresora de cocina
- `impresora_bar`: Nombre de impresora de bar
- `impresora_postres`: Nombre de impresora de postres
- `impresora_parrilla`: Nombre de impresora de parrilla
- `impresion_automatica`: Habilitar impresión automática
- `copias_ticket`: Número de copias del ticket

## Pruebas

- **Feature**: `tests/Feature/Restaurante/FlujoRestauranteTest.php`
- **Feature**: `tests/Feature/Espacios/RestauranteLandingTest.php`
- **Unit**: `tests/Unit/BusinessLogic/Restaurante/`
