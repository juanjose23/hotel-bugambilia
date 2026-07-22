# Hotel Bugambilias — Sistema de Gestion Integral

Sistema de gestion integral para Hotel Bugambilias: inventario, activos, limpieza, compras, restaurante, habitaciones y mas.

## Stack Tecnologico

| Capa            | Tecnologia                          | Version |
| --------------- | ----------------------------------- | ------- |
| Backend         | PHP                                 | 8.3+    |
| Framework       | Laravel                             | 13.17   |
| Admin Panel     | Filament                            | 5.6     |
| Frontend        | React + TypeScript                  | 19.2    |
| UI Components   | shadcn/ui + TailwindCSS             | v4.3    |
| SPA             | Inertia.js                          | v3.0    |
| Autenticacion   | Spatie Permission + Filament Shield | 4.2     |
| Auditoria       | Laravel Auditing                    | 14.0    |
| PDF             | DomPDF                              | 3.1     |
| Testing         | Pest                                | 4.7     |
| Static Analysis | PHPStan (Larastan)                  | Level 9 |

---

## Arquitectura

El proyecto sigue una **arquitectura por capas** estricta definida en `AGENTS.md`. Las capas superiores pueden conocer las inferiores, pero las inferiores **NUNCA** conocen las superiores.

### Flujo de Datos

```
UI (Filament / React / Controller)
         |
         v
Interactor   --- Casos de uso completos
         |
         v
BusinessLogic --- Reglas del negocio
         |
         v
Repository   --- Lectura (Queries) / Escritura (Persistencia)
         |
         v
Model        --- Entidades Eloquent (app/Repository/Models)
```

### Regla de Decision

| Pregunta                               | Respuesta        |
| -------------------------------------- | ---------------- |
| Es una pantalla?                       | Filament         |
| Es una accion completa del usuario?    | Interactor       |
| Es una regla del negocio?              | BusinessLogic    |
| Es acceso a datos?                     | Repository       |
| Es una operacion pequena reutilizable? | Action           |
| Es procesamiento en segundo plano?     | Job              |
| Es una reaccion a algo ocurrido?       | Event + Listener |

---

## Estructura del Proyecto

### app/ - Capas de la Arquitectura

| Directorio     | Archivos | Funcion                                                                  |
| -------------- | -------- | ------------------------------------------------------------------------ |
| Filament/      | 362      | UI administrativa (Resources, Pages, Schemas, Tables, Widgets)           |
| Repository/    | 273      | Models, Queries (lectura), Persistencia (escritura), Policies, Observers |
| Interactors/   | 127      | Casos de uso completos del sistema                                       |
| BusinessLogic/ | 112      | Reglas de negocio puras (independientes de HTTP/Filament)                |
| Enums/         | 42       | Estados, tipos, categorias (sin numeros magicos)                         |
| Events/        | 31       | Eventos de dominio                                                       |
| Actions/       | 28       | Operaciones pequenas reutilizables                                       |
| Listeners/     | 30       | Reacciones a eventos                                                     |
| Notifications/ | 26       | Notificaciones por dominio                                               |
| Support/       | 22       | Utilidades (PDF, Barcode, Excel)                                         |
| Http/          | 15       | Controllers, Middleware (solo lo estrictamente necesario)                |
| Jobs/          | 6        | Trabajos en cola                                                         |
| Console/       | 4        | Comandos Artisan                                                         |
| Exports/       | 3        | Exportaciones Excel/CSV                                                  |
| Services/      | 3        | Integraciones externas                                                   |
| Traits/        | 2        | Comportamientos reutilizables                                            |
| Providers/     | 2        | Service Providers                                                        |

### app/Repository/Models/ - Entidades por Dominio

| Dominio        | Modelos | Descripcion                                     |
| -------------- | ------- | ----------------------------------------------- |
| Activos/       | 8       | Activos fijos, bajas, mantenimientos            |
| Catalogos/     | 6       | Productos, variantes, catalogos                 |
| Compras/       | 13      | Cotizaciones, ordenes, recepciones, proveedores |
| Inventario/    | 5       | Lotes, stock, movimientos                       |
| Limpieza/      | 7       | Ejecuciones, turnos, carritos                   |
| Restaurante/   | 5       | Platos, pedidos, procesos de cocina             |
| Habitaciones/  | 3       | Habitaciones, espacios                          |
| Servicios/     | 1       | Servicios del hotel                             |
| Reservas/      | 2       | Reservaciones                                   |
| Promociones/   | 2       | Promociones y paquetes                          |
| Colaboradores/ | 6       | Empleados, cargos, salarios                     |
| Personas/      | 3       | Personas, documentos                            |
| Usuarios/      | 1       | Usuarios del sistema                            |
| Shared/        | 4       | Monedas, paises, ubicaciones                    |
| Audits/        | 2       | Auditoria del sistema                           |

> Los modelos NO viven en `app/Models/`. La ruta correcta es `app/Repository/Models/`.

### database/ - Migraciones por Dominio

| Directorio    | Migraciones | Tablas principales                                               |
| ------------- | ----------- | ---------------------------------------------------------------- |
| Generales/    | 13          | personas, users, monedas, paises, ubicaciones, permisos          |
| Compras/      | 13          | proveedores, cotizaciones, ordenes_compra, recepciones           |
| Usuarios/     | 10          | usuarios, clientes, colaboradores, documentos                    |
| Inventario/   | 7           | productos, producto_variante, inv_lotes, stocks, inv_movimientos |
| Restaurante/  | 7           | platos, pedidos, pedido_items, procesos_cocina, proceso_items    |
| Activos/      | 5           | activos, activos_bajas, mantenimientos                           |
| Catalogos/    | 5           | catalogos, catalogo_tipos, producto_kit, moneda_tasa             |
| Habitaciones/ | 5           | espacios, habitaciones, espacios_servicios                       |
| Limpieza/     | 5           | limpieza_turnos, limpieza_ejecuciones                            |
| Servicios/    | 4           | servicios, servicio_paquete_items                                |
| Reservas/     | 3           | reservas, reserva_estado_historial                               |
| Promociones/  | 2           | promociones, promociones_condiciones                             |

**Total: 79 migraciones en 12 subdirectorios (0 archivos sueltos en raiz)**

### resources/ - Frontend

| Directorio         | Archivos | Contenido                                                        |
| ------------------ | -------- | ---------------------------------------------------------------- |
| js/modules/        | 104      | 13 modulos React (shared, habitaciones, restaurante, auth, etc.) |
| js/pages/          | 4        | Login, Registro, Home, Habitacion                                |
| views/reports/     | 83       | Plantillas PDF por dominio                                       |
| views/filament/    | 22       | Vistas Filament personalizadas                                   |
| views/components/  | 11       | Componentes Blade (limpieza, shared)                             |
| views/restaurante/ | 1        | Vista de comanda                                                 |

### doc/ - Documentacion

| Modulo       | Archivo                    | Estado   |
| ------------ | -------------------------- | -------- |
| Catalogos    | doc/catalogos/flujos.md    | Completo |
| Servicios    | doc/servicios/flujos.md    | Completo |
| Habitaciones | doc/habitaciones/flujos.md | Completo |
| Promociones  | doc/promociones/flujos.md  | Completo |
| Clientes     | doc/clientes/flujos.md     | Completo |
| Limpieza     | doc/limpieza/flujos.md     | Completo |
| Compras      | doc/compras/flujos.md      | Completo |
| Inventario   | doc/inventario/flujos.md   | Completo |
| Activos      | doc/activos/flujos.md      | Completo |
| Restaurante  | doc/restaurante/flujos.md  | Completo |

### tests/ - Suite de Pruebas

| Directorio     | Archivos | Cobertura                                                   |
| -------------- | -------- | ----------------------------------------------------------- |
| tests/Feature/ | 22       | Servicios, Usuarios, Espacios, Limpieza, Compras, Observers |
| tests/Unit/    | 4        | BusinessLogic, Queries, Rules                               |

**Framework: Pest 4 | Base de datos: SQLite :memory:**

---

## Modulos del Sistema

### Activos Fijos

Gestion de activos fijos del hotel: registro, depreciacion, bajas, mantenimiento preventivo y correctivo, individualizacion con codigos de barras.

**Documentacion:** doc/activos/flujos.md

### Catalogos

Productos, variantes, catalogos, tipos, paises, monedas y tasas de cambio. Base para inventario y compras.

**Documentacion:** doc/catalogos/flujos.md

### Compras

Flujo completo de compras: solicitudes, cotizaciones, ordenes de compra, recepciones, devoluciones. Con analisis de proveedores y reportes.

**Documentacion:** doc/compras/flujos.md

### Inventario

Gestion de lotes, stock por ubicacion, movimientos de entrada/salida/consumo/transferencia, inventario fisico, mermas y trazabilidad.

**Documentacion:** doc/inventario/flujos.md

### Limpieza

Turnos de limpieza, ejecuciones por habitacion/espacio, carrito de limpieza con stock, horarios, recordatorios automaticos y materializacion de ejecuciones.

**Documentacion:** doc/limpieza/flujos.md

### Habitaciones

Gestion de habitaciones y espacios del hotel, con servicios asociados y estados.

**Documentacion:** doc/habitaciones/flujos.md

### Servicios

Servicios que ofrece el hotel, precios historicos, paquetes y reportes de uso.

**Documentacion:** doc/servicios/flujos.md

### Restaurante

Gestion de platos con recetas basadas en stock, pedidos de meseros, procesos de cocina con calculo de costos desde inventario, dashboard con KPIs, portal web publico.

**Documentacion:** doc/restaurante/flujos.md

### Promociones

Promociones, paquetes, condiciones y politicas de precios.

**Documentacion:** doc/promociones/flujos.md

### Clientes / Usuarios

Registro de clientes, gestion de usuarios, roles, permisos, resolucion de conflictos de identidad.

**Documentacion:** doc/clientes/flujos.md

### Reservas

Sistema de reservas con estados, historial y disponibilidad.

### Colaboradores

Gestion de empleados, cargos, salarios, documentos y historial laborales.

### Auditoria

Registro de auditoria del sistema con trazabilidad de cambios.

---

## Cambios Recientes

### Reorganizacion de Migraciones

Se consolidaron todas las migraciones en **12 subdirectorios** eliminando archivos sueltos en la raiz de `database/migrations/`:

| Cambio                               | Detalle                                                    |
| ------------------------------------ | ---------------------------------------------------------- |
| Migraciones movidas a subdirectorios | 14 archivos (2026_07_20_*) a Promociones/, Restaurante/    |
| Migraciones ALTER consolidadas       | 6 migraciones separadas fusionadas en su CREATE original   |
| Imports inline eliminados            | foreignIdFor(Model) a foreignId() con nombres de tabla raw |
| declare(strict_types=1)              | Agregado a users y productos migrations                    |

**Migraciones eliminadas (consolidadas):**

| Eliminada                             | Consolidada en            |
| ------------------------------------- | ------------------------- |
| add_web_to_servicios                  | create_servicios_table    |
| add_precio_paquete_to_promociones     | create_promociones_table  |
| add_password_change_required_to_users | create_users_table        |
| add_es_transformable_to_productos     | productos_table           |
| add_plato_id_to_pedido_items          | create_pedido_items_table |
| drop_servicio_id_from_pedido_items    | create_pedido_items_table |

**Nuevos subdirectorios creados:**

| Directorio   | Contenido                                                                           |
| ------------ | ----------------------------------------------------------------------------------- |
| Promociones/ | 2 migraciones (create_promociones, create_promociones_condiciones)                  |
| Restaurante/ | 7 migraciones (platos, pedidos, pedido_items, procesos_cocina, proceso_items, etc.) |

### Eliminacion de Imports Inline en Migraciones

Las migraciones anteriormente usaban `foreignIdFor(Model::class)` que generaba imports directos. Se reemplazaron por `foreignId('column_name')` con nombres de tabla raw para evitar dependencias entre archivos:

```php
// Antes (incorrecto)
use App\Repository\Models\Catalogos\Producto;
foreignIdFor(Producto::class)

// Despues (correcto)
foreignId('producto_padre_id')
```

### Correccion de Bug en Validacion Unica (ClienteForm)

Se corrigio el error `SQLSTATE[42P01]: Undefined table: personas` en la validacion unica del formulario de clientes:

```php
// Antes (incorrecto)
->unique(ignoreRecord: true)

// Despues (correcto)
->unique(ignorable: fn () => $this->getRecord()?->user)
```

El problema era que `ignoreRecord: true` intentaba resolver el modelo `User` en lugar de `Persona` para la validacion unica.

### Modulo Restaurante - Proceso de Cocina Basado en Recetas

Se reescribio el flujo del modulo Restaurante para que los costos de cocina se calculen automaticamente desde el inventario:

**Antes:** Costos manuales hardcoded, sin conexion con inventario.

**Ahora:**

- Los platos tienen recetas (ProductoKit) con ingredientes
- Los costos se obtienen de Stock -> Lote -> costo_unitario
- El registro de procesos de cocina genera automaticamente el desglose de ingredientes
- El consumo de stock se registra como movimiento CONSUMO
- Los reportes muestran el estado de stock de cada ingrediente

**Archivos modificados:**

- `app/Interactors/Restaurante/RegistrarProcesoCocina.php` - Reescrito completamente
- `app/Interactors/Restaurante/CalcularCostoPlato.php` - Cadena Stock->Lote para costos
- `app/Filament/Resources/Restaurante/ProcesoCocinaResource/Schemas/ProcesoCocinaForm.php` - Selector de plato
- `app/Repository/Models/Restaurante/ProcesoCocina.php` - Relacion plato()
- `database/migrations/Restaurante/2026_07_21_220000_add_plato_and_cantidad_platos_to_procesos_cocina_table.php` - Nueva migracion

### Documentacion del Modulo Restaurante

Se creo `doc/restaurante/flujos.md` con 11 flujos documentados:

1. Gestion de Platos
2. Crear / Editar Plato
3. Calculo de Costo de Plato
4. Registro de Proceso de Cocina
5. Listado de Procesos de Cocina
6. Crear / Editar Pedido
7. Consumo de Ingredientes (KDS)
8. Reportes del Restaurante
9. Impresion de Comanda
10. Portal Web del Restaurante
11. Observer de Pedido

---

## Instalacion

### Requisitos

- PHP 8.3+
- Composer
- Node.js 18+
- SQLite (desarrollo) o PostgreSQL (produccion)

### Configuracion

```bash
# Clonar repositorio
git clone <repository-url>
cd hotel-bugambilias-reload

# Instalar dependencias
composer setup

# Iniciar servidor de desarrollo
composer dev
```

### Comandos de Calidad

```bash
# Analisis estatico (PHPStan Level 9)
composer phpstan

# Formateo de codigo PHP
composer lint

# Verificar formateo sin modificar
composer lint:check

# Ejecutar pruebas
composer test
```

### Scripts de Desarrollo

| Comando            | Descripcion                                               |
| ------------------ | --------------------------------------------------------- |
| `composer setup`   | Instala todo: dependencias, key, migraciones, npm build   |
| `composer dev`     | Inicia servidor + queue + vite en paralelo                |
| `composer lint`    | Ejecuta Laravel Pint para formatear codigo                |
| `composer phpstan` | Analisis estatico nivel 9                                 |
| `composer test`    | Limpia config, verifica formateo, tipos y ejecuta pruebas |

---

## CI/CD

El proyecto tiene 6 workflows de GitHub Actions:

| Workflow           | Funcion                         |
| ------------------ | ------------------------------- |
| tests.yml          | Ejecuta suite de pruebas Pest   |
| pint.yml           | Verifica formateo de codigo PHP |
| phpstan.yml        | Analisis estatico nivel 9       |
| lint.yml           | Verifica ESLint y Prettier      |
| filament-check.yml | Verificaciones de Filament      |
| label.yml          | Auto-labeling de PRs            |

---

## Convenciones de Codigo

### En Migraciones

- Todas las migraciones en subdirectorios por dominio
- Sin imports inline (usar nombres de tabla raw)
- `declare(strict_types=1)` en todas las migraciones
- Migraciones ALTER consolidadas en su CREATE original

### En PHP

- Tipado estricto: `declare(strict_types=1)`
- Clases finales cuando sea apropiado: `final class Nombre`
- Constructor injection (no resolver servicios con `app()`)

### En Filament

- Filament solo contiene UI: forms, tables, pages, widgets
- Toda logica de negocio va a Interactors
- Ejemplo correcto:

```php
->action(function(){
    app(RegistrarProcesoCocina::class)
        ->ejecutar($data);
});
```

### En Modelos

- Ubicacion: `app/Repository/Models/Dominio/Nombre.php`
- Solo relaciones, casts, scopes simples
- Sin logica de negocio compleja

---

## Metricas del Proyecto

| Metrica              | Cantidad |
| -------------------- | -------- |
| Archivos PHP en app/ | 1,064    |
| Archivos de recursos | 222      |
| Migraciones          | 79       |
| Seeders              | 33       |
| Factories            | 26       |
| Archivos de prueba   | 26       |
| Resources Filament   | 33       |
| Models Eloquent      | 77+      |
| Modulos de negocio   | 15+      |
| Modulos documentados | 10       |
| Workflows CI         | 6        |
