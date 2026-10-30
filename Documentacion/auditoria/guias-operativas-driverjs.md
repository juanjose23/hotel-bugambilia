# Guías operativas dentro del administrador

Implementadas con Driver.js 1.4 y Filament 5. El botón **Guía paso a paso** aparece en las pantallas registradas; se puede repetir siempre que se necesite. **Siguiente**, **Anterior**, **Terminar guía** y **Cerrar guía** permiten recorrer y salir de la ayuda. Escape cierra la guía conservando el formulario abierto.

## Cobertura

38 definiciones de pantalla/recurso, heredadas por sus páginas de listado, creación y edición cuando corresponda:

- Cocina y restaurante: control diario, materias primas y porciones, comandas, despacho, mesas, preparaciones, recetas y platos, buffets y conciliación de recetas.
- Caja y facturación: inicio agrupado, turno, gestión de cajas, historial de cierres, cuentas, pagos/ventas, facturas, comprobantes, series y autorizaciones.
- Tesorería: bancos, conciliación, fondos, movimientos, fondos de turno, caja chica, entregas y devoluciones.
- Recepción: reservas, entrada y salida.
- Compras e inventario: solicitudes, cotizaciones, órdenes, recepción, existencias, lotes y conteos físicos.
- Habitaciones: tablero de limpieza y lavandería.

9 guías adicionales dentro de formularios, mediante **Guía de este paso**:

1. Apertura de turno.
2. Cobrar una cuenta.
3. Arqueo y cierre.
4. Revisar un comprobante de transferencia.
5. Crear buffet/evento.
6. Organizar su menú.
7. Enviar o reponer alimentos.
8. Cerrar servicio midiendo sobrantes y desperdicios.
9. Registrar el cargo en una cuenta.

Las guías no ejecutan las operaciones explicadas ni completan formularios automáticamente. Señalan elementos presentes y visibles; campos condicionales, pestañas cerradas y acciones no autorizadas se omiten. Para conocer un campo condicional, primero activa la opción correspondiente y vuelve a abrir la guía. La consulta no sustituye las validaciones ni los permisos existentes.

## Mantenimiento

- Catálogo: `config/guias_operativas.php`, por clase de página o recurso; incluye títulos, explicaciones, selectores y formularios asociados.
- Resolución por pantalla: `app/Filament/Support/Guias/CatalogoGuiasOperativas.php`.
- Botón de formulario: `app/Filament/Shared/Actions/VerGuiaAction.php`.
- Motor: `resources/js/filament/guias/index.ts`; estilos en `resources/css/filament/guias.css`.
- Integración: hooks del panel y entrada independiente de Vite. Driver se importa al abrir una guía.
- Usar `data-guia` para controles propios. Comprobar selectores compartidos al actualizar Filament.
- No incluir datos de clientes, importes reales ni credenciales en las definiciones. No requiere cambios de base de datos.

## Verificación

- 189 pruebas de guías, restaurante, tesorería y agrupación de facturación: aprobadas, 2019 comprobaciones; base sintética independiente.
- PHPStan, TypeScript, ESLint y compilación: aprobados.
- Ocho pantallas del proyecto principal renderizadas con guía bajo una transacción estrictamente de solo lectura.
- Navegador con datos ficticios: cocina, preparación, buffet y caja; ayuda dentro de buffet y cobro, borrador conservado, Escape, foco del teclado y limpieza al navegar.
- Vista móvil de 390 px sin desbordamiento de la guía.
- Evidencias locales: `.local/guias-operativas/`. No se confirmaron operaciones financieras ni se alteró inventario para probar las guías.

Documentación de la biblioteca: https://driverjs.com/docs/installation
