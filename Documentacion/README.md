# Hotel Bugambilias — Documentación del Sistema

## Módulos Documentados

| Módulo              | Archivo                                                  | Estado   |
| ------------------- | -------------------------------------------------------- | -------- |
| Catálogos           | [doc/catalogos/flujos.md](catalogos/flujos.md)           | Completo |
| Servicios           | [doc/servicios/flujos.md](servicios/flujos.md)           | Completo |
| Habitaciones        | [doc/habitaciones/flujos.md](habitaciones/flujos.md)     | Completo |
| Espacios & Mesas    | [doc/habitaciones/espacios.md](habitaciones/espacios.md) | Completo |
| Promociones         | [doc/promociones/flujos.md](promociones/flujos.md)       | Completo |
| Clientes / Usuarios | [doc/clientes/flujos.md](clientes/flujos.md)             | Completo |
| Limpieza            | [doc/limpieza/flujos.md](limpieza/flujos.md)             | Completo |
| Compras             | [doc/compras/flujos.md](compras/flujos.md)               | Completo |
| Inventario          | [doc/inventario/flujos.md](inventario/flujos.md)         | Completo |
| Activos Fijos       | [doc/activos/flujos.md](activos/flujos.md)               | Completo |
| Restaurante         | [doc/restaurante/flujos.md](restaurante/flujos.md)       | Completo |
| Facturación         | [doc/facturacion/flujos.md](facturacion/flujos.md)       | Completo |
| Requerimientos      | [requerimientos/README.md](requerimientos/README.md)     | Completo |
| Pruebas de Carga k6 | [../tests/k6/README.md](../tests/k6/README.md)           | Completo |
| Background Jobs     | [jobs/README.md](jobs/README.md)                         | Completo |

## Convenciones

- Cada módulo tiene su carpeta bajo `doc/`
- El archivo `flujos.md` contiene la documentación de flujos de proceso
- La plantilla incluye: Descripción de la Pantalla, Disparador, Flujo Paso a Paso
- La sección de Arquitectura muestra la estructura de archivos del módulo

## Procesos y auditoría

- [Manual de procesos y subprocesos](auditoria/manual-procesos-subprocesos.md): fichas, responsables, controles y matriz de auditoría.
- [Validación de correcciones críticas](auditoria/validacion-criticos.md): alcance y evidencia de las pruebas ejecutadas.
- [Revisión del submódulo de cocina](auditoria/revision-submodulo-cocina.md): hallazgos, reproducciones y prioridades para materias primas, pedidos, bebidas y buffet.
- [Correcciones implementadas de cocina y buffet](auditoria/correcciones-cocina-implementadas.md): cambios, validación y activación.
- [Guía operativa de cocina, bebidas y buffet](restaurante/operacion-cocina-buffet.md).
