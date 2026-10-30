# Requerimientos del Sistema: Restaurante, Cocina y Punto de Venta (POS)

### 3. Requerimientos del Sistema

#### 3.1. Requerimientos Funcionales (RF)

##### Requerimientos del Módulo de Platos y Menú Gastronómico
* **RF-42:** El software listará todos los platos del menú en el panel administrativo, mostrando en pantalla imagen circular, código (PLT-XXXX), nombre del platillo, categoría (Entradas, Platos Fuertes, Postres, Bebidas), tiempo estimado de preparación, estado activo/inactivo y visibilidad en portal web.
* **RF-43:** El S.I. permitirá registrar y actualizar platos, solicitando código único, nombre, categoría de menú, receta asociada (`producto_receta_id`), descripción, tiempo de preparación, galería fotográfica (hasta 3 imágenes) y tarifas por moneda (NIO y USD), verificando que el código no esté duplicado y que el nombre sea obligatorio.
* **RF-44:** Se asignará un código único autogenerado con formato `PLT-XXXX` con el cual se vincularán las recetas, órdenes de preparación en cocina y tickets de comanda.
* *Excepción/Regla:* Se podrán modificar ingredientes, tiempos y descripciones a excepción del código identificador del plato y de los consumos históricos ya imputados a pedidos previos.

##### Requerimientos del Cálculo Automático de Costos y Precios Sugeridos
* **RF-45:** El software calculará en tiempo real el costo técnico de preparación de cada plato sumando los costos de sus ingredientes según los lotes activos en el stock de "Cocina Restaurante" (`CalcularCostoPlato`), mostrando en pantalla el costo total unitario y el margen comercial sugerido (del 55% al 70% según tramos de costo).
* **RF-46:** El S.I. verificará la existencia de stock para cada insumo de la receta en la bodega de cocina, solicitando la variante requerida y verificando que la cantidad actual sea mayor a cero en los lotes vigentes.
* *Excepción/Regla:* Si algún ingrediente de la receta carece de stock físico en cocina, el sistema marcará el insumo con advertencia visual `con_stock: false` y alertará sobre la necesidad de reabastecimiento o sustitución.

##### Requerimientos de Transformación de Materia Prima y Conciliación de Recetas
* **RF-47:** El software dispondrá de un panel de transformación de materia prima en cocina (`/admin/restaurante/materia-prima-cocina`), mostrando en pantalla el producto bruto seleccionado, cantidad bruta a procesar, materia prima lista obtenida y porcentaje de merma resultante.
* **RF-48:** El S.I. permitirá registrar la transformación de material bruto en materia prima limpia, solicitando variante y ubicación origen, cantidad procesada, variante y ubicación destino, y merma producida, verificando que la suma de materia prima obtenida más la merma equivalga exactamente a la cantidad bruta extraída del inventario.
* **RF-49:** El software ofrecerá una vista de conciliación de recetas (`/admin/restaurante/conciliacion-recetas`) que evalúa el stock disponible de ingredientes contra las recetas de platos activos, clasificando cada ingrediente como `OK`, `Puede transformarse`, `Falta bruto`, `Sin regla de transformación`, `Receta incompleta` o `Variante inválida`.
* *Excepción/Regla:* Las mermas resultantes del proceso de desposte o limpieza de insumos en cocina se registrarán con movimiento de inventario de tipo `MERMA_COCINA` sin costo asignado a producto terminado.

##### Requerimientos del Módulo de Pedidos y Comandas de Mesa
* **RF-50:** El software gestionará los pedidos de restaurante en sala y barra, mostrando en pantalla código de pedido, mesa asignada, mesero responsable, estado operativo (`Abierto`, `En Preparación`, `Listo`, `Servido`, `Pagado`, `Cancelado`), desglose de platos con notas especiales y total monetario calculado automáticamente.
* **RF-51:** El S.I. permitirá abrir pedidos y registrar comandas, solicitando mesa activa, mesero y lista de platillos con cantidades y términos de cocción, verificando que la mesa se encuentre en estado disponible y cuente con mobiliario físico activo asignado (`ValidarEspacioOperativoConActivo`).
* **RF-52:** Se asignará un código correlativo único de pedido con el cual se imprimirá la comanda térmica de 80 mm para cocina (`ComandaController::imprimir`) y se enviará la orden a la pantalla KDS.
* *Excepción/Regla:* Una vez enviada una comanda a cocina, los ítems en estado "En Preparación" no podrán eliminarse del pedido directamente; requerirán una anulación justificada con autorización de supervisor de restaurante.

##### Requerimientos de la Pantalla de Cocina (KDS) y Consumo Automático de Stock
* **RF-53:** El software presentará la pantalla interactiva de cocina (KDS - Kitchen Display System), mostrando en pantalla los pedidos activos organizados por orden de llegada con temporizador de espera, platillos solicitados, términos y notas especiales del cliente.
* **RF-54:** El S.I. ejecutará el consumo automático de insumos en inventario (`ConsumirIngredientesPedido`) al momento en que el cocinero marque un plato como "Listo" en el KDS, solicitando la receta asociada y recalculando las porciones, verificando y descontando el stock del lote de cocina con registro de movimiento de tipo `CONSUMO`.
* **RF-55:** Si al despachar una comanda se detecta escasez de ingredientes, el sistema ejecutará `BloquearItemsPorFaltaStock`, marcando los platos afectados con estado `Bloqueado por Stock` y notificando al mesero para coordinar con el cliente la sustitución de ingredientes, cambio de platillo o retiro del ítem.

##### Requerimientos del Mapa Visual de Mesas y Operatividad
* **RF-56:** El software mostrará un mapa interactivo de mesas (`/admin/restaurante/mapa-mesas`) segmentado por zonas (Salón Principal, Terraza, Bar), mostrando en pantalla el estado en vivo (Disponible, Ocupada, Cuenta Abierta, Limpieza), capacidad de comensales, tiempo de ocupación y alerta de mobiliario físico.
* **RF-57:** El S.I. actualizará automáticamente el estado de la mesa mediante el `PedidoObserver`:
  1) Al crearse un pedido: cambiará la mesa a estado "Ocupada" y generará una solicitud de limpieza normal.
  2) Al marcarse el pedido como "Pagado" o "Cancelado": cambiará la mesa a estado "Limpieza" y generará una solicitud de limpieza urgente para habilitar la mesa rápidamente para nuevos comensales.

##### Requerimientos de Reportes y KPIs de Restaurante
* **RF-58:** El software proveerá un tablero de control gerencial de restaurante con actualización en vivo por sondeo cada 30 segundos, mostrando en pantalla total de pedidos atendidos, facturación consolidada, pedidos pendientes, ranking Top 10 de platillos más vendidos y recaudación desglosada por categoría.

##### Requerimientos del Proceso de Liquidación de Pedido y Cobro de Mesa
* **RF-59:** Proceso de Cobro de Cuenta de Restaurante:
  1) Verificación del total de consumos y modalidad de cobro solicitada:
     * i) Condición A (Pago en caja directo en efectivo, tarjeta o divisa): El sistema registrará el pago, calculará propina o cargo por servicio voluntario, emitirá el ticket de caja, marcará el pedido como "Pagado" y pasará la mesa a estado "Limpieza".
     * ii) Condición B (Cargo directo a habitación de huésped hospedado): El sistema verificará que la habitación cuente con cuenta de estancia activa y saldo de crédito disponible suficiente; de cumplirse, transferirá el importe del pedido a la cuenta maestra de la estancia; de no contar con crédito o estar en check-out, denegará la transferencia y exigirá el cobro presencial en el restaurante.
