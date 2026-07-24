# Arquitectura de reservaciones

## Alcance

Esta primera etapa normaliza las reservaciones de habitaciones, espacios y servicios. Incluye recursos reservables, detalles, huéspedes opcionales e historial de estados.

Los pagos quedan expresamente fuera de esta etapa y se diseñarán en otra sesión. Tampoco se eliminan todavía las columnas heredadas de `reservas` ni la tabla `reserva_servicios`.

## Objetivos

- Permitir varios recursos dentro de una misma reserva.
- Evitar agregar una columna nullable por cada nuevo tipo de recurso.
- Mantener estados como enteros estables.
- Separar la cabecera comercial de los periodos y recursos reservados.
- Registrar huéspedes por habitación o detalle cuando sean necesarios.
- Conservar trazabilidad de cada cambio de estado.

## Modelo relacional

```text
reservas
  ├── reserva_detalles
  │     ├── recursos_reservables
  │     │     ├── habitaciones
  │     │     ├── espacios
  │     │     └── servicios
  │     └── reserva_huespedes
  └── reserva_estado_historial
```

`reservas` es la cabecera. `reserva_detalles` contiene cada recurso, periodo, cantidad y precio contratado. Un detalle puede tener huéspedes y puede depender de otro detalle mediante `parent_id`.

## Diccionario de datos

### recursos_reservables

Registro común para cualquier entidad que pueda reservarse.

| Columna                  | Descripción                                     |
| ------------------------ | ----------------------------------------------- |
| `tipo`                   | 1 habitación, 2 espacio, 3 servicio, 4 paquete. |
| `nombre`                 | Nombre presentado durante la reserva.           |
| `capacidad`              | Máximo de personas o unidades; puede ser nulo.  |
| `control_disponibilidad` | 1 fechas, 2 horario, 3 cupos, 4 sin bloqueo.    |
| `duracion_minutos`       | Duración estándar para servicios o espacios.    |
| `estado`                 | 1 activo, 2 inactivo, 3 mantenimiento.          |

Las tablas `habitaciones`, `espacios` y `servicios` reciben un `reservable_id` único y nullable. Será nullable durante la transición de datos.

La entidad específica continúa siendo la fuente de verdad de sus datos descriptivos y físicos. `recursos_reservables.nombre`, `capacidad` y `estado` son la proyección comercial usada por disponibilidad y deben sincronizarse mediante un Interactor, nunca mediante actualizaciones manuales independientes.

### reserva_detalles

| Columna                              | Descripción                                              |
| ------------------------------------ | -------------------------------------------------------- |
| `reserva_id`                         | Cabecera comercial propietaria.                          |
| `reservable_id`                      | Recurso contratado.                                      |
| `parent_id`                          | Detalle principal del que depende un servicio adicional. |
| `estado`                             | Estado operativo del detalle.                            |
| `fecha_inicio`, `fecha_fin`          | Periodo de ocupación o prestación.                       |
| `cantidad`                           | Unidades contratadas.                                    |
| `adultos`, `ninos`                   | Ocupación prevista.                                      |
| `precio_unitario`                    | Precio congelado al confirmar la reserva.                |
| `descuento`, `impuestos`, `subtotal` | Composición financiera del detalle.                      |

Estados del detalle:

| Código | Estado     |
| -----: | ---------- |
|      1 | Pendiente  |
|      2 | Confirmado |
|      3 | En uso     |
|      4 | Completado |
|      5 | Cancelado  |

### reserva_huespedes

Es opcional. Se utiliza cuando debe conocerse quién ocupará una habitación o recibirá un servicio. El cliente que compra puede ser distinto del huésped.

| Columna                                 | Descripción                              |
| --------------------------------------- | ---------------------------------------- |
| `reserva_detalle_id`                    | Detalle al que pertenece el huésped.     |
| `nombre`                                | Nombre completo.                         |
| `tipo_identificacion`, `identificacion` | Documento opcional de check-in.          |
| `tipo_huesped`                          | 1 adulto, 2 niño, 3 infante.             |
| `es_titular`                            | Identifica al responsable del detalle.   |
| `fecha_nacimiento`                      | Dato opcional para validaciones de edad. |

### reserva_estado_historial

Tabla inmutable para auditoría del estado general de la reserva.

| Columna           | Descripción                                                    |
| ----------------- | -------------------------------------------------------------- |
| `reserva_id`      | Reserva modificada.                                            |
| `estado_anterior` | Estado antes de la operación; nulo al crear.                   |
| `estado_nuevo`    | Estado resultante.                                             |
| `motivo`          | Explicación opcional.                                          |
| `usuario_id`      | Usuario responsable; puede ser nulo para procesos automáticos. |
| `created_at`      | Momento de la transición.                                      |

## Flujo general

```text
1. Seleccionar habitación, espacio o servicio.
2. Resolver su registro en recursos_reservables.
3. Consultar disponibilidad según control_disponibilidad.
4. Crear la cabecera en reservas con estado 1 (Pendiente).
5. Crear uno o varios reserva_detalles.
6. Calcular subtotal y total desde los detalles.
7. Registrar huéspedes opcionales por detalle.
8. Registrar la transición inicial en reserva_estado_historial.
9. Confirmar la reserva y congelar precios.
10. Iniciar y completar cada detalle según su tipo.
11. Completar la reserva cuando todos sus detalles estén finalizados.
```

## Reglas de integridad

- Todos los estados se almacenan como números enteros; sus etiquetas viven en enums PHP.
- Un `reservable_id` debe corresponder al tipo declarado en `recursos_reservables`.
- `fecha_fin` debe ser posterior a `fecha_inicio` cuando exista.
- `cantidad` debe ser mayor que cero.
- Importes, descuentos e impuestos no pueden ser negativos.
- Los recursos inactivos o en mantenimiento no pueden recibir nuevos detalles.
- El historial de estados no se actualiza ni se elimina desde la aplicación.
- La disponibilidad se vuelve a comprobar dentro de la misma transacción que crea el detalle.

## Disponibilidad por ámbito

### Habitaciones

Control por intervalo de entrada y salida. No se permite superposición con detalles activos del mismo recurso.

### Espacios

Control por fecha y horario. La capacidad solicitada no puede superar `capacidad`.

### Servicios

Puede controlarse por horario, cupos o no requerir bloqueo. La estrategia se selecciona mediante `control_disponibilidad`.

## Estrategia de adopción

1. Ejecutar las nuevas migraciones sin borrar estructuras anteriores.
2. Crear un recurso reservable para cada habitación, espacio y servicio.
3. Asignar `reservable_id` a las entidades existentes.
4. Transformar cada reserva actual en uno o más detalles.
5. Convertir el JSON de acompañantes a huéspedes cuando aplique.
6. Comparar conteos, fechas, totales y relaciones.
7. Cambiar las consultas y formularios para leer `reserva_detalles`.
8. Mantener temporalmente lectura compatible con la estructura anterior.
9. Retirar columnas heredadas solamente en una fase posterior aprobada.

## Compatibilidad implementada

- Las creaciones nuevas escriben la cabecera heredada y el nuevo detalle normalizado durante la transición.
- Los servicios adicionales se conservan en `reserva_servicios` y también se crean como detalles hijos.
- El portal mantiene el texto heredado `detalles` y publica la estructura normalizada en `items`.
- Los formularios administrativos delegan la creación y los cambios de estado a Interactors.
- La edición de datos generales también pasa por `ActualizarReserva` y el repositorio; Filament no persiste directamente.
- Filament muestra los detalles normalizados, sus huéspedes y el historial como relaciones de solo lectura.
- Durante la creación, Filament permite seleccionar múltiples servicios y espacios adicionales; cada uno se guarda como detalle hijo con su tarifa y periodo.
- Las transiciones vuelven a validar el estado después de bloquear la reserva para evitar carreras concurrentes.
- Los estados terminales o reprogramados de detalles no se sobrescriben al cambiar la cabecera.
- Los campos estructurales de una reserva existente quedan bloqueados en edición para evitar desincronización.
- El backfill reconoce estados históricos guardados como texto o como entero.

## Migraciones de esta etapa

- `create_recursos_reservables_table`
- `add_reservable_id_to_habitaciones_espacios_servicios`
- `create_reserva_detalles_table`
- `create_reserva_huespedes_table`
- `create_reserva_estado_historial_table`
- `backfill_recursos_y_detalles_reservas`
- `normalize_reserva_estado_to_integer`

No se crea ninguna tabla de pagos en esta etapa.
