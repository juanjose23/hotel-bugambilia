# Correcciones implementadas: cocina, bebidas y buffet

Fecha: 28 de octubre de 2026. Estado: implementación local validada; sin despliegue ni modificación de la base real.

## Resultado

Se corrigieron los flujos de consumo, anulación, transformación, lotes, costos, mermas y abastecimiento. Se incorporó confirmación de producción con resultado físico, protección de registros históricos, configuración de bodegas por espacio y buffet mixto por persona o paquete de evento.

La guía funcional está en [operación de cocina y buffet](../restaurante/operacion-cocina-buffet.md). Los hallazgos originales se conservan en [revisión del submódulo](revision-submodulo-cocina.md).

## Correspondencia con los 13 hallazgos

| Hallazgo | Cambio implementado | Evidencia principal |
| --- | --- | --- |
| COC-01: doble consumo | El envío es dueño del consumo; el listener solo documenta lo consumido. Bloqueo de pedido y comprobación de movimientos por partida. | Regresión de envío/reenvío y dos envíos simultáneos |
| COC-02: cargo tras anulación | Se desactivan los detalles de cuenta originados por la partida y se recalcula el saldo. Se corrigió el fallback de subtotal histórico. | Regresión de cuenta en cero conservando consumo físico |
| COC-03: productos repartidos | Disponibilidad y consumo comparten alcance; se recorren lotes, se registra cada movimiento y se rechaza insuficiencia. | Venta de 5 unidades repartidas en 2 y 3, sin existencias residuales |
| COC-04: transformación vacía | Se valida toda la lista de resultados antes del descuento; una fila inválida rechaza la operación. | Regresión sin pérdida de origen |
| COC-05: vencimientos | Se excluyen lotes no disponibles; se comprueba nuevamente su estado bloqueado. La salida no amplía vigencia. | Regresión de origen vencido y herencia de fecha |
| COC-06: lotes mezclados | Índice por lote, nueva fila para cada resultado y actualización del saldo del lote consumido; traslados conservan su identidad. | Regresión de dos lotes separados y cantidades conciliadas |
| COC-07: costos de formulario | El servidor toma el costo de lotes utilizados. Distribución y redondeo conservan el total sin importes negativos. | Regresión de costo cero manipulado y reparto de centavos |
| COC-08: cantidades y unidades | Conversión declarada de masa, volumen y empaques; balance físico y registro de diferencia de rendimiento. | 1 a 100 en misma unidad rechazado, botella de 750 ml y caja de 24 unidades |
| COC-09: merma en otro inventario | MermaCocina utiliza stocks de cocina y ajusta lote y movimiento con motivo. | Regresión de existencia física descontada |
| COC-10: producción inconsistente | Borrador de costeo separado de confirmación física. La confirmación crea lote, consume receta por rendimiento y queda protegida. | Producción confirmada, repetición rechazada y rollback por ingrediente faltante |
| COC-11: concurrencia | Bloqueo de pedido/partida, existencias, lote y solicitud; despachos registrados no se repiten. | Seis escenarios simultáneos y regresión de despacho repetido |
| COC-12: bebidas parciales | Apertura por transformación, medidas y empaques, área BAR y bodegas por espacio. | Botella→ml, caja→latas, consumo desde bar y desde espacio configurado |
| COC-13: buffet sin circuito | Alta por persona/evento/mixto; relación opcional con reserva; envíos, reposiciones, cierre medido, sobrantes separados, descarte y cargo único. | Pruebas de conciliación, moneda, repetición, acceso autorizado/denegado y concurrencia |

## Validación final

- **118 pruebas aprobadas, 453 comprobaciones.** Alcance: Feature/Restaurante, Unit/BusinessLogic/Restaurante, Feature/Cuentas, RegistrarPagoCuentaTest y CorreccionesCriticasTest. No se ejecutó toda la suite del repositorio.
- PHPStan nivel 9 en los archivos de esta corrección: sin errores. Pint y git diff --check: aprobados.
- Seis escenarios con dos procesos PHP sincronizados sobre PostgreSQL 18: pedidos distintos compitiendo por ingredientes; envío del mismo pedido; transformación; confirmación de producción; abastecimiento de buffet; cargo de buffet. En cada escenario se completó una operación y se rechazó la incompatible, conservando saldos y cargos.
- Las cuatro migraciones nuevas se aplicaron, revirtieron y reaplicaron correctamente en una segunda base vacía.
- Se probaron las cuatro guardas de reversión con datos sintéticos: conservar lotes separados, producción confirmada, buffet registrado y configuración de bodegas.
- La página de buffet se renderizó con Livewire para usuario autorizado y denegó acceso al no autorizado. No se realizó una inspección visual completa en navegador, móvil ni una prueba de carga.

Entornos exclusivos de prueba: 127.0.0.1:55448, bases hotel_auditoria_sintetica y hotel_cocina_migraciones. La instancia se detiene al finalizar. Ejecutores, resultados y parche acotado a esta corrección quedan en .local/correcciones-cocina, excluido de Git.

## Migraciones preparadas

1. 2026_10_28_000001_preserve_kitchen_stock_lots: unicidad por variante/producto, lote y ubicación.
2. 2026_10_28_000002_add_kitchen_production_confirmation: identificación de borradores, históricos, consumos de pedido y resultado de producción.
3. 2026_10_28_000003_create_buffet_operations: cabeceras y conciliaciones de buffet.
4. 2026_10_28_000004_configure_kitchen_bar_locations: bodegas de cocina y bar por espacio.

Las reversiones rechazan pérdida de información operativa. No se ejecutaron sobre la base real ni se cambiaron sus datos o configuración de conexión. No se hizo commit ni publicación.

## Condiciones de uso y activación

- Revisar respaldo, duplicados por lote y saldos reales antes de aplicar migraciones en el entorno de destino. Los datos inconsistentes anteriores no se reparan automáticamente.
- Configurar variantes, unidades y empaques, recetas que consuman preparaciones elaboradas, bodegas por espacio y permisos de la página nueva.
- El envío a preparación sigue siendo el momento de consumo del pedido. La anulación conserva el consumo físico y solo ajusta automáticamente cuentas abiertas sin pagos; una cuenta pagada o cerrada exige el ajuste financiero autorizado.
- El buffet admite precio por persona, por paquete y mixto. Las personas adicionales no deben duplicar las incluidas en el paquete. El cargo se agrega una sola vez a una cuenta de la misma moneda; el pago lo sigue gestionando caja.
- Los sobrantes del buffet cerrado permanecen separados. Se permite su descarte con motivo; no se devuelve automáticamente comida al inventario disponible.
- El rendimiento de producción requiere medidas físicamente compatibles. No se inventaron densidades, rendimientos culinarios ni períodos de conservación para salvar una configuración incompleta.
- El repositorio tenía numerosos cambios anteriores. El parche local se generó comparando con las copias previas de los archivos intervenidos; evita incorporar automáticamente modificaciones ajenas.
