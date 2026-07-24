# Documentación de Flujos de Procesos: Módulo Inventario

## 1. Submódulo / Funcionalidad: Gestión de Productos y Variantes

- **Descripción de la Pantalla / Vista:** Tabla con productos. Formulario con: código, nombre, categoría, marca, unidad de medida, tipo (consumible/activo fijo). Variantes con atributos (talla, color, SKU). Precios con moneda y vigencia. Imágenes polimórficas.
- **Disparador (Trigger):** Acceso desde `Inventario > Productos` en el panel admin.
- **Flujo Paso a Paso:**
    1. El usuario accede a la interfaz de Productos y visualiza la tabla con filtros (categoría, marca, estado).
    2. El sistema valida los permisos y muestra los datos con relaciones eager-loaded.
    3. El usuario crea un producto: nombre, categoría, marca, unidad de medida.
    4. El usuario agrega variantes: SKU, atributos, precios.
    5. El sistema sube imágenes vía FileUpload polimórfico.
    6. El usuario hace clic en "Crear".
    7. El sistema registra el producto y sus variantes.

---

## 2. Submódulo / Funcionalidad: Stock y Movimientos

- **Descripción de la Pantalla / Vista:** Tabla de stock actual por ubicación. Relación polimórfica: stock puede estar en bodega, habitación, carrito de limpieza, servicio. Movimientos de stock registran entradas, salidas, traslados, mermas.
- **Disparador (Trigger):** Visualización desde `Inventario > Stock` o automáticamente al recepcionar compras.
- **Flujo Paso a Paso:**
    1. El sistema mantiene el stock mediante el modelo polimórfico `Stock`:
        - `stockable_type` + `stockable_id` (Bodega, Habitación, Espacio, Carrito)
        - `cantidad_actual`, `cantidad_ideal`, `cantidad_minima`
    2. Cada operación que afecta el stock genera un `MovimientoStock`:
        - Tipo: Entrada (recepción), Salida (consumo/venta), Traslado (entre ubicaciones), Merma (pérdida), Ajuste (inventario físico).
        - Cantidad, costo unitario, lote, ubicación origen/destino.
    3. El usuario puede consultar el historial de movimientos por producto, lote o ubicación.

---

## 3. Submódulo / Funcionalidad: Lotes y Caducidades

- **Descripción de la Pantalla / Vista:** Gestión de lotes con número de lote, fecha de fabricación, fecha de vencimiento. Alertas de productos próximos a vencer.
- **Disparador (Trigger):** Automático al recepcionar compras. Job programado `VerificarCaducidadesJob` (diario 06:00).
- **Flujo Paso a Paso:**
    1. Al recepcionar una compra, el `CreadorLoteRecepcion` genera lotes con fechas de vencimiento.
    2. Las `ReglasLotesRecepcion` determinan si el lote va a disponible o cuarentena.
    3. El job programado `VerificarCaducidadesJob` revisa lotes próximos a vencer (30, 15, 7 días).
    4. Los lotes vencidos se marcan y se notifica al administrador.
    5. El sistema usa `FEFOStrategy` (First Expired First Out) para consumo de inventario.

---

## 4. Submódulo / Funcionalidad: Cuarentena de Lotes

- **Descripción de la Pantalla / Vista:** Flujo de cuarentena: enviar a cuarentena, liberar (aprobar), rechazar (merma). Tabla de lotes en cuarentena.
- **Disparador (Trigger):** Manual desde el admin o automático en recepción según `ReglasLotesRecepcion`.
- **Flujo Paso a Paso:**
    1. El usuario selecciona un lote y lo envía a cuarentena (`ServicioCuarentena::enviarACuarentena`).
    2. El sistema mueve el stock a la ubicación de cuarentena.
    3. El usuario evalúa el lote y decide:
        - **Liberar**: mueve stock de vuelta a disponible. Dispara evento `LoteLiberadoCuarentena`.
        - **Rechazar**: convierte en merma. Dispara evento `LoteRechazadoCuarentena`.
    4. Todo movimiento queda registrado en `MovimientoStock`.

---

## 5. Submódulo / Funcionalidad: Traslados entre Bodegas

- **Descripción de la Pantalla / Vista:** Formulario para mover stock entre ubicaciones (bodega central → bodega de piso, bodega → carrito de limpieza).
- **Disparador (Trigger):** Manual desde `Inventario > Traslados` o automático vía `ReabastecedorFefo`.
- **Flujo Paso a Paso:**
    1. El usuario selecciona el lote origen, ubicación destino y cantidad.
    2. El sistema valida disponibilidad (`ValidacionLotes::validarCantidadTraslado`).
    3. El `ServicioTraslados` ejecuta el traslado en transacción:
        - Reduce stock en origen.
        - Crea/incrementa stock en destino.
        - Actualiza `ubicacion_id` del lote si es traslado completo.
        - Registra movimiento de tipo Traslado.
    4. El `ReabastecedorFefo` automatiza el reabastecimiento de carritos usando FEFO.

---

## 6. Submódulo / Funcionalidad: Inventario Físico

- **Descripción de la Pantalla / Vista:** Conteo físico de inventario con reconciliación de diferencias. Importación de datos desde spreadsheet.
- **Disparador (Trigger):** Manual desde `Inventario > Inventario Físico`.
- **Flujo Paso a Paso:**
    1. El usuario inicia un conteo de inventario físico para una ubicación.
    2. Registra las cantidades contadas manualmente o importa desde archivo.
    3. El `ConciliadorInventarioFisico` compara cantidades del sistema vs conteo:
        - Calcula diferencias (sobrante/faltante).
        - Ajusta stock y registra movimientos de tipo Ajuste.
        - Actualiza costos según método de valoración.
    4. El sistema genera reporte de diferencias.

---

## 7. Submódulo / Funcionalidad: Kits y Packs de Productos

- **Descripción de la Pantalla / Vista:** Definición de kits (ej: "Kit de Bienvenida" = 2 toallas + 1 jabón + 1 shampoo). Asignación de kits a habitaciones.
- **Disparador (Trigger):** Manual desde `Inventario > Kits` (PackResource).
- **Flujo Paso a Paso:**
    1. El usuario crea un Kit con nombre y items (variante + cantidad).
    2. El sistema valida que los items existan en el catálogo.
    3. El `ServicioAsignacionPacks` asigna el kit a una habitación:
        - Consume stock de la bodega de abastecimiento.
        - Crea/actualiza stock en la habitación destino.
        - Registra movimientos de consumo.
    4. El sistema verifica disponibilidad del pack completo antes de asignar (`VerificarDisponibilidadPack`).

---

## Arquitectura del Módulo

```
app/
├── Repository/Models/
│   ├── Catalogos/Producto.php, ProductoVariante.php
│   ├── Shared/Stock.php, MovimientoStock.php
│   └── Inventario/Lote.php, Kit.php
├── BusinessLogic/Inventario/
│   ├── Servicios/
│   │   ├── ServicioTraslados.php           ← Traslados entre ubicaciones
│   │   ├── ServicioCuarentena.php          ← Ciclo de cuarentena
│   │   ├── ServicioMermas.php              ← Registro de mermas
│   │   ├── ServicioConsumos.php            ← Consumo de stock
│   │   └── CreadorLoteRecepcion.php        ← Creación de lotes en recepción
│   ├── Validacion/ValidacionLotes.php      ← Reglas de validación
│   ├── Estrategias/
│   │   ├── FEFOStrategy.php               ← First Expired First Out
│   │   └── PutawayPolicy.php              ← Política de ubicación
│   └── ConciliadorInventarioFisico.php     ← Reconciliación de conteo
├── Jobs/Inventario/
│   └── VerificarCaducidadesJob.php         ← Job programado
├── Events/Inventario/                      ← Eventos (lote, merma, traslado)
└── Filament/Resources/Inventario/
    ├── Producto/                           ← CRUD productos + variantes
    ├── StockResource/                      ← Visualización de stock
    ├── MovimientoStockResource/            ← Historial de movimientos
    ├── LoteResource/                       ← Gestión de lotes
    └── PackResource/                       ← Kits de productos
```
