# 📅 Jobs de Reservaciones y Restaurante

Este documento detalla el funcionamiento interno, interactores y flujo de ejecución de los **Jobs de Reservaciones y Restaurante**.

---

## 🍽️ 1. `ProcesarNoShowsRestauranteJob`

### Ubicación

- **Job:** `App\Jobs\Restaurante\ProcesarNoShowsRestauranteJob`
- **Interactor:** `App\Interactors\Restaurante\Mesas\ProcesarNoShowsRestaurante`

### Frecuencia de Ejecución

- **Frecuencia:** Cada 15 minutos (`everyfifteenminutes`).
- **Comando Manual:** `php artisan restaurante:procesar-noshows`.

### ¿Qué Hace? (Flujo Detallado)

1. **Búsqueda de Reservas Expiradas:** Consulta las reservaciones de tipo `RESTAURANTE` en estado `PENDIENTE` o `CONFIRMADA` cuya fecha sea el día de hoy.
2. **Evaluación de Tolerancia (30 min):** Calcula la diferencia entre la hora actual y la `hora_check_in` programada de la reserva.
3. **Cancelación Automática:** Si transcurrieron más de 30 minutos de tolerancia sin que el cliente haya ingresado:
    - Modifica el estado de la reserva a `CANCELADA`.
    - Agrega en las observaciones: `"Cancelada automáticamente por No-Show (Tolerancia excedida de 30 min)"`.
4. **Liberación de Mesas:** Si la reserva tenía una mesa asignada (`espacio_id`), cambia el estado del espacio a `Disponible` y desvincula los meta-datos de la reservación.

---

## 🔔 2. `EnviarRecordatoriosReservasJob`

### Ubicación

- **Job:** `App\Jobs\Reservas\EnviarRecordatoriosReservasJob`

### Frecuencia de Ejecución

- **Frecuencia:** Cada 5 minutos (`everyfiveminutes`).

### ¿Qué Hace? (Flujo Detallado)

1. **Identificación de Reservas Próximas:** Examina las reservas de habitación y restaurante programadas para ingresar en la siguiente ventana de tiempo (15 a 30 minutos).
2. **Generación de Alertas Internas:**
    - Para **Restaurante:** Notifica al capitán de meseros y cocina para verificar el montaje de la mesa.
    - Para **Habitaciones:** Notifica a Recepción para preparar el kit de llaves y confirmación de la habitación asignada.
3. **Notificación al Cliente:** Envía correo/notificación de recordatorio al cliente confirmando su número de reservación y ubicación.
