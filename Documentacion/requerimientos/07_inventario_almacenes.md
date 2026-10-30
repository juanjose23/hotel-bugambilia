# Requerimientos del Sistema: Inventario, Almacenes y Lotes

### 3. Requerimientos del Sistema

#### 3.1. Requerimientos Funcionales (RF)

##### Requerimientos del Catálogo de Productos y Variantes de Inventario
* **RF-70:** El software listará todos los artículos de inventario divididos por tipo (Consumible, Activo Fijo, Materia Prima, Insumo de Limpieza), mostrando en pantalla código, nombre, categoría, marca, unidad de medida, existencia total consolidada y estado operativo.
* **RF-71:** El S.I. permitirá registrar productos y sus variantes físicas (SKU, atributos de talla, color, volumen, empaque), solicitando nombre, categoría, unidad de medida base y variantes asociadas, verificando que el código SKU sea único en todo el catálogo.
* **RF-72:** Se asignará un código SKU individual a cada variante con el cual se registrarán las entradas en compras, despachos a habitaciones y consumos de cocina.
* *Excepción/Regla:* Se podrán modificar descripciones y fotos del producto a excepción de su unidad de medida y tipo de inventario una vez que registre movimientos en el Kardex.

##### Requerimientos de Stock Polimórfico y Movimientos de Inventario
* **RF-73:** El software controlará las existencias en múltiples ubicaciones físicas (Bodega Central, Cocina Restaurante, Bar Principal, Bodegas de Piso, Carritos de Camaristas, Habitaciones), mostrando en pantalla stock actual, stock mínimo de seguridad y cantidad ideal de abastecimiento por ubicación.
* **RF-74:** El S.I. registrará cada alteración de existencias mediante el modelo `MovimientoStock`, solicitando tipo de movimiento (`ENTRADA`, `SALIDA`, `TRASLADO`, `MERMA`, `AJUSTE`), lote de origen, cantidad y costo unitario, verificando que ninguna salida o traslado genere existencias negativas en la ubicación emisora.
* **RF-75:** Se asignará un identificador secuencial de movimiento de Kardex con el cual se garantizará la trazabilidad auditable del origen y destino de cada insumo.
* *Excepción/Regla:* Las transacciones de Kardex ya asentadas son estrictamente inmutables; cualquier corrección requerirá un nuevo movimiento compensatorio de tipo "Ajuste".

##### Requerimientos de Lotes, Caducidades y Estrategia FEFO
* **RF-76:** El software gestionará los productos perecederos mediante números de lote, fecha de fabricación y fecha de caducidad, mostrando en pantalla alertas visuales de vencimiento próximo (a 30, 15 y 7 días de expirar).
* **RF-77:** El S.I. aplicará de forma automatizada la estrategia de consumo FEFO (_First Expired, First Out_) al despachar insumos a cocina o camaristas, seleccionando prioritariamente los lotes con fecha de vencimiento más próxima antes de consumir lotes de mayor vigencia.
* **RF-78:** El job nocturno programado `VerificarCaducidadesJob` inspeccionará diariamente las existencias de todos los lotes a las 06:00 AM, marcando automáticamente los lotes caducados y emitiendo notificaciones críticas a los jefes de almacén y cocina.

##### Requerimientos del Flujo de Cuarentena de Lotes
* **RF-79:** Proceso de Evaluación y Disposición de Lotes en Cuarentena:
  1) Verificación técnica de calidad del lote recibido o retenido:
     * i) Condición A (Lote aprobado por control de calidad o subsanación de inconsistencias): El sistema ejecutará `ServicioCuarentena::liberar`, trasladará el stock a la ubicación disponible para consumo y disparará el evento `LoteLiberadoCuarentena`.
     * ii) Condición B (Lote dañado, contaminado o vencido): El sistema ejecutará `ServicioCuarentena::rechazar`, reclasificará el stock como pérdida, generará el movimiento de tipo `MERMA` con costo registrado y disparará el evento `LoteRechazadoCuarentena`.

##### Requerimientos de Traslados entre Bodegas y Reabastecimiento
* **RF-80:** El software permitirá solicitar y autorizar traslados internos de insumos (ej. Bodega General → Bodega de Piso → Carrito de Camarista), mostrando en pantalla el estado del traslado (Solicitado, En Tránsito, Recibido, Rechazado).
* **RF-81:** El S.I. ejecutará el traslado en transacción atómica (`ServicioTraslados`), solicitando lote de origen, cantidad y ubicación destino, verificando la disponibilidad física en origen antes de incrementar el stock en el destino.

##### Requerimientos de Inventario Físico y Reconciliación de Diferencias
* **RF-82:** Proceso de Conteo Físico y Cierre de Inventario:
  1) Verificación de las cantidades físicas contadas contra las existencias registradas en el sistema:
     * i) Condición A (Conteo físico idéntico a las existencias del sistema): El sistema validará el inventario sin requerir ajustes contables y emitirá el certificado de conformidad de stock.
     * ii) Condición B (Presencia de sobrantes o faltantes en el conteo físico): El sistema ejecutará `ConciliadorInventarioFisico`, ajustará el stock al valor real contado, generará los movimientos de `AJUSTE_ENTRADA` o `AJUSTE_SALIDA`, registrará el costo de la diferencia y emitirá el informe de variaciones para auditoría.

##### Requerimientos de Kits y Packs de Productos
* **RF-83:** El software permitirá agrupar insumos en kits (ej. "Kit de Bienvenida" = 2 toallas + 1 jabón + 1 shampoo), mostrando en pantalla los componentes y cantidades de cada pack.
* **RF-84:** El S.I. asignará kits a habitaciones mediante `ServicioAsignacionPacks`, verificando previamente la existencia de todos los componentes en la bodega de abastecimiento antes de descontar los insumos individuales e incrementar el inventario fijo de la habitación.
