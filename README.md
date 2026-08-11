# Hotel Bugambilias — Sistema de Gestion Integral

Sistema de gestion integral para Hotel Bugambilias: inventario, activos, limpieza, compras, restaurante, habitaciones y mas.

## Requisitos

- PHP 8.4.1 o superior
- Composer 2
- Node.js 22 o superior
- npm
- Base de datos compatible con Laravel 13

## Instalacion

1. Clona el repositorio.
2. Instala dependencias de PHP:

    ```bash
    composer install --no-interaction --prefer-dist --optimize-autoloader
    ```

3. Instala dependencias de frontend:

    ```bash
    npm install
    ```

4. Crea el archivo de entorno y genera la clave de la aplicacion:

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5. Configura la base de datos en `.env` y ejecuta migraciones:

    ```bash
    php artisan migrate --force
    ```

6. Compila los assets:

    ```bash
    npm run build
    ```

## Comandos Utiles

- `composer install --no-interaction --prefer-dist --optimize-autoloader`
- `composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts` cuando quieras evitar los scripts de Composer
- `php artisan test`
- `vendor/bin/phpstan analyse --level=9 --memory-limit=1G`
- `vendor/bin/pint --test`
- `npm run dev`
- `npm run build`

## Variables de Entorno

Las variables principales se definen en `.env`.

- `APP_NAME`
- `APP_ENV`
- `APP_KEY`
- `APP_URL`
- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

## Notas de CI y Entorno Local

- El proyecto ya exige PHP 8.4.1 en `composer.json`.
- Los workflows de GitHub Actions ejecutan las validaciones sobre PHP 8.4.
- Si en Windows `composer install` falla al publicar assets de Filament, usa `composer install --no-scripts` y luego ejecuta los scripts necesarios por separado.

## Stack Tecnologico

| Capa            | Tecnologia                          | Version |
| --------------- | ----------------------------------- | ------- |
| Backend         | PHP                                 | 8.4.1+  |
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

## Desarrollo Local

Para levantar la aplicacion en local:

```bash
php artisan serve
npm run dev
```

En otra terminal, ejecuta las validaciones principales cuando cambies codigo:

```bash
composer install --no-interaction --prefer-dist --optimize-autoloader
php artisan test
vendor/bin/phpstan analyse --level=9 --memory-limit=1G
vendor/bin/pint --test
```

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

---x....x...........

✗ deprecated-reactive (Deprecated Code)
app\Filament\Resources\Restaurante\PlatoResource\RelationManagers\RecetaRelationManager.php
Line 48: The `reactive()` method is deprecated.
→ Use `live()` instead of `reactive()`. See: https://filamentphp.com/docs/5.x/forms/overview#the-basics-of-reactivity

✗ deprecated-mutate-form-data-using (Deprecated Code)
app\Filament\Resources\Restaurante\PlatoResource\RelationManagers\RecetaRelationManager.php
Line 103: The `mutateFormDataUsing()` method is deprecated in Filament v4.
→ Use `mutateDataUsing()` instead.

Rules: 15 passed, 2 failed
Issues: 2 warning(s)

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
