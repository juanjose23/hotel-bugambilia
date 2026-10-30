# Facturación y caja: acceso unificado

La navegación financiera utiliza un solo clúster y una pantalla de entrada en `/admin/facturacion`. El menú principal muestra **Facturación y caja**; las pantallas del módulo permiten desplazarse mediante su navegación interna.

Se agruparon 21 componentes en cuatro secciones:

| Sección | Opciones |
|---|---|
| Cobros y documentos | Cuentas, facturas, ventas, transacciones y comprobantes pendientes |
| Caja y cierres | Mi turno, gestión de cajas, arqueos/cierres, entregas y devoluciones |
| Fondos y bancos | Fondos y bancos, libro de fondos, fondos para turnos, caja chica y conciliaciones bancaria y de pasarela |
| Configuración fiscal | Series, autorizaciones DGI, folios, cargos de facturación y pasarelas |

Cada usuario ve únicamente las opciones autorizadas por sus permisos actuales. El clúster no concede permisos adicionales; tampoco se muestra ni permite el acceso cuando el usuario no tiene acceso a ninguna opción financiera. La activación de tesorería mantiene su control de configuración existente.

La pantalla inicial presenta las opciones agrupadas, sin repetir el menú secundario a su lado. Dentro de las pantallas financieras se utiliza la navegación del clúster. Se reutilizó la clase existente `FacturacionConfigCluster` para conservar sus referencias internas, actualizando su propósito, etiqueta y dirección.

Se conservaron 31 rutas anteriores mediante redirecciones dentro de las rutas autenticadas del panel. Los documentos mantienen sus identificadores y se conservan los filtros de búsqueda. Los enlaces que ya coincidían con la dirección canónica continúan con su ruta actual.

Validación: **92 pruebas aprobadas, 387 aserciones** de navegación, permisos, redirecciones, caja, cuentas e integridad fiscal. PHPStan nivel 9 sin errores en los archivos modificados y compilación correcta. El inicio y cuatro pantallas financieras se verificaron en la base principal dentro de una transacción de solo lectura.

Esta entrega modifica navegación, rutas de compatibilidad y presentación; conserva los datos, operaciones y permisos financieros existentes.

[Evidencia de verificación](cluster-facturacion-verificacion.json).
