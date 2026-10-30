# Normalización de los módulos críticos del hotel

Fecha: 29 de octubre de 2026, hora de Nicaragua.

## Alcance acordado

Prioridad: **facturación, cuentas, reservas, habitaciones y espacios**. El orden de implementación parte de la integridad de cuentas y continúa por recursos, reservas y documentos financieros, porque estos últimos dependen del resto.

**Las tablas y relaciones polimórficas se conservan.** No se sustituyen, dividen, renombran ni eliminan los vínculos de precios, imágenes, servicios, políticas, existencias asignadas, auditoría y orígenes de cargos o líneas. Los campos `*_type` y sus identificadores mantienen su contrato actual. La revisión de una columna normal de una tabla con relaciones polimórficas no autoriza alterar esas relaciones.

Se conserva también la distinción **producto → variante/presentación → cantidad y unidad de medida**. Aceite de 500 ml y aceite de 5 litros pueden ser presentaciones del mismo producto; no se consideran duplicados por compartir producto. Cocina e inventario no forman parte de esta primera intervención.

## Estado concreto de la primera entrega

Se preparó en el proyecto una migración posterior y reversible:

`database/migrations/Cuentas/2026_10_30_000001_add_missing_financial_foreign_keys.php`.

| Relación protegida | Regla incorporada | Índice |
|---|---|---|
| `cuentas.estancia_id → estancias.id` | FK opcional; impedir borrado físico de estancia vinculada | Nuevo índice en `estancia_id` |
| `pedidos.cuenta_id → cuentas.id` | FK opcional; impedir borrado físico de cuenta vinculada | Reutiliza el índice existente |
| `pagos_cuenta.venta_id → ventas.id` | FK opcional; impedir borrado físico de venta vinculada | Nuevo índice en `venta_id` |

No se reescribieron migraciones antiguas ni se rellenaron relaciones vacías. NULL continúa significando ausencia de vínculo. No se utiliza CASCADE ni se borran relaciones para conseguir que una migración pase. Si existen huérfanos, la migración debe fallar para permitir su conciliación.

**Estado: activada en la base principal local y en la demostración; ampliada según [la entrega aplicada](normalizacion-aplicada.md).** Añadir FK corrige integridad referencial, pero no acredita por sí solo ninguna forma normal.

## Plan por módulo

| Orden | Módulo | Trabajo necesario | Condición de aceptación |
|---|---|---|---|
| 1 | Cuentas | Proteger las tres relaciones anteriores. Definir el significado de `reserva_id` frente a la reserva de `estancia_id`. Documentar apertura, cargos, pagos, saldos y cierre. | Sin referencias inexistentes; reservas y estancias coherentes; pagos y documentos históricos sin cambios. |
| 2 | Habitaciones | Separar las vistas de habitación en una relación con catálogo. Revisar los campos compartidos con el recurso reservable y distinguir características físicas de reglas de ocupación. | Cada etiqueta original queda mapeada o identificada como pendiente; filtros, edición y detalle funcionan; no cambia el precio pactado de reservas anteriores. |
| 3 | Espacios | Revisar jerarquía padre/hijo, capacidad física, habilitación para reservas y reglas de disponibilidad. Definir la responsabilidad de cada atributo respecto al recurso reservable. | Restaurante, mesas y salones conservan su jerarquía; no se duplica una asignación de recurso; no se alteran precios ni servicios polimórficos. |
| 4 | Reservas y estancias | Dar a cada recurso reservado sus fechas, estado y ocupantes mediante los detalles. Reconciliar acompañantes históricos y separar datos del solicitante, huésped y pagador. Distinguir horario programado de entrada/salida real. | Una reserva con varias habitaciones o espacios mantiene cada ocupación y huésped correcto; disponibilidad, cambios, cancelación y check-in/out siguen funcionando. |
| 5 | Facturación | Clasificar datos maestros, hechos fiscales históricos y resultados calculados. Revisar las dependencias entre cuenta, venta, factura y líneas; definir fuente de cada importe y versión fiscal antes de retirar duplicidades. | Importes, numeración, receptor, tasa, impuesto y autorización de documentos emitidos se reproducen exactamente; emisión, anulación y conciliación conservan trazabilidad. |

## Dependencias que debemos resolver antes de retirar columnas

### Cuentas

`cuentas.id` identifica la cuenta y sus operaciones. `estancias.id` identifica una estancia y su reserva. Si `cuentas.reserva_id` significa siempre la misma reserva heredada de la estancia, hay una dependencia que debe resolverse en el modelo objetivo. Sin embargo, también existen cuentas sin estancia y cuentas de reserva: no se puede borrar esa columna por observar coincidencias.

Debemos clasificar los casos de reserva anticipada, estancia, restaurante y cuenta corporativa; establecer si una cuenta puede agrupar varias reservas; y documentar cómo se traslada o comparte el cargo. Los vínculos polimórficos de los cargos permanecen. Saldos y totales deben tener una fórmula y una fuente de movimientos documentadas; si se mantienen como proyecciones almacenadas, se declaran como tales y se verifican.

### Habitaciones y espacios

El catálogo físico responde qué habitación o espacio existe. El recurso reservable responde cómo se ocupa y se bloquea en el calendario. Actualmente ambos almacenan ciertos atributos similares, como nombre o capacidad; antes de unificarlos hay que definir si tienen el mismo significado. No se elimina un dato si representa una regla operativa distinta.

La colección `detalles_habitacion.vistas` es candidata a una tabla hija con `id` primero, FK a habitación, FK al catálogo de vista y unicidad del par. Los valores observados son etiquetas textuales: se exige un mapeo explícito y compatible con los catálogos, sin convertir cadenas en números. Primero se implementan escritura y lectura compatibles; luego se compara el contenido y solo al final se retira la representación antigua.

La jerarquía de espacios se conserva: un restaurante puede contener mesas y un espacio puede tener su propia disponibilidad. El indicador de habilitación para reservar no equivale al identificador del recurso reservable.

### Reservas

El objetivo es que el detalle sea la fuente de la asignación de cada recurso y de su intervalo. La cabecera agrupa el contrato y contacto de la reserva. No debe elegirse automáticamente la primera habitación como destino de todos los acompañantes ni confundirse comprador con huésped.

El diagnóstico previo encontró dos JSON de acompañantes informados, uno con lista no vacía y sin huéspedes relacionados en sus detalles. Ese caso necesita correspondencia explícita antes de transformar o retirar datos. Debe probarse tanto reserva simple como paquete con varios recursos, cambio de habitación y salidas parciales.

Los datos de contacto capturados al reservar pueden ser una fotografía contractual diferente del cliente actual. Se clasifican antes de normalizarlos. Fecha programada y fecha real tampoco son duplicados si representan hechos diferentes.

### Facturación

La venta conserva la operación cerrada; la factura documenta el acto fiscal. El receptor al emitir puede diferir del cliente actual o del huésped. Precio, impuesto, tipo de cambio, autorización y conceptos emitidos no se reconstruyen desde catálogos actuales.

Para una exigencia de 3FN estricta se debe modelar qué hechos pertenecen a la emisión y qué datos se referencian mediante versiones inmutables. Los totales o saldos repetidos deben identificarse como proyecciones o eliminarse solo después de adaptar las consultas y validar la reproducción del documento. No se declarará 3FN por conservar una redundancia útil.

Una FK individual tampoco garantiza que un `cuenta_id` coincida con la cuenta de una venta referenciada: esas reglas cruzadas se deben definir y probar aparte. No se impone una relación uno a uno entre cuenta, venta y factura solamente porque la muestra actual no tenga otros casos.

## Cómo se realiza cada cambio

1. Registrar reglas, claves candidatas y dependencias funcionales del módulo; distinguir hechos actuales de históricos.
2. Actualizar respaldo y restaurar una copia aislada. Guardar conteos y huellas antes de intervenir.
3. Crear migraciones nuevas, con claves, índices y política de borrado explícitas. Mantener `id` primero en nuevas tablas de negocio.
4. Implementar el mapeo de datos y adaptar las capas de lectura, escritura y pantallas; marcar los casos ambiguos para revisión.
5. Verificar conservación de datos, contratos polimórficos y pruebas de negocio. Ensayar reversión y reaplicación cuando corresponda.
6. Preparar la activación en el entorno principal separadamente, con respaldo vigente y comprobación de operaciones posteriores.

No se ejecutan pruebas destructivas ni `migrate:fresh` contra la base principal. La copia restaurada contiene información real y permanece en el entorno local; no se publica ni se exportan valores personales en los informes.

## Verificación de esta entrega

El ensayo verifica contenido de 37 tablas, incluidas las tablas de los cinco módulos y las que tienen columnas polimórficas `*_type` observadas en el inventario. Las huellas coinciden antes y después de aplicar, revertir y reaplicar la migración. Esto verifica conservación de esas tablas, no certifica toda la base en 3FN ni todas las relaciones del negocio.

Pasaron **46 pruebas con 162 aserciones**, incluidas 10 pruebas nuevas de las relaciones protegidas. Se comprobaron referencias inexistentes, relaciones opcionales, borrado físico restringido y conservación del vínculo al dar de baja lógicamente una cuenta.

La copia del ensayo se identifica en `verificacion-primera-etapa.json`, junto con sus restricciones resultantes. Los respaldos completos, configuración y datos permanecen en `.local/normalizacion-criticos/` y no se incorporan a la documentación pública.

**Actualización:** las vistas, acompañantes, conservación de detalles y controles de contexto financiero ya están implementados y activados. Véase [el resultado y las limitaciones de 3FN](normalizacion-aplicada.md).
