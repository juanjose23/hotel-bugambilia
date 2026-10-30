# Requerimientos del Sistema: Promociones, Combos y Paquetes Especiales

### 3. Requerimientos del Sistema

#### 3.1. Requerimientos Funcionales (RF)

##### Requerimientos del Módulo de Gestión de Promociones (Admin)
* **RF-121:** El software listará las promociones comerciales del hotel en el panel administrativo, mostrando en pantalla imagen de portada, código (PROM-XXXX), nombre de la promoción, tipo (Descuento por Temporada, Paquete/Combo, Estancia Prolongada, Reserva Anticipada, Evento Especial), fechas de vigencia (inicio y fin), porcentaje o monto de descuento, estado activo/inactivo y visibilidad web.
* **RF-122:** El S.I. permitirá registrar y editar promociones, solicitando nombre, tipo de promoción (`TIPO_PROMOCION`), fechas de vigencia, tipo de descuento (porcentaje o monto fijo), descripción, términos y condiciones, y galería de imágenes (hasta 3 fotografías), verificando que la fecha de fin sea posterior a la fecha de inicio.
* **RF-123:** Se asignará un código único autogenerado con formato `PROM-XXXX` con el cual se identificarán los paquetes promocionales en el motor de reservas y en el catálogo web.
* *Excepción/Regla:* Las promociones aplicadas a reservas ya confirmadas o facturadas mantendrán inmutables los descuentos acordados, aun cuando la promoción expire o sea modificada con posterioridad.

##### Requerimientos de Paquetes y Combos con Ítems Polimórficos
* **RF-124:** El software permitirá armar paquetes combinados incorporando tanto habitaciones como servicios adicionales (ej. "Escapada Romántica" = Suite Presidencial + Masaje Relajante + Jacuzzi + Cena de 3 Tiempos), mostrando en pantalla la lista de ítems incluidos y el precio especial asignado a cada componente.
* **RF-125:** El S.I. permitirá vincular ítems al paquete mediante un selector dinámico polimórfico (`promocion_items`), solicitando el tipo de entidad (`Servicio` o `Habitación`), cargando dinámicamente el catálogo correspondiente y permitiendo especificar una tarifa especial de paquete o aplicar el descuento porcentual global.
* **RF-126:** El software permitirá asociar políticas de reserva y cancelación específicas (`PoliticasRelationManager`) y stocks consumibles de cortesía (`StocksRelationManager`) a cada promoción.

##### Requerimientos del Proceso de Aplicación de Promoción en Reservas
* **RF-127:** Proceso de Validación y Aplicación de Promoción en Reserva:
  1) Verificación de la vigencia temporal y condiciones mínimas de la promoción (`AplicarPromocionReserva`):
     * i) Condición A (Fecha de estancia dentro del rango de vigencia, cumplimiento del mínimo de noches y disponibilidad de los ítems del paquete): El sistema ejecutará el cálculo del descuento comercial sobre el total de la reserva, desglosará el beneficio en el resumen de compra y asociará la promoción a la orden.
     * ii) Condición B (Promoción caducada, no aplicable para las fechas seleccionadas o agotamiento de cupos promocionales): El sistema denegará la aplicación del descuento, alertará con un mensaje explicativo al usuario y cotizará la reserva a tarifa regular estándar.

##### Requerimientos del Portal Web de Promociones
* **RF-128:** El software presentará las promociones vigentes con visibilidad web activa en el portal público, mostrando en pantalla banners visuales atractivos, etiquetas de descuento (ej. "20% OFF", "7x6 Noches"), desglose de amenidades incluidas y botón directo de "Reservar Paquete" que preconfigura la habitación y servicios en el motor de reservas.
