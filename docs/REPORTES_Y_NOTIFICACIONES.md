# Reportes y Notificaciones — Módulo de Compras

Este documento cubre los reportes PDF, correcciones aplicadas y el sistema de notificaciones del módulo de Compras.

---

## 1. Reportes PDF (Serie HTB-COM)

Todos los reportes de compras usan **Spatie PDF** con el layout `layouts.reporte-htb`.

### HTB-COM-001 — Solicitud de Compra

| Campo | Valor |
|-------|-------|
| Controlador | `CompraReportController@imprimirSolicitud` |
| Ruta | `GET /admin/compras/reportes/solicitud/{solicitud}` |
| Permiso | `compras:ImprimirSolicitud` |
| Contenido | Datos del solicitante, departamento, justificación, tabla de ítems con cantidades solicitadas/aprobadas, firmas |

### HTB-COM-002 — Cotización de Proveedor

| Campo | Valor |
|-------|-------|
| Controlador | `CompraReportController@imprimirCotizacion` |
| Ruta | `GET /admin/compras/reportes/cotizacion/{cotizacion}` |
| Permiso | `compras:ImprimirCotizacion` |
| Contenido | Proveedor, condición de pago, vigencia, ítems cotizados con precios unitarios y subtotales |

### HTB-COM-003 — Orden de Compra

| Campo | Valor |
|-------|-------|
| Controlador | `CompraReportController@imprimirOrdenCompra` |
| Ruta | `GET /admin/compras/reportes/orden-compra/{orden}` |
| Permiso | `compras:ImprimirOrdenCompra` |
| Contenido | Proveedor, condición de pago, ítems, precios, impuestos y total |

### HTB-COM-004 — Recepción de Mercancía

| Campo | Valor |
|-------|-------|
| Controlador | `CompraReportController@imprimirRecepcion` |
| Ruta | `GET /admin/compras/reportes/recepcion/{recepcion}` |
| Permiso | `compras:ImprimirRecepcion` |
| Contenido | Orden de compra origen, ítems recibidos, cantidades recibidas/rechazadas, motivo de rechazo |

### HTB-COM-005 — Resumen por Departamentos

| Campo | Valor |
|-------|-------|
| Controlador | `CompraReportController@imprimirResumenDepartamentos` |
| Ruta | `GET /admin/compras/reportes/resumen-departamentos` |
| Permiso | `compras:ImprimirReportesCompras` |
| Contenido | Conteo de órdenes y total gastado agrupado por departamento |

### HTB-COM-006 — Cuadro Comparativo de Cotizaciones

| Campo | Valor |
|-------|-------|
| Controlador | `CompraReportController@imprimirComparativa` |
| Ruta | `GET /admin/compras/reportes/comparativa/{solicitud}` |
| Permiso | `compras:ImprimirComparativa` |
| Contenido | Cuadro técnico comparativo de precios, variantes y plazos, con resumen de adjudicación agrupado por proveedor y totales generales. |

> [!NOTE]
> Este reporte filtra por el período actual (inicio de mes → fecha actual) usando `whereBetween('fecha_orden', ...)`. Acepta los parámetros query opcionales `fecha_inicio` y `fecha_fin` (formato `Y-m-d`) para personalizar el rango. El botón en `ListOrdenCompras` abre un modal con DatePickers para seleccionar el rango. Anteriormente mostraba datos históricos sin filtrar.

### HTB-COM-007 — Devolución a Proveedor

| Campo | Valor |
|-------|-------|
| Controlador | `CompraReportController@imprimirDevolucion` |
| Ruta | `GET /admin/compras/reportes/devolucion/{devolucion}` |
| Permiso | `compras:ImprimirDevolucion` |
| Contenido | Datos de la orden de compra origen, recepción vinculada, items devueltos con lotes, cantidades y motivo |

---



## 2. Correcciones Aplicadas a Reportes

### 2.1 Filtro de fechas en Resumen por Departamentos

- **Problema:** El reporte `imprimirResumenDepartamentos` mostraba en el encabezado "Inicio de mes → Hoy", pero la consulta SQL no aplicaba ningún filtro de fechas, mostrando todas las órdenes históricas.
- **Solución:** Se agregó `whereBetween('oc.fecha_orden', [$fechaInicio, $fechaFin])` para alinear el período mostrado con los datos consultados.

### 2.2 Unificación de generación de PDF (Solicitudes)

- **Problema:** Existían dos motores y plantillas para el mismo documento:
  - `ViewSolicitud.php` usaba **DomPDF** con la vista `reportes.solicitud-compra`
  - `CompraReportController@imprimirSolicitud` usaba **Spatie PDF** con la vista `reports.compras.solicitud`
- **Solución:** Se eliminó la dependencia de DomPDF en `ViewSolicitud.php`. Ahora usa **Spatie PDF** con la misma vista `reports.compras.solicitud`, unificando el diseño y el motor de renderizado.
- **Archivos afectados:**
  - `app/Filament/Resources/Compras/Solicitudes/Pages/ViewSolicitud.php`
  - `app/Actions/Compras/GenerarReporteSolicitudPdfAction.php` (ya no se usa desde Filament)

### 2.3 Correo electrónico con acento

- **Problema:** El email del hotel en los reportes contenía un carácter acentuado: `recepción@bugambiliashotel.com` (no válido para SMTP).
- **Solución:** Se cambió a `recepcion@bugambiliashotel.com` (ASCII).

### 2.4 Middleware de autorización en rutas PDF

- **Problema:** Las rutas de descarga de PDF solo estaban protegidas con `auth`, permitiendo que cualquier usuario autenticado descargara documentos sensibles.
- **Solución:** Se agregó middleware `can:Imprimir*` a cada ruta, validando los permisos personalizados documentados en la matriz de roles.

```php
Route::get('/solicitud/{solicitud}', [CompraReportController::class, 'imprimirSolicitud'])
    ->middleware('can:ImprimirSolicitud');
```

### 2.5 Nullsafe en traits

- **Problema:** El trait `HasStatusHistory` llamaba a `method_exists($estadoAnterior, 'label')` sin verificar si `$estadoAnterior` era un objeto. Al obtener el valor original desde BD (`getOriginal()`), este es un entero crudo, causando TypeError.
- **Solución:** Se agregó `is_object()` antes de cada `method_exists()`.

---

## 3. Sistema de Notificaciones

### 3.1 Arquitectura

Las notificaciones del módulo de Compras se centralizan en el servicio:

**`app/Services/Compras/NotificadorCompras.php`**

El servicio maneja dos tipos de notificaciones:

| Tipo | Método | Persistencia |
|------|--------|-------------|
| **Base de Datos** | `Notification::make()->sendToDatabase($user)` | Persistente, visible en el panel de notificaciones |
| **En vivo (Toast)** | `Notification::make()->send()` | Solo visible si el usuario está conectado en ese momento |

### 3.2 Enrutamiento de notificaciones

El servicio define tres métodos de enrutamiento:

| Método | Destinatarios | Uso |
|--------|--------------|-----|
| `enviar($user, ...)` | Un solo usuario | Notificaciones personales (creador de solicitud) |
| `notificarMultiples($users, ...)` | Lista de usuarios | Notificaciones masivas |
| `notificarCreadorYCompras($creator, ...)` | Creador + usuarios con permiso `ViewAny:Solicitud` | Eventos que afectan a todo el equipo de compras |

**Resolución de destinatarios:**
- **Creador de solicitud:** Se obtiene mediante `User::where('persona_id', $solicitud->colaborador->persona_id)->first()`
- **Usuarios de compras:** Se obtienen mediante `User::permission('ViewAny:Solicitud')->get()`

### 3.3 Eventos notificados

| Evento | Método | Destinatarios | Icono |
|--------|--------|---------------|-------|
| Solicitud creada | `solicitudCreada()` | Creador | `document-text` |
| Solicitud aprobada | `solicitudAprobada()` | Creador | `check-circle` |
| Solicitud rechazada | `solicitudRechazada()` | Creador | `x-circle` |
| Solicitud cancelada | `solicitudCancelada()` | Creador + Compras | `x-circle` |
| Cotización creada | `cotizacionCreada()` | Creador + Compras | `document-currency-dollar` |
| Ganador seleccionado | `ganadorSeleccionado()` | Creador + Compras | `trophy` |
| Orden de compra creada | `ordenCreada()` | Creador + Compras | `shopping-cart` |
| Orden de compra emitida | `ordenEmitida()` | Creador + Compras | `paper-airplane` |
| Orden de compra cancelada | `ordenCancelada()` | Creador + Compras | `x-circle` |
| Recepción registrada | `recepcionCreada()` | Creador + Compras | `archive-box` / `exclamation-triangle` |
| Devolución creada | `devolucionCreada()` | Creador + Compras | `arrow-turn-down-left` |
| Devolución confirmada | `devolucionConfirmada()` | Creador + Compras | `check-circle` |

### 3.4 Puntos de disparo (Triggers)

| Archivo | Línea | Evento |
|---------|-------|--------|
| `CreateSolicitud.php` | 55 | `solicitudCreada()` — al crear solicitud |
| `SolicitudTable.php` | 91 | `solicitudAprobada()` — al aprobar desde tabla |
| `SolicitudTable.php` | 104 | `solicitudCancelada()` — al cancelar desde tabla |
| `SolicitudTable.php` | 119 | `solicitudRechazada()` — al rechazar desde tabla |
| `EditSolicitud.php` | 116 | `solicitudCancelada()` — al cancelar desde edición |
| `ComparativaSolicitud.php` | 61 | `solicitudAprobada()` — al aprobar desde comparativa |
| `CreateCotizacion.php` | 18 | `cotizacionCreada()` — al crear cotización |
| `CotizacionTable.php` | 119 | `ganadorSeleccionado()` — al elegir ganador en tabla |
| `ViewCotizacion.php` | 34 | `ganadorSeleccionado()` — al elegir ganador en vista |
| `ComparativaCotizaciones.php` | 119, 140 | `ganadorSeleccionado()` — desde comparativa |
| `ComparativaCotizaciones.php` | 183, 233 | `solicitudAprobada()` — al generar orden desde comparativa |
| `GenerarOrdenDesdeCotizacion.php` | 80, 83 | `ordenCreada()`, `solicitudAprobada()` — al generar OC |
| `OrdenCompraTable.php` | 123 | `ordenEmitida()` — al emitir OC |
| `OrdenCompraTable.php` | 150 | `ordenCancelada()` — al cancelar OC |
| `RecepcionObserver.php` | 19 | `recepcionCreada()` — al crear recepción (observer) |
| `CreateDevolucionCompra.php` | afterCreate | `devolucionCreada()` — al crear devolución |
| `DevolucionCompraTable.php` | confirmar action | `devolucionConfirmada()` — al confirmar devolución desde tabla |
| `ViewDevolucionCompra.php` | confirmar action | `devolucionConfirmada()` — al confirmar devolución desde vista |

### 3.5 Polling del panel

El `AdminPanelProvider` configura polling de notificaciones cada 15 segundos:

```php
->databaseNotifications()
->databaseNotificationsPolling('15s')
```

### 3.6 Estructura de la notificación

Cada notificación incluye:
- **Título:** Nombre del evento (ej. "Solicitud Aprobada")
- **Cuerpo:** Descripción con referencias (ej. "La solicitud SOL-2026-001 ha sido aprobada.")
- **Acción:** Botón "Ver" con enlace a la página de detalle del registro
- **Icono:** Heroicon representativo del evento

---

## 4. Configuración del Polling

El polling de notificaciones base de datos se configura en:

**`app/Providers/Filament/AdminPanelProvider.php`**

```php
$panel
    ->databaseNotifications()
    ->databaseNotificationsPolling('15s');
```

> [!TIP]
> Si la frecuencia de 15s es muy alta para el servidor, se puede aumentar a `30s` o `60s`.

---

## 5. Queue Worker para Notificaciones

Las notificaciones de base de datos (`sendToDatabase`) se procesan a través del sistema de colas de Laravel. **Es obligatorio tener el worker en ejecución** para que las notificaciones se envíen y aparezcan en el panel.

### Iniciar el worker

```bash
php artisan queue:work
```

Esto debe correr en segundo plano (una terminal separada, o como servicio con Supervisor en producción).

### Verificar colas fallidas

Si las notificaciones no llegan, revisar:

```bash
php artisan queue:failed
```

### Comandos útiles

| Comando | Propósito |
|---------|-----------|
| `php artisan queue:work` | Procesar colas en tiempo real (modo daemon) |
| `php artisan queue:listen` | Procesar colas reiniciando el worker en cada job (útil en desarrollo) |
| `php artisan queue:restart` | Reiniciar todos los workers después de desplegar cambios |
| `php artisan queue:failed` | Listar jobs fallidos |
| `php artisan queue:retry all` | Reintentar todos los jobs fallidos |

> [!WARNING]
> El panel de Filament usa **polling** (cada 15s) para buscar notificaciones nuevas. No confundir el polling del frontend con el worker del backend. **Ambos son necesarios**: el worker envía la notificación a la BD, el polling la muestra en pantalla.
