# Normalización aplicada en los módulos críticos

Las cuatro migraciones están activadas en **hotel_bugambilias**, base principal local, y en **hotel_cocina_demo**. Se ensayaron primero sobre una copia restaurada del respaldo. La interfaz está compilada.

## Cambios operativos

| Módulo | Resultado implementado |
|---|---|
| Habitaciones | `habitacion_vistas` es la relación canónica con catálogo. Se trasladaron las **188 vistas** conservando sus etiquetas y orden. Administración permite seleccionarlas, guardarlas y consultarlas; clonación, detalle público y datos de demostración utilizan la relación. Editar únicamente las vistas conserva la galería existente; su guardado usa las claves polimórficas correctas y mantiene los identificadores de imágenes que permanecen. |
| Reservas y huéspedes | Recepción y portal leen `reserva_huespedes`. El acompañante histórico sin representación relacional fue trasladado a su única habitación posible. En reservas múltiples se exige destino; se rechazan personas y detalles ajenos. |
| Edición de reservas | Las habitaciones, servicios y espacios adicionales conservan el identificador del detalle. No se permite retirar un recurso con huéspedes o estancia vinculados sin resolverlos antes. |
| Cuentas | FK para cuenta–estancia, pedido–cuenta y pago–venta. La reserva declarada de una cuenta debe coincidir con la de su estancia cuando ambas estén informadas. |
| Facturación | La factura y el pago vinculados a una venta deben corresponder a su cuenta. La persistencia rechaza líneas de otra venta y devuelve errores controlados. No se reescribieron documentos emitidos. |
| Espacios | La administración rechaza un padre inexistente, el propio espacio o una cadena que genere ciclos. Se mantienen jerarquía, catálogo físico y recurso reservable con sus funciones actuales. |
| Portal | Permite seleccionar habitación o espacio por acompañante. Guarda sobre la misma identidad que recepción, mantiene datos personales no editados y diferencia consulta de autorización para modificar. Las consultas cargan huéspedes en bloque. |

## Datos conservados

Se compararon **38 tablas** antes y después de la activación. Las huellas coinciden para sus registros anteriores, normalizando únicamente los nombres de columnas archivadas para comparar la misma información. Los registros anteriores del catálogo y de huéspedes se conservaron; se añadieron las vistas de catálogo necesarias y el acompañante pendiente.

Las relaciones polimórficas mantienen estructura y contenido. Continúan los vínculos de precios, servicios, políticas, imágenes, existencias asignadas, auditoría y orígenes de movimientos. Productos, variantes, presentaciones y unidades no se modificaron.

Precio, receptor, impuesto, tasa de cambio, autorización, importes y consecutivos de documentos anteriores se conservan. No se recalcularon con los catálogos actuales.

La entrada original queda en `detalles_habitacion.vistas_legacy` y `reservas.acompanantes_legacy` como resguardo de migración. **La aplicación no las usa como fuente operativa** y no las expone mediante la serialización de los modelos. Las migraciones de reversión reconstruyen la representación anterior desde los datos relacionados vigentes.

## Verificación

- Batería integrada: **137 pruebas, 592 aserciones, todas aprobadas**.
- Revisión posterior de portal y acceso: **24 pruebas aprobadas**, incluyendo dos casos nuevos de autorización.
- Registro, edición, traslado, capacidad, titular, documentos ajenos, conservación de datos y reversión comprobados en bases de prueba.
- Dos pruebas adicionales del formulario y galería aprobadas: guardar, recuperar y retirar vistas, conservar imágenes locales y remotas no editadas, y actualizar la galería manteniendo identidad y metadatos.
- PHPStan nivel 9 sin errores en los archivos de producción modificados; revisión adicional de las consultas y autorización del portal sin errores.
- TypeScript y compilación de la interfaz completados correctamente.
- Acceso de inicio de sesión del proyecto principal y de la demostración: HTTP 200.
- Pantallas de edición y ficha de habitación, edición de espacio y reserva, ficha de factura y detalle público comprobados después de activar, dentro de una transacción de solo lectura.
- La demostración tiene respaldo propio y conserva las cantidades de sus siete tablas financieras verificadas.

## Esquema actual y alcance de 3FN

El esquema tiene **149 tablas, 1706 columnas, 366 claves foráneas y 573 índices**. La tabla nueva tiene `id` primero y una clave única por habitación y vista. La identidad del huésped se conserva en su registro de ocupación; la reserva se obtiene mediante su detalle.

**Esto no constituye una certificación global de 3FN.** Las cabeceras de compatibilidad, saldos y totales almacenados siguen requiriendo una revisión formal de dependencias y proyecciones. Los datos de una emisión son hechos históricos y no se sustituyen por datos maestros actuales. La eliminación de resguardos y la descomposición adicional de esas proyecciones se trata separadamente, con una política de conservación y reproducción documental.

No se cambiaron las tablas polimórficas para intentar imponer una FK convencional sobre ellas. El nombre operativo y las reglas de un recurso reservable no se confundieron con las características físicas de la habitación o espacio.

Documentación actualizada:

- [Diccionario del esquema actual](estado-actual/diccionario-tablas.md).
- [Esquema observado, sin valores personales](estado-actual/esquema-observado.json).
- [Grafo completo de FK](estado-actual/relaciones-fk.mmd).
- [Evidencia de activación y verificación](verificacion-bloque-aplicado.json).

Los archivos del diagnóstico de 148 tablas se conservan como fotografía anterior. Los respaldos y configuración privada permanecen en `.local/normalizacion-criticos/`, excluida del repositorio.
