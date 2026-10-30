# Requerimientos del Sistema: Habitaciones, Espacios y Mobiliario Físico

### 3. Requerimientos del Sistema

#### 3.1. Requerimientos Funcionales (RF)

##### Requerimientos del Módulo de Gestión de Habitaciones (Admin)
* **RF-17:** El software listará las habitaciones del hotel con soporte para papelera de reciclaje (soft deletes), mostrando en pantalla miniatura de imagen, código único, número de habitación, nombre descriptivo, categoría, estado operativo actual, tarifa y ubicación física (edificio/nivel).
* **RF-18:** El S.I. permitirá registrar y actualizar habitaciones, solicitando código, número de habitación, categoría de catálogo (`CATEGORIA_HABITACION`), ubicación física, detalle de capacidades (adultos, niños), dimensiones en metros cuadrados, tipo de vistas, camas e inventario de amenidades, verificando la unicidad del número de habitación dentro de la misma ubicación.
* **RF-19:** Se asignará un código único alfanumérico (ej. `HAB-XXXX`) y un slug amigable con el cual se vincularán las reservas, comandas de restaurante, registros de housekeeping y el portal web.
* *Excepción/Regla:* Se podrán modificar las características físicas y descripciones de la habitación a excepción del código de habitación y los históricos de ocupación y facturación previos.

##### Requerimientos de la Clonación Rápida de Habitaciones
* **RF-20:** El software permitirá la clonación integral de una habitación existente, mostrando en pantalla un diálogo de confirmación con los datos base a replicar.
* **RF-21:** El S.I. permitirá duplicar una habitación seleccionada, solicitando el nuevo número identificador y verificando que dicho número no se encuentre asignado a otra habitación activa.
* **RF-22:** Se asignará un nuevo código autogenerado secuencial y un nuevo slug con el cual se crearán en una única transacción de base de datos la copia de `DetalleHabitacion`, `ServicioAsignaciones`, precios vigentes, políticas y stocks de lencería.
* *Excepción/Regla:* La habitación resultante se creará por defecto en estado "Disponible" sin heredar reservas pasadas ni consumos pendientes de la habitación origen.

##### Requerimientos del Módulo de Espacios, Sub-espacios y Jerarquías Físicas
* **RF-23:** El software modelará la estructura arquitectónica del hotel en forma de árbol jerárquico multinivel (Hotel → Espacio Contenedor → Áreas/Zonas → Puntos de Servicio/Mesas), mostrando en pantalla el tipo de espacio (`RESTAURANTE`, `AMBIENTE`, `TERRAZA`, `BAR`, `MESA`, `SALON`, `GYM`, `SPA`, `PISCINA`, `CANCHA`), nivel de anidación, capacidad de personas, estado y total de elementos subordinados.
* **RF-24:** El S.I. permitirá crear y configurar sub-espacios mediante paneles laterales deslizables (_Slide-Over_), solicitando nombre, tipo de espacio, capacidad máxima de comensales, orden visual, estado y mobiliario/activos fijos asociados, verificando que no se supere la capacidad máxima de mesas configurada en el restaurante padre.
* **RF-25:** Se asignará un código correlativo único (ej. `MESA-0001`, `TERR-0001`, `SALN-0001`) con el cual se identificarán los centros de consumo en el punto de venta y reservas.
* *Excepción/Regla:* Se podrán reubicar o renombrar los sub-espacios a excepción de su tipo de espacio y su identificador numérico primario una vez que hayan registrado transacciones de comandas o consumos.

##### Requerimientos de la Regla de Operatividad de Mesas basada en Mobiliario Físico
* **RF-26:** Proceso de Validación Operativa de Mesa para Apertura de Comanda:
  1) Verificación de la vinculación de activos fijos activos en la mesa (`ValidarEspacioOperativoConActivo`):
     * i) Condición A (Mesa con al menos un activo fijo asignado con asignación activa en `inventarioFijo`): El sistema habilitará la mesa en el mapa interactivo con badge de disponible y permitirá abrir comandas y registrar pedidos.
     * ii) Condición B (Mesa sin activo fijo asignado o con asignación finalizada): El sistema marcará la mesa con badge rojo "Sin Mobiliario", bloqueará la apertura de comandas e impedirá su selección en el TPV hasta que se asigne formalmente el mobiliario desde el inventario de activos.

##### Requerimientos del Portal Web de Habitaciones
* **RF-27:** El software presentará las habitaciones públicas agrupadas por categoría en el portal web (`/habitaciones`), mostrando en pantalla tarjetas interactivas con badge de disponibilidad semántico ("3 de 5 disponibles" en verde, "Última disponible" en ámbar, "Agotado" en rojo), capacidad de huéspedes, precio mínimo desde y acceso al detalle.
* **RF-28:** El S.I. permitirá consultar el detalle completo de la habitación (`/habitaciones/{slug}`), solicitando el slug público y verificando que la habitación cuente con estado activo y precios vigentes en el sistema, cargando la galería fotográfica, amenidades, políticas de cancelación y habitaciones similares recomendadas.
