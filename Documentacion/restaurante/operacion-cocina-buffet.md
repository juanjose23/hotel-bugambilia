# Operación de cocina, bebidas y buffet

Corte: 28 de octubre de 2026. Describe las correcciones y funciones implementadas; su activación en otro entorno requiere las migraciones y configuración correspondientes.

## 1. Preparación inicial

- Mantener productos y variantes distintos cuando cambia el artículo físico: carne entera, corte limpio, salsa elaborada, botella cerrada y bebida medida.
- Configurar unidades: kilogramo/gramo/libra, litro/mililitro y unidades o empaques. El peso se declara en gramos, el volumen en mililitros y unidades_por_empaque expresa el contenido del empaque. Las transformaciones solo permiten medidas compatibles.
- Configurar las bodegas de cocina y bar en el formulario de espacios. Las mesas pueden heredar las del espacio principal. Para operaciones sin mesa se conserva la resolución de una ubicación de cocina/bar activa; conviene configurar un punto de venta explícito cuando haya más de uno.
- Revisar lotes, caducidades, costos y cantidades antes de usar el circuito. Si un lote no coincide con sus existencias, la operación se detiene: no se corrige automáticamente información histórica.
- Dar a los responsables los permisos correspondientes de Filament/Shield. La página nueva «Buffets y eventos» usa el control de acceso de páginas; no se asignaron permisos a usuarios de producción.

## 2. Transformar o porcionar materia prima

Desde «Materia Prima Cocina», seleccionar producto, variante, bodega y cantidad de origen. Registrar los resultados útiles y las pérdidas. Las variantes destino deben pertenecer al producto indicado y tener unidades compatibles.

Ejemplo: retirar 1 kg de carne y obtener 0,8 kg de corte limpio. Si no se especifican los 0,2 kg restantes, quedan registrados como pérdida de rendimiento. No se permite generar 100 kg desde 1 kg sin entradas adicionales. Para una elaboración con varios ingredientes se usa la confirmación de producción del apartado siguiente.

Los costos definitivos se calculan en servidor desde los lotes consumidos, se distribuyen entre resultados útiles y conservan el total con redondeo a centavos. La pérdida se registra sin descontar el origen una segunda vez. Si todo es merma, el costo se atribuye a la pérdida. No se aceptan lotes vencidos o no disponibles.

Cada resultado útil crea su propio lote. El vencimiento no puede superar el de las entradas; se puede indicar una fecha menor. No se mezclan cantidades anteriores bajo un lote nuevo.

## 3. Producir preparaciones por lote

En «Preparaciones y costeo», crear primero un borrador con la receta y cantidad de porciones planificadas. El borrador no cambia inventario.

Desde su edición, usar «Confirmar producción e ingresar resultado»: indicar variante elaborada, ubicación, cantidad realmente obtenida y vencimiento. La confirmación descuenta los ingredientes una sola vez y crea el lote elaborado con el costo real. El rendimiento físico exige medidas compatibles entre las entradas y el resultado; no se asume densidad de líquidos ni se convierten unidades incompatibles silenciosamente.

Una producción confirmada no puede volver a confirmarse, editarse o eliminarse para ocultar sus movimientos. Los registros anteriores a la migración quedan identificados como históricos y no pueden utilizarse para consumir otra vez. Los documentos generados por pedidos son registros de su consumo, no órdenes de producción adicionales.

Si un plato utiliza una preparación ya producida, su receta debe referenciar la variante elaborada. No debe volver a incluir los ingredientes crudos que ya fueron consumidos para producirla.

## 4. Bebidas y empaques

- Venta envasada: vender una botella o lata descuenta unidades del producto y genera movimientos. Si las existencias están repartidas entre lotes, se toman las cantidades necesarias de cada uno.
- Apertura o fraccionamiento: configurar la botella, por ejemplo, con volumen de 750 ml y transformar una unidad cerrada en hasta 750 ml de la variante medida. El costo y la trazabilidad pasan a ese lote.
- Caja: con 24 unidades por empaque y volumen de 355 ml por unidad contenida, la transformación puede generar 24 latas equivalentes. No basta escribir «caja» en el nombre: el contenido debe estar configurado.
- Cóctel: su receta debe usar la variante medida, por ejemplo, 45 ml por preparación. El rendimiento de la receta se aplica tanto al consumo como al costo por porción.
- Área: un ítem de bar utiliza la bodega de bar del espacio cuando está configurada. No debe descontar de una cocina ajena por coincidencia de identificadores.

## 5. Pedidos, anulación y merma

Enviar a preparación es el momento de consumo del circuito actual. Debe hacerse cuando el trabajo vaya a comenzar. Repetir el envío no duplica consumo ni cargo; reenviar productos usa el mismo mecanismo que el primer envío.

Anular una partida en una cuenta abierta sin pagos desactiva su cargo y recalcula el saldo. La existencia física consumida se conserva: cancelar comercialmente no equivale a devolver alimentos. Si hay pagos o la cuenta está cerrada, se exige primero el ajuste financiero autorizado. No se automatizó un reembolso externo con esta acción.

La merma diaria de cocina utiliza sus propias existencias y exige un motivo. Descuenta la cantidad física y el lote, y registra la pérdida. No registrar otra baja de ingredientes que ya fueron consumidos por una partida anulada: su disposición física debe revisarse sin duplicar el descuento.

Un despacho de abastecimiento ya registrado no puede repetirse con la misma solicitud. El traslado conserva el lote; no se considera consumo ni incrementa el saldo total del lote.

## 6. Buffet por persona, por evento o mixto

En «Buffets y eventos»:

1. Crear el servicio con fecha, modalidad, moneda y precios. Puede relacionarse con una reserva o evento.
2. Indicar comensales previstos para planificación. Este dato no descuenta inventario.
3. Enviar o reponer alimentos desde existencias de cocina/bar. Se conserva su lote y se separan las cantidades asignadas al buffet.
4. Al cerrar, registrar personas realmente atendidas y medir, para cada existencia, sobrante y desperdicio. El consumo atribuido al servicio se obtiene por diferencia. Este cálculo no identifica cuánto comió cada persona.
5. El sobrante permanece separado en el buffet cerrado y no vuelve automáticamente a existencias disponibles. Puede registrarse su descarte con motivo. No se implementó devolución automática para reutilizarlo.
6. Agregar el importe a una cuenta abierta de la misma moneda. Si se indicó una reserva, la cuenta también debe corresponderle. El cargo no equivale a registrar un pago: caja continúa con el cobro normal.

En modalidad mixta, las personas por cobrar son adicionales a las incluidas en el paquete. Ejemplo: un paquete de 1.000 y dos adicionales de 100 generan un cargo base de 1.200; los impuestos/cargos siguen las reglas de la cuenta. El mismo buffet no puede cargarse dos veces.

## 7. Alcance de activación

Las migraciones se probaron con datos sintéticos, sin modificar la base real. Antes de activar en otro entorno: respaldar, revisar duplicados por variante/lote y saldos históricos, aplicar las cuatro migraciones de cocina de este cambio y configurar unidades, recetas, bodegas y permisos. Los métodos de reversión rechazan eliminar estructuras que ya contengan producción o buffet registrado.

Estas funciones no reparan retroactivamente pedidos, lotes o cuentas afectados antes de la corrección. Su conciliación requiere revisar la evidencia histórica y autorizar cada ajuste. No se introdujeron cambios de precios comerciales, rendimientos sanitarios ni políticas de conservación decididos automáticamente por el software.
