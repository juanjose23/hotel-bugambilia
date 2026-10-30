# Validación de correcciones críticas

Fecha: 28 de octubre de 2026.

## Entorno y alcance

PostgreSQL 18 independiente en 127.0.0.1:55448; base `hotel_auditoria_sintetica`, datos generados exclusivamente para pruebas. Directorio de datos `.local/auditoria-criticos/pgdata`. No se utilizó ni modificó la base real. Las migraciones completas se aplicaron correctamente. La instancia se detiene al terminar y sus datos se conservan localmente.

## Resultados

- Regresión previa en SQLite: 14 pruebas, 62 comprobaciones aprobadas.
- Pruebas existentes en PostgreSQL: ProcesarCobroCuentaTest, CrearYConfirmarPagoStripeCuentaTest y ZonasDeliveryTest: 5 pruebas, 27 comprobaciones aprobadas.
- Concurrencia real con dos procesos PHP separados y barrera de inicio: consumir 6 unidades de un stock de 10 permite una operación y rechaza la otra; quedan 4 unidades. Dos liquidaciones del saldo completo producen un pago y una venta; la otra operación se rechaza por cuenta cerrada. Dos emisiones para una venta producen una factura y rechazan el segundo intento.
- Se comprobó el saldo con los impuestos configurados: 115 para un consumo base de 100. La prueba de liquidación utiliza el saldo completo, no el subtotal.
- Fallos deliberados mediante triggers de PostgreSQL en movimientos, pagos y detalles de factura: las tres operaciones se revierten. Se verificaron stock, pagos, facturas, folios y correlativo antes y después.
- Migración de productos simples en stocks: aplicar, revertir y reaplicar conserva la cantidad. La reversión con stock sin variante se rechaza antes de alterar el esquema.
- PHPStan nivel 9 sobre los archivos de aplicación corregidos: sin errores, validado en la entrega anterior. Pint y revisión de espacios de los cambios: aprobados.

Evidencia local: `.local/auditoria-criticos/resultados-concurrencia.json`; ejecutores `worker.php` y `run.py` en ese mismo directorio. Se excluyó `.local/` de Git para evitar incorporar datos y archivos del servidor.

## Registros locales

No se encontró una política específica de conservación documental. Se depuraron los contextos y mensajes históricos de 1024 entradas GoogleAuth en un archivo local, conservando la fecha y la existencia del evento. De estas entradas, 230 contenían query_params o target_url según el análisis previo. La comprobación posterior encontró cero entradas GoogleAuth con esos dos campos. No se eliminaron archivos completos ni se guardaron copias que perpetúen los datos sensibles. Esto no acredita la depuración de copias externas o servidores remotos.

## Revisión antes del despliegue

Los cambios de esta corrección se limitan a GoogleAuthController, CrearPedidoPublicoController, ObtenerDepartamentosDelivery, ObtenerStockParaConsumo, ProcesarCobroCuenta, RegistrarPagoCuenta, la reversión de la migración de stocks, las pruebas CorreccionesCriticasTest y la exclusión de .local. El repositorio contiene numerosos cambios ajenos; no deben incluirse automáticamente en la entrega.

No se hizo commit, publicación ni despliegue. No se reescribieron esquemas reales. Las pruebas de concurrencia cubren los escenarios descritos; no constituyen una prueba de carga ni validan por sí solas todos los caminos de pago, lotes compartidos, proveedores externos o efectos secundarios asíncronos.

Para preparar producción: revisar únicamente el alcance anterior, establecer el respaldo y procedimiento de reversión aplicable al entorno de destino y verificar la configuración de logs. La migración editada corresponde al método down; no hace falta revertir la base real para instalar estas correcciones. La aprobación del despliegue queda como paso separado.
