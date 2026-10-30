# Asistente de recetas y menú de buffet

## Propósito

El encargado aprende el recorrido en la misma pantalla: elegir, revisar ingredientes, seguir la ficha, confirmar el resultado, enviar al buffet y conciliar. El proceso mantiene separados la preparación, el servicio y el cobro.

## Preparar una receta

1. En Control de cocina, entrar en **Elaborar una receta** y pulsar **Preparar una receta**.
2. Elegir el platillo y el número entero de porciones. El sistema usa el rendimiento de la receta para calcular sus ingredientes.
3. Revisar cantidades y existencias. Las cantidades conservan su unidad (kg, ml, unidades). Los lotes no disponibles o vencidos no cuentan como disponibles. Si hay faltantes, solicitar reposición antes de elaborar.
4. Leer la ficha registrada para el platillo. El encargado con permiso de edición puede mantenerla en Restaurante & Cocina → Platos, en **Ficha de preparación para el equipo**, escribiendo un paso por línea. Si está vacía, la pantalla lo indica; no se inventan instrucciones de cocción.
5. Guardar la planificación. Se guarda un borrador con ingredientes calculados desde la receta del servidor; no se retira inventario.
6. Elaborar físicamente y entrar en **Confirmar preparación**. Elegir la bodega, revisar sus ingredientes y registrar el producto terminado, la cantidad obtenida en su unidad y el vencimiento.
7. Confirmar una sola vez. Se consumen los ingredientes y se crea el lote del resultado dentro de una transacción. Una falla revierte la operación. La vigencia del resultado no se extiende más allá de sus ingredientes.
8. El listado distingue **Plan sin confirmar** y **Preparación terminada**, mostrando producto y cantidad obtenida. Los registros terminados quedan para consulta.

Los cambios de receta o porciones deben guardarse antes de abrir la confirmación. El servidor rechaza una receta que no corresponda al platillo y no acepta ingredientes o costos inventados en el formulario.

## Organizar un buffet

Al crear el servicio se definen dos cosas diferentes:

- **Cobro:** por persona, paquete de evento o paquete con personas adicionales.
- **Quién sirve:** las personas, un mesero o ambos.

El menú contiene cada platillo, su presentación terminada y las porciones previstas. Por ejemplo: res con arroz en porciones de 350 g y arroz de acompañamiento en kg. Una persona puede elegir ambos platillos; no se convierte en dos personas por cobrar.

**Organizar menú** permite completar servicios anteriores o ajustar los abiertos. Los servicios anteriores deben organizar su menú antes de habilitar nuevos envíos desde la interfaz. No se permiten platillos repetidos, presentaciones ambiguas ni quitar productos ya enviados. No se acepta como resultado un ingrediente de la propia receta.

## Enviar, servir y cerrar

1. Preparar la receta o disponer del producto terminado.
2. Enviar o reponer únicamente presentaciones del menú. La salida traslada producto listo de cocina/bar al buffet y conserva los lotes. No vuelve a consumir la receta.
3. En **Ver menú y guía**, consultar los platillos y la explicación para el tipo de servicio elegido.
4. En autoservicio, las personas eligen y se sirven; se controlan cantidades enviadas, reposiciones y sobrantes. En servicio por mesero, el mesero entrega las porciones siguiendo la ficha del platillo. Ambos usan el mismo control físico del buffet.
5. Al terminar, registrar asistentes reales, sobrantes y desperdicios medidos, incluidos los ceros. La cantidad servida se concilia como enviado menos sobrante menos desperdicio. Las porciones previstas son planificación, no un contador automático de platos servidos ni un registro de lo que comió cada cliente.
6. Conservar los sobrantes separados hasta registrar su disposición. Registrar el cargo del evento en la cuenta correspondiente, sin cobrar de nuevo cada platillo servido.

## Demostración y comprobaciones

Esta ampliación está activada únicamente en `hotel_cocina_demo`, en `127.0.0.1:55448`. Acceso: http://127.0.0.1:8029/admin/restaurante/control-cocina.

La demostración incluye una ficha de res con arroz, un menú con dos platillos y autoservicio. Se confirmó una preparación de diez porciones y se enviaron dos al buffet. Se verificó que el envío redujo el producto terminado de diez a ocho unidades y conservó las cantidades de ingredientes posteriores a cocinar.

La migración `2026_10_29_000001_add_guided_recipes_and_buffet_menu` conserva los servicios anteriores y marca su servicio como por definir hasta que se organice el menú. Su reversión se bloquea si existen instrucciones o menús para evitar perderlos. No se modificó la base real.

Validación: 138 pruebas aprobadas, análisis PHPStan nivel 9 sin errores en los archivos modificados, formato Pint y revisión en navegador de escritorio y teléfono de 390 px.
