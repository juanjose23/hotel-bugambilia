# Optimización del módulo de restaurante

## Estado de la entrega

Cambios aplicados en el proyecto principal local `D:\Developer\laravel\hotel-bugambilia`. Se actualizaron consultas, pantallas, controles de área, alertas y reportes. El proyecto principal y la demostración utilizan este código. Los cambios conservan el esquema y los registros existentes; las comprobaciones de escritura se ejecutaron en una base exclusiva de pruebas.

## Hallazgos corregidos

| Punto | Problema observado | Cambio aplicado |
|---|---|---|
| R-01 | Cocina consultaba la cola al hidratar, al ejecutar la actualización y al construir la vista | Una selección de comandas por actualización; el resultado se reutiliza durante la misma solicitud |
| R-02 | El filtro de área se aplicaba en memoria después de cargar pedidos y partidas de todas las áreas | Filtro en SQL tanto para seleccionar pedidos como para cargar sus partidas; orden estable por fecha e identificador |
| R-03 | Una partida podía aparecer en dos áreas por combinar su asignación con la del platillo | La asignación de la partida prevalece; el área del platillo se hereda cuando la partida no tiene una propia, igual que en inventario |
| R-04 | Pedidos cancelados con partidas pendientes seguían visibles; pedidos bloqueados podían desaparecer | Cancelados excluidos de las colas; bloqueados por stock visibles como pendientes. Los prepagados continúan apareciendo cuando requieren preparación |
| R-05 | El aviso de pedidos listos se repetía en cada consulta; cocina omitía la primera llegada después de quedar vacía | Avisos por identificadores nuevos; sin repetición mientras permanece el mismo pedido. Cambiar de área no anuncia el historial como novedad |
| R-06 | La relación de pedidos activos de una mesa omitía LISTO | Los listos forman parte de la ocupación operativa y de las comandas activas |
| R-07 | Nombres de productos/variantes y preórdenes provocaban lecturas adicionales por registro | Relaciones cargadas en bloque en despacho, detalle de comandas y reservas vigentes de mesas |
| R-08 | Se cargaban todas las variantes para cada selector de sustitución | Hasta 50 opciones iniciales y búsqueda por código, variante o producto; recuperación de la etiqueta seleccionada |
| R-09 | La consulta de ingredientes tenía una dependencia de inventario sin inicializar | Inyección tipada restaurada. Los cinco avisos estáticos de esa misma causa quedan resueltos |
| R-10 | La consulta de receta calculaba existencias que sus dos consumidores no utilizaban y volvían a consultar | Eliminado el resultado interno sin consumidores y sus lecturas redundantes. La validación y el consumo efectivo siguen usando existencias y bloqueos actuales |
| R-11 | Los reportes trasladaban cada partida a PHP y mezclaban cancelaciones y anulaciones en los importes | Agregación por artículo y presentación en SQL; importes operativos excluyen pedidos cancelados y líneas anuladas |
| R-12 | Productos sin platillo aparecían agrupados como desconocidos y los artículos retirados se omitían | Productos y variantes diferenciados; los artículos con borrado lógico permanecen en el reporte histórico |
| R-13 | El reporte presentaba consumo como facturación y el listado repetía subtotal/total o mostraba el total de toda la cuenta por pedido | Etiqueta Consumo operativo; una columna principal Consumo del pedido. Total de cuenta compartida queda como columna opcional, identificada claramente |
| R-14 | Los botones de área repetían el mismo bloque y el nombre de categoría se imprimía sin escape | Controles generados desde el catálogo de áreas, con ajuste de línea y estado accesible; categoría escapada al mostrarla |

La numeración fiscal, los pagos, los movimientos, las recetas, lotes, unidades y vínculos polimórficos permanecen en sus fuentes. No se alteraron cantidades para conseguir mejoras de rendimiento. Los resultados de reportes se recalculan al consultarlos; los documentos emitidos conservan sus datos.

## Evidencia de rendimiento

| Comprobación | Antes | Después | Alcance |
|---|---:|---:|---|
| Consulta de cocina filtrada por barra | 9 consultas | 1 consulta | Misma copia, sin partidas coincidentes en barra; antes se cargaban relaciones de otras áreas |
| Selección de cola por actualización de cocina | Hasta 3 selecciones | 1 selección | Prueba sobre el ciclo real de Livewire |
| Reporte 2025–2026 | 86.244 partidas elegibles | 19 grupos devueltos | Medición de lectura en el proyecto principal; agrupación por artículo/presentación |
| Opciones iniciales de sustitución | Catálogo completo | Máximo 50 | La búsqueda permite encontrar variantes fuera de la primera lista |

Despacho incorpora ahora productos, variantes y persona del cliente de forma anticipada. Esa selección puede realizar más consultas iniciales que antes, pero evita consultas adicionales por cada partida al mostrar sus datos. La evidencia conserva ambas mediciones; no se afirma que todas las pantallas redujeran su número inicial de consultas.

Los tiempos guardados en la evidencia son mediciones puntuales locales, con cachés y cantidades específicas; no equivalen a una prueba de carga ni a una promesa de latencia para otros volúmenes.

## Verificación funcional y técnica

- **124 pruebas aprobadas**, con 456 aserciones, del restaurante y su integración con unión de mesas de reservas.
- **14 pruebas específicas finales aprobadas**, con 44 aserciones. Incluyen el ajuste posterior del listado de pedidos; varias forman parte de la batería anterior y no se suman como pruebas únicas adicionales.
- PHPStan nivel 9 del módulo completo: cero errores con la configuración del proyecto. Verificación adicional del listado final: cero errores.
- Formato aplicado a los archivos modificados.
- Ocho pantallas del proyecto principal renderizadas en una transacción READ ONLY: mesas, comandas, turnos, reportes, control de cocina, preparación de materias primas, buffets y listado de pedidos.
- Pruebas de consumo único, anulación, preparación, merma, lotes, buffet, reservas, venta de productos y cobros incluidas en la batería del restaurante.

Archivos PHP incluidos en la revisión estática por área:

| Área | Archivos |
|---|---:|
| `app/Repository/Queries/Restaurante` | 37 |
| `app/Repository/Persistencia/Restaurante` | 8 |
| `app/Repository/Models/Restaurante` | 13 |
| `app/Interactors/Restaurante` | 54 |
| `app/BusinessLogic/Restaurante` | 23 |
| `app/Filament/Pages/Restaurante` | 9 |
| `app/Filament/Resources/Restaurante` | 27 |

## Criterios que se mantienen

Gestión de mesas, comandas, turnos, autoservicio y administración de pedidos atienden roles distintos. Comparten criterios de estado y datos; su separación funcional se conserva. Productos y presentaciones se identifican por separado: por ejemplo, un litro y cinco litros no se fusionan en el reporte.

El consumo operativo es distinto de la factura emitida y del cobro conciliado. La clave interna `total_facturado` del resultado del reporte se conserva por compatibilidad, pero la pantalla identifica su contenido como consumo operativo. `pedidos.total` permanece como campo de compatibilidad; las pantallas corregidas no lo usan como sustituto del importe propio del pedido.

## Continuación de la revisión

Esta entrega cubre los problemas confirmados y las dependencias verificadas. No certifica que todo el módulo carezca de deuda técnica. Quedan como trabajo posterior:

1. Dividir las páginas administrativas más grandes en esquemas y acciones compartidas, manteniendo permisos, validaciones y estados.
2. Evaluar con EXPLAIN y una prueba de concurrencia los índices de colas y consultas de inventario antes de crear índices adicionales o introducir cachés persistentes.
3. Planificar por separado la retirada del campo `pedidos.total` y otros contratos antiguos después de revisar consumidores externos y datos históricos.
4. Continuar separando reportes operativos de reportes fiscales y conciliación; sus cifras responden a procesos diferentes.

[Evidencia completa y huellas de los archivos](verificacion.json). Los originales de los archivos intervenidos están conservados en `.local/optimizacion-restaurante/originales/`, excluida del control de versiones.
