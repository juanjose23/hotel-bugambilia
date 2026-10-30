# Revisión del submódulo de cocina, materias primas y bebidas

Fecha de corte: 28 de octubre de 2026. Revisión de código y pruebas; sin cambios de lógica de aplicación.

## Resultado

El submódulo contiene funciones reales de abastecimiento, transformación, recetas, porcionado, costos, pedidos, sustitución y merma. Sin embargo, las conexiones actuales no permiten declarar cerrado el recorrido materia prima → producción → servicio → consumo → costo → cuenta. Se identificaron fallos reproducibles de cantidades, lotes y cargos, además de brechas de diseño para bebidas y buffet.

## Alcance y método

Se revisaron las pantallas MateriaPrimaCocina, ProcesoCocina y acciones de cocina; casos de uso de transformación, producción, abastecimiento, consumo, anulación y reenvío; reglas de cantidades y costos; consultas y modelos de ambos inventarios; configuración de áreas y tipos de cocina; y pruebas del dominio. El abastecimiento se revisó en su conexión con cocina; no se auditó en profundidad todo compras. No se realizó navegación visual en navegador ni observación del trabajo físico del hotel.

Las pruebas se ejecutaron exclusivamente en PostgreSQL 18 local independiente, 127.0.0.1:55448, base hotel_auditoria_sintetica. Los escenarios de reproducción utilizaron una transacción exterior revertida al terminar. No se utilizó la base real. La instancia se detiene al terminar esta revisión.

## Flujo actual observado

| Etapa | Implementación actual | Evaluación |
| --- | --- | --- |
| Abastecimiento | Solicitud, aprobación, resolución interna y despacho a cocina | Existe; revisar fuente de stock, bloqueos, estados e idempotencia |
| Transformación | Descuenta producto origen y crea resultados, lotes y merma | Existe; validación, conservación de costos y trazabilidad deficientes |
| Receta | ProductoKit, ingredientes por variante y rendimiento_porciones | Existe; cálculo no unificado entre pedido, producción y costo |
| Producción manual | Formulario de procesos y costeo editable | Registra documento; no equivale a materializar producción en inventario |
| Envío a cocina | Consume ingredientes o productos, crea cargos y emite evento | Consume otra vez a través del listener para platos con receta |
| Producto envasado | Disponibilidad agregada y descuento directo | El descuento no recorre todas las existencias y carece de movimientos en esa ruta |
| Anulación | Cambia estado y recalcula pedido y cuenta | El cargo original permanece en el escenario reproducido |
| Merma diaria | Acción de cocina que consulta inv_stock y opera lotes | No alcanza las existencias de stocks utilizadas por cocina en el escenario probado |
| Bar | Área BAR, productos directos y recetas | No se acreditó circuito completo de medidas, apertura y ubicación propia |
| Buffet | Tipo configurable en espacios | No se encontró ciclo operativo específico de producción, reposición y cierre |

## Hallazgos

### COC-01 · Prioridad alta · Descuento doble al enviar un plato a cocina

**Evidencia:** Reproducido.

Una receta de 1 unidad y un pedido de 1 plato dejaron el stock de 10 en 8. EnviarPedidoACocina consume ingredientes y después emite PedidoEnviadoACocina. El listener activo CrearProcesosCocina llama a RegistrarProcesoCocina, que vuelve a descontarlos. La inscripción del listener se comprobó con event:list.

**Fuentes:** [app/Interactors/Restaurante/Pedidos/EnviarPedidoACocina.php:76](../../app/Interactors/Restaurante/Pedidos/EnviarPedidoACocina.php#L76), [app/Listeners/Restaurante/CrearProcesosCocina.php:39](../../app/Listeners/Restaurante/CrearProcesosCocina.php#L39), [app/Interactors/Restaurante/Cocina/RegistrarProcesoCocina.php:109](../../app/Interactors/Restaurante/Cocina/RegistrarProcesoCocina.php#L109).

**Criterio de corrección:** Elegir un único dueño del consumo. Registrar producción no debe volver a consumir lo ya descontado por el pedido. Probar pedido, reenvío, sustitución y rendimiento mayor que uno.

### COC-02 · Prioridad alta · La anulación deja el cargo activo en la cuenta

**Evidencia:** Reproducido.

Después de enviar el plato, la cuenta tenía saldo 115. Al anular la partida, el subtotal del pedido pasó a 0, pero la cuenta continuó en 115. Recalcular la cuenta no elimina por sí solo el detalle creado con origen PedidoItem. El flujo tampoco registra aquí la disposición física de los ingredientes utilizados.

**Fuentes:** [app/Interactors/Restaurante/Cocina/AnularItemPedido.php:29](../../app/Interactors/Restaurante/Cocina/AnularItemPedido.php#L29), [app/Interactors/Restaurante/Pedidos/RecalcularTotalesPedido.php:43](../../app/Interactors/Restaurante/Pedidos/RecalcularTotalesPedido.php#L43).

**Criterio de corrección:** Coordinar anulación de partida y ajuste del cargo. Separar la decisión comercial del destino físico: recuperación, merma, cortesía o consumo conservado. No devolver automáticamente alimentos al inventario.

### COC-03 · Prioridad alta · La venta de productos y bebidas envasadas puede descontar menos de lo vendido

**Evidencia:** Reproducido.

Con 5 unidades distribuidas en filas de 2 y 3, un pedido de 5 pasó el envío, pero quedaron 3 en stock y se registraron 0 movimientos del producto. La disponibilidad suma filas; el descuento toma first(), reduce solo esa fila y recorta a cero con max(). Sin mesa, la consulta es global; el fallback de producto simple también elimina el alcance del punto de venta.

**Fuentes:** [app/Interactors/Restaurante/Pedidos/DescontarStockProductoPedido.php:57](../../app/Interactors/Restaurante/Pedidos/DescontarStockProductoPedido.php#L57), [app/Interactors/Restaurante/Pedidos/DescontarStockProductoPedido.php:68](../../app/Interactors/Restaurante/Pedidos/DescontarStockProductoPedido.php#L68), [app/Repository/Queries/Restaurante/Stock/ObtenerStockDisponibleRestauranteQuery.php:60](../../app/Repository/Queries/Restaurante/Stock/ObtenerStockDisponibleRestauranteQuery.php#L60).

**Criterio de corrección:** Asignar explícitamente el punto de venta; consumir todas las filas necesarias bajo bloqueo; rechazar insuficiencia real; registrar movimientos por lote y origen del pedido.

### COC-04 · Prioridad alta · Transformación que consume materia prima sin producir resultados válidos

**Evidencia:** Reproducido.

Una lista no vacía con producto_destino_id=0 fue aceptada: el origen bajó de 10 a 9 y la transformación quedó con 0 resultados. El origen se descuenta antes de recorrer los resultados; los inválidos se omiten con continue.

**Fuentes:** [app/Interactors/Restaurante/Cocina/TransformarMateriaPrimaCocina.php:58](../../app/Interactors/Restaurante/Cocina/TransformarMateriaPrimaCocina.php#L58), [app/Interactors/Restaurante/Cocina/TransformarMateriaPrimaCocina.php:74](../../app/Interactors/Restaurante/Cocina/TransformarMateriaPrimaCocina.php#L74).

**Criterio de corrección:** Validar íntegramente todos los resultados antes de descontar. Rechazar datos inválidos y exigir una salida útil o una pérdida explícita autorizada, nunca una transformación vacía.

### COC-05 · Prioridad alta · La transformación acepta un lote vencido y genera una salida disponible sin vencimiento

**Evidencia:** Reproducido.

Se vinculó el stock a un lote en estado Vencido y con fecha anterior al día de prueba. TransformarMateriaPrimaCocina aceptó el origen y creó un lote disponible cuyo vencimiento quedó nulo. La búsqueda del stock no filtra estado ni caducidad.

**Fuentes:** [app/Interactors/Restaurante/Cocina/TransformarMateriaPrimaCocina.php:109](../../app/Interactors/Restaurante/Cocina/TransformarMateriaPrimaCocina.php#L109), [app/Repository/Persistencia/Restaurante/RestauranteRepositorio.php:212](../../app/Repository/Persistencia/Restaurante/RestauranteRepositorio.php#L212), [app/Interactors/Restaurante/Cocina/TransformarMateriaPrimaCocina.php:147](../../app/Interactors/Restaurante/Cocina/TransformarMateriaPrimaCocina.php#L147).

**Criterio de corrección:** Bloquear entradas vencidas, rechazadas o en cuarentena en el caso de uso. Definir la vigencia de preparaciones mediante el procedimiento operativo autorizado; no convertir ausencia de fecha en una validación.

### COC-06 · Prioridad alta · Se mezclan cantidades de lotes y se pierde correspondencia entre saldo y lote

**Evidencia:** Reproducido.

Dos transformaciones generaron salidas de 2 y 3: una fila de stocks terminó con 5, vinculada únicamente al lote nuevo de 3. El método sobrescribe lote_id y suma la cantidad previa. En otro escenario, descontar 1 dejó stock origen 9 pero cantidad_disponible del lote origen 10. Esto compromete caducidades, costos y trazabilidad.

**Fuentes:** [app/Interactors/Restaurante/Cocina/TransformarMateriaPrimaCocina.php:162](../../app/Interactors/Restaurante/Cocina/TransformarMateriaPrimaCocina.php#L162), [app/Interactors/Restaurante/Cocina/TransformarMateriaPrimaCocina.php:165](../../app/Interactors/Restaurante/Cocina/TransformarMateriaPrimaCocina.php#L165).

**Criterio de corrección:** Definir saldos por lote y ubicación, sin sustituir la identidad del lote de existencias anteriores. Conciliar stock, lotes y movimientos en cada transformación y consumo.

### COC-07 · Prioridad alta · El costo de transformación depende del valor recibido del formulario

**Evidencia:** Reproducido.

Un origen con costo unitario 20 pudo transformarse asignando costo 0 a la salida; la cabecera terminó con costo_total=0. El backend suma costo_asignado recibido sin conciliarlo con el costo retirado. La pantalla calcula proporciones, pero el campo es editable y no sustituye una validación del servidor.

**Fuentes:** [app/Interactors/Restaurante/Cocina/TransformarMateriaPrimaCocina.php:70](../../app/Interactors/Restaurante/Cocina/TransformarMateriaPrimaCocina.php#L70), [app/Filament/Pages/Restaurante/MateriaPrimaCocina.php:214](../../app/Filament/Pages/Restaurante/MateriaPrimaCocina.php#L214), [app/Filament/Pages/Restaurante/MateriaPrimaCocina.php:508](../../app/Filament/Pages/Restaurante/MateriaPrimaCocina.php#L508).

**Criterio de corrección:** Calcular el costo en servidor desde los lotes realmente utilizados; distribuirlo con una política explícita para productos útiles, subproductos y pérdidas; conciliar redondeos.

### COC-08 · Prioridad media · No hay balance de cantidades ni conversiones que justifiquen el rendimiento

**Evidencia:** Reproducido / diseño incompleto.

Con origen y destino en la misma unidad, una entrada de 1 generó una salida de 100 sin rechazo. No existe en este caso de uso una conversión o receta que justifique ese resultado. No se debe exigir igualdad simple para todos los procesos: cocinar, agregar líquidos o mezclar puede cambiar el peso; se necesita un balance con entradas y salidas justificadas.

**Fuentes:** [app/Interactors/Restaurante/Cocina/TransformarMateriaPrimaCocina.php:31](../../app/Interactors/Restaurante/Cocina/TransformarMateriaPrimaCocina.php#L31), [app/Interactors/Restaurante/Cocina/TransformarMateriaPrimaCocina.php:69](../../app/Interactors/Restaurante/Cocina/TransformarMateriaPrimaCocina.php#L69).

**Criterio de corrección:** Definir unidad base, conversiones y tolerancias por transformación. Registrar rendimiento esperado y real, entradas adicionales, subproductos y merma.

### COC-09 · Prioridad alta · La merma diaria no trabaja sobre las mismas existencias que cocina

**Evidencia:** Reproducido.

Con 10 unidades visibles en stocks de cocina y sin fila correspondiente en inv_stock, RegistrarMermaGlobalDiaria indicó que no había stock. La acción del tablero de cocina invoca ese caso de uso, cuya consulta lee Inventario/Stock (inv_stock), mientras cocina usa Shared/Stock (stocks). ServicioMermas actualiza el lote y crea movimiento; no se observa allí el ajuste de la fila física de cocina.

**Fuentes:** [app/Filament/Shared/Actions/Restaurante/MermaGlobalDiariaAction.php:98](../../app/Filament/Shared/Actions/Restaurante/MermaGlobalDiariaAction.php#L98), [app/Interactors/Inventario/Lotes/RegistrarMermaGlobalDiaria.php:54](../../app/Interactors/Inventario/Lotes/RegistrarMermaGlobalDiaria.php#L54), [app/Repository/Models/Inventario/Stock.php:19](../../app/Repository/Models/Inventario/Stock.php#L19), [app/Repository/Models/Shared/Stock.php:40](../../app/Repository/Models/Shared/Stock.php#L40).

**Criterio de corrección:** Definir la fuente de existencia física para cocina y bar y registrar la merma en esa misma fuente, conciliando lote y movimiento sin descontar dos veces.

### COC-10 · Prioridad media · Producción manual y producción automática tienen efectos distintos

**Evidencia:** Inspección de código.

La pantalla de crear/editar ProcesoCocina llama a ProcesarProcesoCocina::guardar: persiste cabecera e ítems y costos, sin materializar entradas y salidas de inventario. El listener usa RegistrarProcesoCocina, que sí descuenta ingredientes y no genera existencias de producto cocinado. Además, el consumo por pedido divide por rendimiento_porciones; RegistrarProcesoCocina y CalcularCostoPlato no aplican esa división.

**Fuentes:** [app/Filament/Resources/Restaurante/ProcesoCocinaResource/Pages/CreateProcesoCocina.php:21](../../app/Filament/Resources/Restaurante/ProcesoCocinaResource/Pages/CreateProcesoCocina.php#L21), [app/Interactors/Restaurante/Cocina/ProcesarProcesoCocina.php:29](../../app/Interactors/Restaurante/Cocina/ProcesarProcesoCocina.php#L29), [app/BusinessLogic/Restaurante/Cocina/CalcularCantidadIngredienteReceta.php:14](../../app/BusinessLogic/Restaurante/Cocina/CalcularCantidadIngredienteReceta.php#L14), [app/Interactors/Restaurante/Cocina/RegistrarProcesoCocina.php:51](../../app/Interactors/Restaurante/Cocina/RegistrarProcesoCocina.php#L51), [app/BusinessLogic/Restaurante/Platos/CalcularCostoPlato.php:37](../../app/BusinessLogic/Restaurante/Platos/CalcularCostoPlato.php#L37).

**Criterio de corrección:** Separar borrador o costeo de una producción confirmada. Una confirmación debe tener efectos únicos, lotes elaborados y reversión controlada. Unificar cantidades y costos por rendimiento.

### COC-11 · Prioridad alta · Faltan bloqueos coherentes en transformación, despacho y sustituciones

**Evidencia:** Riesgo por inspección; no reproducido en paralelo en esta revisión.

ObtenerStockPorVariante usa first() sin lockForUpdate y es reutilizado para transformación, despacho y variante sustituta. La transacción por sí sola no protege el patrón leer-calcular-guardar. El envío valida el estado antes de su transacción y no bloquea explícitamente pedido e ítems en esa ruta. Las pruebas anteriores de inv_stock no acreditan la concurrencia de stocks en cocina.

**Fuentes:** [app/Repository/Persistencia/Restaurante/RestauranteRepositorio.php:212](../../app/Repository/Persistencia/Restaurante/RestauranteRepositorio.php#L212), [app/Interactors/Restaurante/Pedidos/EnviarPedidoACocina.php:52](../../app/Interactors/Restaurante/Pedidos/EnviarPedidoACocina.php#L52), [app/Interactors/Restaurante/Cocina/DespacharSolicitudAbastecimientoCocina.php:81](../../app/Interactors/Restaurante/Cocina/DespacharSolicitudAbastecimientoCocina.php#L81).

**Criterio de corrección:** Aplicar bloqueos e idempotencia por operación, validar el estado bloqueado y repetir pruebas con dos procesos de cocina, despacho y sustitución sobre las mismas existencias.

### COC-12 · Prioridad media · Bebidas tienen soporte parcial pero no un circuito completo de botellas y medidas

**Evidencia:** Brecha funcional por inspección.

Hay venta de producto directo, receta y área BAR. La variante tiene un campo volumen, pero los casos de consumo revisados operan sobre cantidad sin una conversión automática explícita botella→ml ni un registro dedicado de apertura. El consumo de recetas resuelve una ubicación de cocina por nombre, sin seleccionar almacén por el área BAR del ítem.

**Fuentes:** [app/Enums/Restaurante/AreaCocina.php:17](../../app/Enums/Restaurante/AreaCocina.php#L17), [database/migrations/Catalogos/2026_05_10_231842_productos_variantes_table.php:27](../../database/migrations/Catalogos/2026_05_10_231842_productos_variantes_table.php#L27), [app/Repository/Queries/Restaurante/Pedidos/ObtenerIngredientesPedidoQuery.php:24](../../app/Repository/Queries/Restaurante/Pedidos/ObtenerIngredientesPedidoQuery.php#L24).

**Criterio de corrección:** Distinguir botella envasada, botella abierta e ingrediente líquido; fijar unidad base y ubicación por área. Probar una botella de 750 ml y una receta de 45 ml sin doble descuento. No dar por ausente toda conversión del proyecto: esta conclusión se limita al circuito revisado.

### COC-13 · Prioridad media · Buffet es una opción de configuración, no un flujo operativo completo

**Evidencia:** Brecha funcional por inspección.

TipoCocina incluye Buffet, Mixto y Barra y se ofrece en la configuración del espacio. No se encontró un caso de uso, entidad o tabla específica para apertura de buffet, producción asignada, bandejas, reposiciones, comensales, sobrantes y cierre físico. Las referencias restantes son textos comerciales o datos de ejemplo. No equivale a decir que la aplicación no pueda vender un concepto llamado buffet.

**Fuentes:** [app/Enums/HabitacionesEspacios/TipoCocina.php:17](../../app/Enums/HabitacionesEspacios/TipoCocina.php#L17), [app/Filament/Resources/Habitaciones/EspacioResource/Schemas/EspacioForm.php:276](../../app/Filament/Resources/Habitaciones/EspacioResource/Schemas/EspacioForm.php#L276).

**Criterio de corrección:** Diseñar buffet como servicio por lote: planificación, asignación de elaborados, reposiciones, cierre, sobrantes y pérdidas. Separar entradas vendidas de consumo físico real.

## Resultados de pruebas

- Suite completa seleccionada: tests/Feature/Restaurante y tests/Unit/BusinessLogic/Restaurante: **69 pruebas, 67 aprobadas y 2 fallidas; 247 comprobaciones reportadas**.
- Fallaron los casos de pedido delivery con Estelí en RestauranteCheckoutTest y RestaurantePublicoTest: esperaban 201 y recibieron 422 porque el departamento no estaba registrado en zonas de entrega. Es coherente con la corrección previa que dejó de habilitar configuración alternativa cuando la tabla existe pero no tiene zonas activas. Se debe definir y preparar la configuración de zonas de esos tests; no se atribuye el fallo a cocina ni se propone reactivar tarifas por un fallback silencioso.
- Una selección inicial de 25 pruebas, contenida en la suite anterior, pasó. No se suma como otras 25 pruebas diferentes.
- Se ejecutaron **7 escenarios adicionales de reproducción**, con los resultados detallados abajo. La suite existente aprobada no cubría estos resultados incorrectos.

### Reproducción: transformacion_sin_resultados_validos

```json
{
  "origen_antes": 10,
  "origen_despues": 9,
  "resultados": 0
}
```

### Reproducción: rendimiento_1_a_100_misma_unidad

```json
{
  "entrada": 1,
  "salida": 100
}
```

### Reproducción: mezcla_lotes_transformaciones

```json
{
  "stock": 5,
  "cantidad_lote_actual": 3,
  "lotes": 2
}
```

### Reproducción: pedido_consumo_y_anulacion

```json
{
  "stock_inicial": 10,
  "consumo_esperado": 1,
  "stock_tras_envio": 8,
  "pedido_subtotal_tras_anular": 0,
  "saldo_cuenta_antes": 115,
  "saldo_cuenta_despues": 115
}
```

### Reproducción: producto_simple_existencia_repartida

```json
{
  "disponible_inicial": 5,
  "vendido": 5,
  "stock_restante": 3,
  "movimientos": 0
}
```

### Reproducción: transformacion_lote_vencido_y_costo_cero

```json
{
  "aceptado": true,
  "estado_origen": "Vencido",
  "stock_origen": 9,
  "saldo_lote_origen": 10,
  "costo_transformacion": 0,
  "vencimiento_salida": null
}
```

### Reproducción: merma_diaria_no_encuentra_stock_cocina

```json
{
  "stock_en_cocina": 10,
  "error": "No hay stock disponible del producto ID 52 en la ubicación seleccionada."
}
```

Los ejecutores y resultados originales se conservan localmente en .local/auditoria-criticos/revision-cocina.php y revision-cocina-resultados.jsonl, excluidos de Git. Sus fixtures derivan del caso existente de transformación y usan la aplicación real con datos sintéticos. No son una suite permanente de regresión y deberán convertirse en pruebas automatizadas al implementar las correcciones.

## Orden de trabajo recomendado

1. Corregir los efectos económicos y de existencias: descuento doble, cargos tras anulación y venta de productos con existencias distribuidas (COC-01 a COC-03).
2. Asegurar transformaciones: validar todas las salidas, impedir origen no disponible, conservar lotes y costos y justificar rendimientos (COC-04 a COC-08).
3. Conciliar almacén, cocina y merma y proteger concurrencia (COC-09 y COC-11). Definir si ambos modelos de stock se conservan y su relación exacta antes de migrar datos.
4. Unificar producción manual, automática y consumo por rendimiento (COC-10). Distinguir documento de costeo, producción confirmada y consumo por pedido.
5. Definir y construir conversiones y flujo de bebidas y buffet (COC-12 y COC-13) sobre el inventario corregido.

Para el ejemplo de un kilo de carne: identificar corte/unidad/lote → retirar cantidad real → registrar resultados útiles, subproductos y pérdidas → crear lotes elaborados con costo y vigencia definidos → asignar a platos o buffet → registrar servicio, reposiciones y destino de sobrantes. Cada transición debe tener un único efecto sobre inventario y un documento de origen. Una anulación comercial y una devolución física son decisiones diferentes.

## Límites y conclusión de la revisión

No se corrigieron estos hallazgos durante la revisión. No se desplegó ni se alteró información real. La concurrencia específica de las rutas de cocina y el funcionamiento visual de cada pantalla quedan pendientes de prueba. La ausencia de un flujo específico de buffet o apertura de botellas se basa en los archivos y referencias inspeccionados, no en una certificación de ausencia de toda funcionalidad equivalente.

Antes de ampliar el submódulo con buffet, conviene corregir las inconsistencias reproducidas y convertirlas en regresiones que fallen antes del cambio y pasen después. El manual general debe interpretarse junto con este informe: las validaciones anteriores sobre inv_stock no certifican por extensión las rutas de stocks y eventos de cocina.

## Actualización de implementación

Las correcciones posteriores y sus pruebas se documentan en [correcciones de cocina implementadas](correcciones-cocina-implementadas.md). Los resultados anteriores describen el estado previo al cambio.
