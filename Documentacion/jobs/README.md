# ⚙️ Guía General de Background Jobs y Programación del Sistema

Este documento describe la arquitectura, métodos de ejecución, horarios y comandos manuales de los **Jobs en Segundo Plano** del sistema Hotel Bugambilias.

---

## 📌 Regla de Arquitectura para Jobs (`AGENTS.md` — Regla 11)

Todo Job en el sistema sigue estrictamente la separación de capas:

```
Scheduler / Worker / Artisan Command
                  |
                  v
                 Job
                  |
                  v
              Interactor
                  |
                  v
      BusinessLogic & Repository
```

**Principios:**

- Un **Job NO debe contener lógica de negocio directa**.
- Su única responsabilidad es recibir el evento/tarea, instanciar y ejecutar el **Interactor** correspondiente.
- Permite la ejecución tanto asíncrona (Queue Worker) como síncrona (Artisan CLI).

---

## ⏰ Programación Global de Jobs (Crontab / Scheduler)

Los jobs están programados en `routes/console.php` y se gestionan mediante el scheduler de Laravel (`php artisan schedule:run`).

| #   | Job / Comando                           | Frecuencia / Horario                             | Propósito Principal                                                 | Comando Manual CLI                              |
| --- | --------------------------------------- | ------------------------------------------------ | ------------------------------------------------------------------- | ----------------------------------------------- |
| 1   | `ProcesarNoShowsRestauranteJob`         | Cada 15 min (`everyfifteenminutes`)              | Expira reservas de restaurante con +30 min de atraso y libera mesas | `php artisan restaurante:procesar-noshows`      |
| 2   | `EnviarRecordatoriosReservasJob`        | Cada 5 min (`everyfiveminutes`)                  | Notifica reservaciones de hotel y restaurante próximas a ingresar   | N/A                                             |
| 3   | `VerificarCaducidadesJob`               | Diaria 06:00 AM (`jobs.inventario_caducidades`)  | Alerta sobre productos próximos a vencer y marca lotes caducados    | `php artisan inventario:verificar-caducidades`  |
| 4   | `VerificarMantenimientosPreventivosJob` | Diaria 06:00 AM (`jobs.mtto_preventivo`)         | Genera órdenes de mantenimiento preventivo para activos             | `php artisan mantenimiento:procesar-todos`      |
| 5   | `NotificarMantenimientosJob`            | Diaria 07:00 AM (`jobs.mtto_notificar_proximos`) | Envía alertas de mantenimientos programados                         | `php artisan mantenimiento:procesar-todos`      |
| 6   | `SincronizarEstadoActivoJob`            | Diaria 06:40 AM (`jobs.mtto_sincronizar`)        | Restaura estado de activos cuyo mantenimiento finalizó              | `php artisan mantenimiento:procesar-todos`      |
| 7   | `VerificarGarantiasJob`                 | Diaria 06:15 AM (`jobs.mtto_garantias`)          | Advierte sobre garantías de activos por vencer en < 30 días         | `php artisan mantenimiento:procesar-todos`      |
| 8   | `limpieza:materializar-ejecuciones`     | Cada Hora (`hourly`)                             | Crea hojas de trabajo de limpieza para el turno correspondiente     | `php artisan limpieza:materializar-ejecuciones` |
| 9   | `limpieza:enviar-recordatorios`         | Diaria 12:00 PM (`12:00`)                        | Recordatorio de habitaciones pendientes de limpieza                 | `php artisan limpieza:enviar-recordatorios`     |

---

## 🚀 Modos de Ejecución de los Jobs

### 1. Servidor de Producción (Crontab del Sistema Operativo)

En el servidor Linux de producción, agregar la siguiente entrada a `crontab -e`:

```bash
* * * * * cd /var/www/hotel-bugambilia && php artisan schedule:run >> /dev/null 2>&1
```

### 2. Cola en Segundo Plano (Queue Worker)

Para procesar las colas de background jobs en producción:

```bash
php artisan queue:work --queue=default --tries=3 --timeout=90
```

### 3. Ejecución Manual en Desarrollo (Comandos Artisan)

Se pueden forzar individualmente en consola sin esperar la hora del reloj:

```bash
# Procesar No-Shows de Restaurante
php artisan restaurante:procesar-noshows

# Verificar productos e insumos vencidos de inventario
php artisan inventario:verificar-caducidades

# Procesar todos los jobs de mantenimiento y garantías de activos
php artisan mantenimiento:procesar-todos
```
