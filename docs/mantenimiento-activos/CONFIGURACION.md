# Configuración — Mantenimiento de Activos Fijos

## Frecuencia y Horarios de Jobs (diario, vía ENV)

Todos los Jobs automáticos del programador se ejecutan de forma **DIARIA** a la hora configurada en tu archivo `.env`.

| Variable de Entorno | Valor por Defecto | Job Laravel | Frecuencia | Descripción |
|---|---|---|---|---|
| `JOB_MANTENIMIENTO_PREVENTIVO_AT` | `06:00` | `VerificarMantenimientosPreventivosJob` | **Diario** | Escanea planes preventivos vencidos y genera sus tickets de mantenimiento correspondientes de manera silenciosa. |
| `JOB_MANTENIMIENTO_GARANTIAS_AT` | `06:15` | `VerificarGarantiasJob` | **Diario** | Verifica y notifica a los administradores y técnicos sobre garantías de activos próximas a vencer en los siguientes 30 días. |
| `JOB_MANTENIMIENTO_SINCRONIZAR_AT` | `06:40` | `SincronizarEstadoActivoJob` | **Diario** | Sincroniza el estado de los activos con base en mantenimientos que hayan sido completados o que estén en curso. |
| `JOB_MANTENIMIENTO_NOTIFICAR_PROXIMOS_AT` | `07:00` | `NotificarMantenimientosJob` | **Diario** | Notificador unificado (anti-spam): envía alertas a técnicos y administradores sobre tickets próximos (7, 3, 1 días, hoy) y vencidos/críticos. |

> [!IMPORTANT]
> **Frecuencia Diaria**: Todos los Jobs están programados para correr **una vez al día** en la hora asignada en formato de 24 horas (`HH:MM`).
> Para desactivar por completo la ejecución programada de cualquier Job, puedes definir su variable de entorno como `null` en tu `.env`.

### Cómo probar o forzar la ejecución en desarrollo local

1. **Simular Cron (Recomendado)**:
   Abre una terminal dedicada en tu entorno de desarrollo y ejecuta:
   ```bash
   php artisan schedule:work
   ```
   Este proceso verificará cada minuto tu archivo de configuración y despachará las tareas en el minuto exacto que configuraste en tu `.env`.

2. **Forzar ejecución manual inmediata (Tinker)**:
   Si deseas correr el proceso inmediatamente sin esperar a que marque el reloj, abre la consola interactiva:
   ```bash
   php artisan tinker
   ```
   Y ejecuta el Job que deseas probar síncronamente:
   ```php
   App\Jobs\Activos\VerificarMantenimientosPreventivosJob::dispatchSync();
   ```

3. **Ejecutar todos a la vez vía consola**:
   Hemos preparado un comando Artisan exclusivo para forzar todos los Jobs de mantenimiento en un solo llamado:
   ```bash
   php artisan mantenimiento:procesar-todos
   ```

## Tabla: `act_plan_activos` (pivot)

Conecta `inv_planes_mantenimiento` con `inv_activos`.

```sql
CREATE TABLE act_plan_activos (
    act_plan_mantenimiento_id BIGINT NOT NULL REFERENCES inv_planes_mantenimiento(id),
    activo_id                 BIGINT NOT NULL REFERENCES inv_activos(id),
    PRIMARY KEY (act_plan_mantenimiento_id, activo_id)
);
```

## Columnas agregadas a `inv_planes_mantenimiento`

| Columna | Tipo | Descripción |
|---|---|---|
| `fecha_ultimo_mantenimiento` | `date` nullable | Última vez que se ejecutó el plan |
| `fecha_proximo_mantenimiento` | `date` nullable | Próxima fecha calculada |

## Enlaces

- [Flujo completo](FLUJO.md)
