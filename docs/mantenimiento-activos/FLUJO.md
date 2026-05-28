# Flujo — Mantenimiento de Activos Fijos

## Arquitectura

```
routes/console.php  (lee config('jobs.*'))
    │
    ▼
Jobs (dispatchSync ó dispatch)
    │
    ▼
UseCases (App\UseCases\Activos\Mutations\*)
    │
    ▼
Services (App\Services\Activos\NotificadorActivos)
    │
    ▼
Database Notifications (Filament)
```

## 1. Mantenimiento Preventivo Automático

**Job:** `VerificarMantenimientosPreventivosJob` (**Diario**, default `06:00` America/Managua)
**UseCase:** `DetectarMantenimientosPreventivos`

### Flujo

1. Busca planes activos (`inv_planes_mantenimiento`) con `fecha_proximo_mantenimiento <= today`
2. Para cada plan, itera sus activos vinculados vía pivot `act_plan_activos`
3. Por cada activo, si no tiene un ticket con estado `Programado` o `En Proceso` abierto, crea uno automáticamente.
4. Actualiza `plan.fecha_ultimo_mantenimiento = today` y calcula la siguiente fecha `plan.fecha_proximo_mantenimiento = today + frecuencia_dias`
5. Este proceso es **100% silencioso** para evitar spam.

### Setup

```php
$plan = ActPlanMantenimiento::create([
    'nombre' => 'Plan Anual',
    'tipo' => TipoPlanMantenimiento::Preventivo,
    'frecuencia_dias' => 90,
    'fecha_inicio' => '2026-01-01',
    'fecha_proximo_mantenimiento' => '2026-04-01',
    'estado' => EstadoPlanMantenimiento::Activo,
]);

$plan->activos()->attach($activo->id);
```

## 2. Notificador Unificado (Anti-Spam)

**Job:** `NotificarMantenimientosJob` (**Diario**, default `07:00` America/Managua)
**UseCase:** `NotificarMantenimientos`

Para evitar saturar la base de datos y a los usuarios con múltiples correos o alertas duplicadas, consolidamos todas las alertas en un solo flujo diario.

### Lógica de Ventanas de Alerta
El notificador lee los mantenimientos programados o en curso y despacha notificaciones bajo las siguientes ventanas discretas exactas:
1. **Próximos**: Alertas a los **7 días**, **3 días**, **1 día** y el **mismo día (hoy)** de la fecha programada.
2. **Atrasados**: Alertas a **1 día vencido** y a **7 días o más vencidos (Críticos)**.
3. **Prolongados**: Alertas sobre mantenimientos en curso (`En Proceso`) con más de **15 días** desde su fecha programada.

### Características Clave
* **Dirigido al Técnico**: Si el ticket tiene un técnico asignado (`realizado_por_id`), la alerta llega exclusivamente a su bandeja. De lo contrario, se notifica al pool administrativo general (`User::all()`).
* **Historial de Alertas**: Cada envío se registra en la tabla `inv_mantenimiento_notificaciones` para asegurar que el mismo ticket no reciba alertas duplicadas del mismo tipo (ej. no volver a enviar la alerta de "7 días" si ya fue despachada).

## 3. Completar Mantenimiento

**UseCase:** `CompletarMantenimiento`

Al marcar un ticket como Completado:
1. Cierra la orden de mantenimiento (`ActivoMantenimiento`).
2. Reactiva el activo (`Activo` cambia a estado `Activo`).
3. Cierra la asignación en taller/almacén de reparación.
4. Asigna de regreso el activo al Almacén General u origen.
5. Si el ticket tiene `plan_id`, propaga y actualiza la `fecha_ultimo_mantenimiento` y la `fecha_proximo_mantenimiento` del plan asociado.

## 4. Catálogo de Jobs Consolidados (Todos de frecuencia Diario)

A continuación se detalla la frecuencia diaria y propósito de los únicos **4 Jobs principales** del módulo de Mantenimiento y Garantías:

| Job Laravel | Frecuencia | Programación por Defecto | Variable de Entorno | Descripción |
|---|---|---|---|---|
| `VerificarMantenimientosPreventivosJob` | **Diario** | `06:00` | `JOB_MANTENIMIENTO_PREVENTIVO_AT` | Escanea planes preventivos activos y genera nuevos tickets de forma silenciosa. |
| `VerificarGarantiasJob` | **Diario** | `06:15` | `JOB_MANTENIMIENTO_GARANTIAS_AT` | Envía alertas para activos cuyas garantías expiren en los próximos 30 días. |
| `SincronizarEstadoActivoJob` | **Diario** | `06:40` | `JOB_MANTENIMIENTO_SINCRONIZAR_AT` | Asegura que el estado físico del Activo se sincronice con el ticket al completarse. |
| `NotificarMantenimientosJob` | **Diario** | `07:00` | `JOB_MANTENIMIENTO_NOTIFICAR_PROXIMOS_AT` | Despacha el recordatorio unificado de próximas alertas, atrasadas y retrasos críticos. |

## 5. Visualización en Filament (Widgets Activos)

Los reportes y counts dinámicos se visualizan mediante dos widgets clave en la pantalla de control:
* **`ProximosMantenimientosWidget`**: Muestra la cantidad total de mantenimientos programados para los siguientes 7 y 30 días.
* **`MantenimientosVencidosWidget`**: Indica mantenimientos retrasados (estado `Programado` con fecha vencida) y prolongados (estado `En Proceso` con más de 15 días de retraso).

Los widgets se encuentran integrados en el dashboard principal de reportes: [ReportesActivos.php](file:///d:/Developer/laravel/hotel-bugambilias/app/Filament/Pages/Activos/ReportesActivos.php).
