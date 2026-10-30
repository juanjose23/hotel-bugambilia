# Requerimientos del Sistema: Servicios del Hotel

### 3. Requerimientos del Sistema

#### 3.1. Requerimientos Funcionales (RF)

##### Requerimientos del Módulo de Gestión de Servicios (Admin)
* **RF-08:** El software listará todos los servicios del hotel en una tabla administrativa paginada, mostrando en pantalla imagen representativa, código secuencial, nombre, categoría, estado activo/inactivo, indicador de visibilidad web y fechas de registro.
* **RF-09:** El S.I. permitirá registrar y actualizar servicios del hotel, solicitando nombre, categoría de catálogo (`CATEGORIA_SERVICIO`), estado, indicador de publicación web, icono gráfico de catálogo y descripción detallada, verificando que el nombre no se encuentre duplicado dentro de la misma categoría.
* **RF-10:** Se asignará un código único autogenerado con formato `SRV-XXXX` con el cual se identificará inequívocamente el servicio en presupuestos, folios de venta y comandas.
* *Excepción/Regla:* Se podrán modificar la descripción, categoría, estado, imágenes y tarifas a excepción del código `SRV-XXXX`, el cual permanecerá inmutable.

##### Requerimientos del Módulo de Galería y Tarifas de Servicios
* **RF-11:** El software permitirá asociar una galería fotográfica polimórfica (hasta 5 imágenes) y una matriz de precios por moneda (NIO y USD), mostrando en pantalla la vista previa de las imágenes cargadas y el desglose de precios vigentes.
* **RF-12:** El S.I. permitirá asignar y actualizar precios vigentes por moneda, solicitando tipo de precio, monto numérico, fecha de inicio y fin de vigencia, verificando que no existan dos precios activos simultáneamente para el mismo tipo de precio y moneda en el mismo rango de fechas.
* **RF-13:** Se asignará un identificador polimórfico de precio con el cual se auditará la tarifa cobrada al momento de consumir el servicio en la estancia.
* *Excepción/Regla:* Los precios asociados a folios o cuentas ya facturadas serán inmutables; cualquier cambio tarifario generará una nueva vigencia sin alterar consumos pasados.

##### Requerimientos del Portal Web de Servicios y Consultas Públicas
* **RF-14:** El software presentará el catálogo público de servicios en el portal web (`/servicios`), mostrando en pantalla filtros por categoría interactivos (pills), tarjetas visuales con imagen optimizada, nombre, extracto, precio base desde y botón de consulta directa.
* **RF-15:** El S.I. permitirá consultar el detalle individual del servicio (`/servicios/{slug}`), solicitando el slug público y verificando que el servicio tenga estado "Activo" y visibilidad "Web" habilitada antes de renderizar la información.
* *Excepción/Regla:* Si el servicio no cuenta con imágenes personalizadas cargadas, el sistema presentará una imagen temática por defecto según la categoría asignada (Restaurante, Bar, Spa, Transporte).

##### Requerimientos del Proceso de Solicitud de Información de Servicios
* **RF-16:** Proceso de Solicitud de Cotización / Reserva de Servicio:
  1) Verificación de la disponibilidad operativa y requerimientos previos del servicio:
     * i) Condición A (Servicio activo con capacidad disponible y datos de contacto completos): El sistema registrará la intención del cliente, enviará notificación al departamento correspondiente y retornará confirmación en pantalla con número de folio de seguimiento.
     * ii) Condición B (Servicio no disponible por mantenimiento, cupo agotado o datos de contacto inválidos): El sistema impedirá el envío de la solicitud, informará al cliente sobre la no disponibilidad temporal y sugerirá servicios alternativos de la misma categoría.
