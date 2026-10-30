# Manual de procesos y subprocesos del sistema Hotel Bugambilias

Versión 1.0 · Corte documental: 28 de octubre de 2026 · Estado: borrador técnico para revisión y aprobación operativa.

## 1. Introducción y propósito

### 1.1 Objetivo

Documentar y estandarizar los procesos operativos y técnicos del sistema, identificando sus entradas, transformaciones, salidas, responsables funcionales, controles y evidencias. Servir de base para auditorías y para la aprobación de los responsables del hotel.

### 1.2 Alcance

Comprende sitio público, portal del cliente, panel administrativo, reservas y estancias, restaurante, servicios, promociones, cuentas, pagos, facturación, compras, inventarios, activos, limpieza, colaboradores y mantenimiento técnico. Describe el software examinado; no certifica procedimientos ejecutados fuera del sistema ni cumplimiento legal o fiscal.

### 1.3 Método y límites

Se contrastaron documentación por módulo, rutas, estructura de casos de uso y código crítico con el informe de pruebas realizadas en esta conversación. Una función documentada o una prueba existente no se considera automáticamente una función validada. Los responsables son perfiles funcionales propuestos: la dirección debe asignar titulares y suplentes. Las rutas e instrucciones internas se citan como evidencia, no como instrucciones que deba ejecutar un cliente.

- **Documentado / implementado, pendiente validación integral:** existe documentación o código; no se declara el proceso terminado.
- **Validado parcialmente:** pasaron escenarios concretos; los pendientes de su ficha siguen abiertos.
- **Pendiente de acreditar:** existe un requerimiento o procedimiento propuesto, sin evidencia suficiente de operación efectiva.
- **Falta optimizar:** requiere una medición que demuestre el problema; no se asigna por suposición.

La columna «lógica completa» de la matriz utiliza «No determinada» cuando no existe aceptación integral. «Parcial» en errores significa evidencia limitada, no necesariamente ausencia del control. La columna de transacciones y seguridad distingue ambos conceptos; una consulta no necesita por sí sola una transacción de escritura. No se utilizan los ejemplos de la plantilla como hallazgos del sistema.

## 2. Mapa general de procesos

| Macroproceso | Procesos incluidos | Resultado principal |
| --- | --- | --- |
| 1. Usuarios y accesos | ACC | Identidad autenticada y permisos aplicables |
| 2. Operación y atención al cliente | CLI, HAB, RES, RST, SRV, PRO | Recursos ofrecidos, reservas, estancia y servicios atendidos |
| 3. Gestión financiera y facturación | FIN, FAC | Cuentas, pagos, ventas y documentos conciliables |
| 4. Recursos y administración interna | CAT, COM, INV, ACT, LIM, COL | Abastecimiento, existencias, bienes, limpieza y personal |
| 5. Soporte técnico y persistencia | TEC | Evidencia, reportes y procedimientos de continuidad |

Flujo operativo de referencia: catálogo de recursos → disponibilidad → reserva → estancia y consumos → cuenta → cobro → venta → factura. Compras abastece inventario; cocina y limpieza consumen recursos; activos soporta los espacios; accesos, auditoría y soporte son transversales. Algunos servicios y pedidos pueden originarse sin reserva de habitación.

## 3. Fichas detalladas por proceso y subproceso

Identificación: `PROC-XXX` identifica el proceso y `SUB-XXX-NN` su subproceso. El mismo código se utiliza en la matriz del apartado 4.

### PROC-ACC. Gestión de usuarios y accesos

**Macroproceso:** 1. **Objetivo:** Permitir acceso y asignar facultades según identidad y función.

**Responsables funcionales:** Administración / usuario titular; el sistema ejecuta las validaciones y persistencia.

**Evidencia base:** [routes/public/auth.php](../../routes/public/auth.php), [app/Http/Controllers/Auth/AuthController.php](../../app/Http/Controllers/Auth/AuthController.php), [app/Http/Controllers/Auth/GoogleAuthController.php](../../app/Http/Controllers/Auth/GoogleAuthController.php).

#### SUB-ACC-01. Registro e identidad

- **Responsable:** Administración / usuario titular.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Datos personales, identificación y correo.
- **Proceso / lógica:** Validar la solicitud y delegar el alta o resolución de identidad al caso de uso correspondiente.
- **Salidas:** Cliente o usuario registrado; errores de validación.
- **Controles observados o documentados:** Form Requests y resolución de conflictos de identidad.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Probar duplicados, identidad conflictiva y altas simultáneas.
- **Evidencia:** fuentes de PROC-ACC; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-ACC-02. Inicio y cierre de sesión

- **Responsable:** Administración / usuario titular.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Credenciales o autorización Google; solicitud de salida.
- **Proceso / lógica:** Autenticar, regenerar sesión y dirigir al portal o administración; cerrar la sesión según la ruta.
- **Salidas:** Sesión o respuesta controlada.
- **Controles observados o documentados:** Login y registro tienen throttle:auth; logout tiene throttle:logout; Google devuelve errores controlados y registra contexto limitado.
- **Estado actual:** Validado parcialmente.
- **Próxima verificación / criterio de aceptación:** Verificar límite efectivo, expiración, cierre de sesión y éxito de Google en entorno de integración.
- **Evidencia:** fuentes de PROC-ACC; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-ACC-03. Roles, permisos y credenciales

- **Responsable:** Administración / usuario titular.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Usuario, rol y permisos solicitados.
- **Proceso / lógica:** Asignar permisos administrativos y gestionar cambios de contraseña.
- **Salidas:** Acceso habilitado o denegado; credencial actualizada.
- **Controles observados o documentados:** Spatie Permission, Filament Shield y middleware de cambio de contraseña forman parte de la implementación.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Validar una matriz de permisos por puesto, accesos directos y separación de funciones.
- **Evidencia:** fuentes de PROC-ACC; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

### PROC-CLI. Gestión de clientes y portal

**Macroproceso:** 2. **Objetivo:** Mantener la identidad del cliente y ofrecer consulta de su operación.

**Responsables funcionales:** Recepción / cliente; el sistema ejecuta las validaciones y persistencia.

**Evidencia base:** [Documentacion/clientes/flujos.md](../../Documentacion/clientes/flujos.md), [routes/public/portal.php](../../routes/public/portal.php), [app/Interactors/Clientes](../../app/Interactors/Clientes).

#### SUB-CLI-01. Perfil y acompañantes

- **Responsable:** Recepción / cliente.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Cliente autenticado; datos personales y acompañantes.
- **Proceso / lógica:** Consultar y actualizar información vinculada al cliente.
- **Salidas:** Ficha y acompañantes actualizados.
- **Controles observados o documentados:** El portal dispone de controladores y rutas autenticadas.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Probar acceso cruzado a datos de otro cliente y consentimiento de uso.
- **Evidencia:** fuentes de PROC-CLI; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-CLI-02. Reservas, pedidos y cuenta del cliente

- **Responsable:** Recepción / cliente.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Identidad autenticada y filtros.
- **Proceso / lógica:** Recuperar reservas, pedidos y detalle de cuenta para el portal.
- **Salidas:** Resumen de actividad y saldo.
- **Controles observados o documentados:** Casos de uso específicos de consulta del portal.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Comprobar pertenencia de cada recurso y concordancia con recepción y caja.
- **Evidencia:** fuentes de PROC-CLI; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

### PROC-HAB. Habitaciones y espacios

**Macroproceso:** 2. **Objetivo:** Administrar los recursos físicos ofrecidos y su publicación.

**Responsables funcionales:** Recepción / administración; el sistema ejecuta las validaciones y persistencia.

**Evidencia base:** [Documentacion/habitaciones/flujos.md](../../Documentacion/habitaciones/flujos.md), [Documentacion/habitaciones/espacios.md](../../Documentacion/habitaciones/espacios.md), [app/Interactors/Habitaciones](../../app/Interactors/Habitaciones), [app/Interactors/Espacios](../../app/Interactors/Espacios).

#### SUB-HAB-01. Alta y actualización de recursos

- **Responsable:** Recepción / administración.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Tipo, capacidad, categoría y datos de habitación o espacio.
- **Proceso / lógica:** Registrar o actualizar el recurso y sus relaciones.
- **Salidas:** Habitación o espacio administrativo.
- **Controles observados o documentados:** Existen recursos administrativos y reglas de capacidad y operatividad documentadas.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Validar claves únicas, referencias y cambios sobre recursos con reservas.
- **Evidencia:** fuentes de PROC-HAB; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-HAB-02. Publicación y consulta

- **Responsable:** Recepción / administración.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Estado, visibilidad, categoría y criterios de búsqueda.
- **Proceso / lógica:** Filtrar recursos publicables y presentar detalle, imágenes y precios.
- **Salidas:** Catálogo público y ficha del recurso.
- **Controles observados o documentados:** Consulta de habitaciones precarga categoría, detalle, imágenes y precios.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Medir el listado de habitaciones sin paginación con volumen representativo.
- **Evidencia:** fuentes de PROC-HAB; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-HAB-03. Mesas y mobiliario operativo

- **Responsable:** Recepción / administración.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Espacio padre, subespacios y activos asociados.
- **Proceso / lógica:** Relacionar mesas con recursos físicos y evaluar capacidad y operatividad.
- **Salidas:** Mesa o espacio habilitado para uso.
- **Controles observados o documentados:** Reglas de activo físico y capacidad descritas en la documentación de espacios.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Probar que un recurso inoperativo no pueda reservarse ni utilizarse.
- **Evidencia:** fuentes de PROC-HAB; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

### PROC-RES. Reservas y estancias

**Macroproceso:** 2. **Objetivo:** Administrar disponibilidad, reserva y ocupación hasta su cierre.

**Responsables funcionales:** Cliente / recepción; el sistema ejecuta las validaciones y persistencia.

**Evidencia base:** [Documentacion/reservas/flujos-publicos.md](../../Documentacion/reservas/flujos-publicos.md), [app/Interactors/Reservas](../../app/Interactors/Reservas), [app/Interactors/Reservas/Gestion/CrearReserva.php](../../app/Interactors/Reservas/Gestion/CrearReserva.php).

#### SUB-RES-01. Búsqueda y disponibilidad

- **Responsable:** Cliente / recepción.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Fechas, capacidad y habitación o espacio.
- **Proceso / lógica:** Consultar disponibilidad por recurso y período y presentar opciones.
- **Salidas:** Opciones y períodos disponibles.
- **Controles observados o documentados:** Existen consultas de disponibilidad y reglas de fechas; hay índice compuesto para recurso, estado y período.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Probar reservas simultáneas del mismo recurso y límites exactos entre períodos.
- **Evidencia:** fuentes de PROC-RES; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-RES-02. Creación de reserva y complementos

- **Responsable:** Cliente / recepción.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Cliente, recurso, fechas, adicionales y canal de pago.
- **Proceso / lógica:** Validar, calcular unidades y totales y persistir reserva y detalles.
- **Salidas:** Código de reserva y estado inicial.
- **Controles observados o documentados:** CrearReserva utiliza transacciones; el estado depende del canal y política de pago.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Validar atomicidad de adicionales, vencimiento del bloqueo y recuperación tras cobro externo fallido.
- **Evidencia:** fuentes de PROC-RES; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-RES-03. Confirmación y avisos

- **Responsable:** Cliente / recepción.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Reserva pendiente y evidencia de pago o autorización.
- **Proceso / lógica:** Aplicar transición de estado y generar avisos asociados.
- **Salidas:** Reserva confirmada y notificación según canal.
- **Controles observados o documentados:** Existen casos de uso de confirmación y recordatorios.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Comprobar idempotencia; un correo fallido requiere seguimiento o reintento, no implica que un pago externo pueda revertirse con una transacción SQL.
- **Evidencia:** fuentes de PROC-RES; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-RES-04. Ingreso y cambios de estancia

- **Responsable:** Cliente / recepción.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Reserva, huéspedes y habitación asignada.
- **Proceso / lógica:** Registrar check-in; gestionar extensión o cambio de habitación.
- **Salidas:** Estancia activa y asignación actualizada.
- **Controles observados o documentados:** Casos de uso separados para ingreso, requisitos, extensión y cambio.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Probar cambio de habitación ocupada, capacidad y diferencias de tarifa.
- **Evidencia:** fuentes de PROC-RES; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-RES-05. Salida, cancelación y no presentación

- **Responsable:** Cliente / recepción.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Estancia o reserva, motivo y saldo.
- **Proceso / lógica:** Comprobar requisitos; cerrar estancia o cancelar y determinar consecuencias económicas.
- **Salidas:** Estado final, liberación del recurso y cargos o solicitud de devolución.
- **Controles observados o documentados:** Existen casos de uso diferenciados de check-out, cancelación, no-show y limpieza de pendientes.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Probar saldos impagos, cancelación repetida y conciliación de reembolsos.
- **Evidencia:** fuentes de PROC-RES; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

### PROC-RST. Restaurante y delivery

**Macroproceso:** 2. **Objetivo:** Gestionar la oferta gastronómica y el pedido hasta su entrega.

**Responsables funcionales:** Mesero / cocina / caja / cliente; el sistema ejecuta las validaciones y persistencia.

**Evidencia base:** [Documentacion/restaurante/flujos.md](../../Documentacion/restaurante/flujos.md), [app/Interactors/Restaurante](../../app/Interactors/Restaurante), [app/Http/Controllers/Restaurante/CrearPedidoPublicoController.php](../../app/Http/Controllers/Restaurante/CrearPedidoPublicoController.php).

#### SUB-RST-01. Menú, recetas y precios

- **Responsable:** Mesero / cocina / caja / cliente.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Plato, categoría, receta y precios por moneda.
- **Proceso / lógica:** Registrar la oferta y vincular receta y disponibilidad comercial.
- **Salidas:** Menú administrativo y público.
- **Controles observados o documentados:** La documentación incluye validación de código y precio activo duplicado.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Probar vigencias superpuestas, receta incompleta y cambios de costos.
- **Evidencia:** fuentes de PROC-RST; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-RST-02. Toma de pedido y mesas

- **Responsable:** Mesero / cocina / caja / cliente.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Mesa o cliente; productos, cantidades y observaciones.
- **Proceso / lógica:** Crear pedido y partidas, calcular importes y enviarlo a preparación.
- **Salidas:** Pedido y comanda.
- **Controles observados o documentados:** Casos de uso de pedido, recálculo y envío a cocina.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Probar repetición de envío, mesa no operativa y cambios concurrentes.
- **Evidencia:** fuentes de PROC-RST; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-RST-03. Preparación, consumo y servicio

- **Responsable:** Mesero / cocina / caja / cliente.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Pedido e ingredientes disponibles.
- **Proceso / lógica:** Consumir ingredientes y registrar cambios de preparación y entrega.
- **Salidas:** Partidas preparadas o servidas y movimientos de inventario.
- **Controles observados o documentados:** Existen casos de uso de disponibilidad, consumo y transiciones por ítem.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Validar receta con sustituciones, anulación y reversión de todos los movimientos.
- **Evidencia:** fuentes de PROC-RST; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-RST-04. Pedido público y zonas de entrega

- **Responsable:** Mesero / cocina / caja / cliente.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Dirección, municipio, zona, artículos y método de pago.
- **Proceso / lógica:** Validar cobertura y generar pedido y respuesta de pago si corresponde.
- **Salidas:** Pedido, importe de entrega y seguimiento.
- **Controles observados o documentados:** Errores inesperados del endpoint se registran y devuelven mensaje seguro; fallback de zonas limitado a tabla ausente en PostgreSQL.
- **Estado actual:** Validado parcialmente.
- **Próxima verificación / criterio de aceptación:** Probar recorrido público completo y fallos externos; una lista vacía de zonas activas no debe habilitar tarifas alternativas.
- **Evidencia:** fuentes de PROC-RST; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

### PROC-SRV. Servicios del hotel

**Macroproceso:** 2. **Objetivo:** Publicar servicios y registrar su solicitud durante la estancia.

**Responsables funcionales:** Administración / recepción / cliente; el sistema ejecuta las validaciones y persistencia.

**Evidencia base:** [Documentacion/servicios/flujos.md](../../Documentacion/servicios/flujos.md), [app/Interactors/Servicios](../../app/Interactors/Servicios), [app/Interactors/Reservas/Estancias/SolicitarServicioEstancia.php](../../app/Interactors/Reservas/Estancias/SolicitarServicioEstancia.php).

#### SUB-SRV-01. Catálogo y tarifas de servicios

- **Responsable:** Administración / recepción / cliente.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Servicio, categoría, imágenes y precios.
- **Proceso / lógica:** Administrar y publicar servicios activos y su detalle.
- **Salidas:** Oferta de servicios y tarifas.
- **Controles observados o documentados:** La documentación describe código único, estado y visibilidad pública.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Probar vigencias de tarifa y acceso a servicios desactivados.
- **Evidencia:** fuentes de PROC-SRV; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-SRV-02. Solicitud durante estancia

- **Responsable:** Administración / recepción / cliente.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Estancia activa, servicio y cantidad.
- **Proceso / lógica:** Validar la solicitud y registrar el servicio asociado a la estancia.
- **Salidas:** Solicitud y cargos cuando corresponda.
- **Controles observados o documentados:** Caso de uso específico SolicitarServicioEstancia.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Probar cupos, servicio repetido, autorización y cargo correcto.
- **Evidencia:** fuentes de PROC-SRV; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

### PROC-PRO. Promociones y políticas comerciales

**Macroproceso:** 2. **Objetivo:** Definir descuentos, paquetes y condiciones aplicables.

**Responsables funcionales:** Administración comercial; el sistema ejecuta las validaciones y persistencia.

**Evidencia base:** [Documentacion/promociones/flujos.md](../../Documentacion/promociones/flujos.md), [app/Interactors/Promociones](../../app/Interactors/Promociones), [app/Interactors/Politicas](../../app/Interactors/Politicas).

#### SUB-PRO-01. Definición de promociones y paquetes

- **Responsable:** Administración comercial.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Vigencia, componentes, condiciones y beneficio.
- **Proceso / lógica:** Crear o editar promociones y relacionar sus componentes.
- **Salidas:** Promoción o paquete disponible según configuración.
- **Controles observados o documentados:** Existen formularios, relaciones y casos de uso del dominio.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Probar fechas inválidas, componentes desactivados y composición incompatible.
- **Evidencia:** fuentes de PROC-PRO; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-PRO-02. Aplicación de beneficios y políticas

- **Responsable:** Administración comercial.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Reserva o cuenta, cliente y promoción elegida.
- **Proceso / lógica:** Evaluar condiciones y calcular el beneficio o cargo aplicable.
- **Salidas:** Totales ajustados y uso del beneficio cuando corresponda.
- **Controles observados o documentados:** Hay reglas de promociones de reserva y beneficios de cuenta.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Verificar acumulación, repetición, límites y reversión ante cancelación.
- **Evidencia:** fuentes de PROC-PRO; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

### PROC-FIN. Cuentas, cobros y pagos

**Macroproceso:** 3. **Objetivo:** Mantener saldos y registrar pagos sin duplicaciones.

**Responsables funcionales:** Caja / recepción / sistema; el sistema ejecuta las validaciones y persistencia.

**Evidencia base:** [app/Interactors/Cuentas](../../app/Interactors/Cuentas), [app/Interactors/Facturacion/Stripe](../../app/Interactors/Facturacion/Stripe), [Documentacion/facturacion/flujos.md](../../Documentacion/facturacion/flujos.md), [Documentacion/auditoria/validacion-criticos.md](../../Documentacion/auditoria/validacion-criticos.md).

#### SUB-FIN-01. Apertura y cargos de cuenta

- **Responsable:** Caja / recepción / sistema.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Cliente, estancia o pedido y conceptos de consumo.
- **Proceso / lógica:** Abrir o asociar cuenta y recalcular importes, impuestos y saldo.
- **Salidas:** Cuenta con cargos detallados.
- **Controles observados o documentados:** Hay casos de uso separados de cuenta, detalle y recálculo.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Comprobar monedas, redondeos, descuentos y asociación a estancia.
- **Evidencia:** fuentes de PROC-FIN; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-FIN-02. Cobro y cierre de cuenta

- **Responsable:** Caja / recepción / sistema.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Cuenta, moneda, monto y forma de pago.
- **Proceso / lógica:** Bloquear cuenta, validar estado y pago, registrar y cerrar si se liquida.
- **Salidas:** Pago, saldo y venta al cierre.
- **Controles observados o documentados:** Bloqueo dentro de transacción y validación del estado bloqueado; escenario simultáneo y reversión de pagos probados.
- **Estado actual:** Validado parcialmente.
- **Próxima verificación / criterio de aceptación:** Extender pruebas a abonos, pagos divididos, reintentos externos y cierres desde todos los canales.
- **Evidencia:** fuentes de PROC-FIN; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-FIN-03. Intento y confirmación por Stripe

- **Responsable:** Caja / recepción / sistema.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Cuenta o reserva, moneda e importe; confirmación o webhook.
- **Proceso / lógica:** Crear intento de pago y procesar confirmación de la pasarela.
- **Salidas:** Transacción pendiente, capturada o fallida y su conciliación.
- **Controles observados o documentados:** Integración y verificación de firma están implementadas; pruebas seleccionadas de cuenta con Stripe aprobadas con simulación.
- **Estado actual:** Validado parcialmente.
- **Próxima verificación / criterio de aceptación:** Validar credenciales de prueba, firma inválida, eventos repetidos y eventos fuera de orden en integración externa.
- **Evidencia:** fuentes de PROC-FIN; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-FIN-04. Anulación y reembolso

- **Responsable:** Caja / recepción / sistema.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Pago o reserva y motivo de devolución.
- **Proceso / lógica:** Determinar monto, solicitar devolución y actualizar registros de conciliación.
- **Salidas:** Pago anulado o devolución y estado registrado.
- **Controles observados o documentados:** Existen casos de uso y pruebas de reembolso; no se ejecutaron todos en esta revisión.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Comprobar devolución parcial, fallo de proveedor y reintento sin duplicación.
- **Evidencia:** fuentes de PROC-FIN; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

### PROC-FAC. Facturación y documentos fiscales

**Macroproceso:** 3. **Objetivo:** Emitir y conservar documentos a partir de ventas.

**Responsables funcionales:** Caja / administración; el sistema ejecuta las validaciones y persistencia.

**Evidencia base:** [Documentacion/facturacion/flujos.md](../../Documentacion/facturacion/flujos.md), [app/Interactors/Facturacion/EmitirFacturaDesdeVenta.php](../../app/Interactors/Facturacion/EmitirFacturaDesdeVenta.php), [Documentacion/auditoria/validacion-criticos.md](../../Documentacion/auditoria/validacion-criticos.md).

#### SUB-FAC-01. Series, autorizaciones y folios

- **Responsable:** Caja / administración.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Serie, correlativo y rango autorizado.
- **Proceso / lógica:** Configurar parámetros y reservar folio dentro del rango.
- **Salidas:** Serie y folio identificables.
- **Controles observados o documentados:** ReservarFolioFactura valida correlativo contra el rango configurado.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Verificar vigencias y permisos; los datos configurados no acreditan cumplimiento fiscal legal.
- **Evidencia:** fuentes de PROC-FAC; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-FAC-02. Emisión de factura

- **Responsable:** Caja / administración.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Venta, serie y datos del receptor.
- **Proceso / lógica:** Bloquear venta, impedir nueva emisión, reservar folio y crear cabecera y detalles.
- **Salidas:** Factura y folio emitido.
- **Controles observados o documentados:** Transacción y bloqueos; emisión simultánea, reintento y rollback de detalle probados.
- **Estado actual:** Validado parcialmente.
- **Próxima verificación / criterio de aceptación:** Probar todas las monedas, notas y variantes de documento.
- **Evidencia:** fuentes de PROC-FAC; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-FAC-03. Anulación y consulta documental

- **Responsable:** Caja / administración.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Factura y motivo; filtros de consulta.
- **Proceso / lógica:** Validar estado y anular o recuperar el documento autorizado.
- **Salidas:** Factura anulada o documento consultable.
- **Controles observados o documentados:** La documentación describe bloqueo, transacción, motivo y usuario de anulación.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Validar autorización, inmutabilidad histórica y tratamiento fiscal aplicable.
- **Evidencia:** fuentes de PROC-FAC; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

### PROC-CAT. Catálogos, monedas y parámetros

**Macroproceso:** 4. **Objetivo:** Mantener datos maestros consistentes para la operación.

**Responsables funcionales:** Administración; el sistema ejecuta las validaciones y persistencia.

**Evidencia base:** [Documentacion/catalogos/flujos.md](../../Documentacion/catalogos/flujos.md), [app/Interactors/Catalogos](../../app/Interactors/Catalogos), [app/Interactors/Monedas](../../app/Interactors/Monedas).

#### SUB-CAT-01. Tipos y entradas de catálogo

- **Responsable:** Administración.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Tipo, código, descripción y estado.
- **Proceso / lógica:** Registrar y clasificar valores de referencia.
- **Salidas:** Catálogos para formularios y relaciones.
- **Controles observados o documentados:** Existen recursos administrativos y organización por tipos.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Verificar duplicados y uso de valores desactivados en operaciones históricas.
- **Evidencia:** fuentes de PROC-CAT; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-CAT-02. Monedas y tasas de cambio

- **Responsable:** Administración.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Divisa, tasa y fecha de vigencia.
- **Proceso / lógica:** Configurar la moneda y obtener la tasa utilizada en cálculos.
- **Salidas:** Importes convertidos y referencia de tasa.
- **Controles observados o documentados:** Hay casos de uso de conversión y consultas de moneda predeterminada.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Probar tasa faltante, cambio de moneda base y conservación de la tasa histórica.
- **Evidencia:** fuentes de PROC-CAT; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

### PROC-COM. Compras y proveedores

**Macroproceso:** 4. **Objetivo:** Abastecer al hotel y documentar la recepción de bienes.

**Responsables funcionales:** Compras / autorizador / almacén; el sistema ejecuta las validaciones y persistencia.

**Evidencia base:** [Documentacion/compras/flujos.md](../../Documentacion/compras/flujos.md), [app/Interactors/Compras](../../app/Interactors/Compras).

#### SUB-COM-01. Solicitud y cotizaciones

- **Responsable:** Compras / autorizador / almacén.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Necesidad, artículos, cantidades y proveedores.
- **Proceso / lógica:** Registrar solicitud, comparar cotizaciones y seleccionar propuesta.
- **Salidas:** Solicitud y selección de oferta.
- **Controles observados o documentados:** La documentación describe estados y selección de ganador.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Validar segregación solicitante-aprobador y criterios de comparación.
- **Evidencia:** fuentes de PROC-COM; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-COM-02. Orden y recepción

- **Responsable:** Compras / autorizador / almacén.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Oferta seleccionada, orden y cantidades recibidas.
- **Proceso / lógica:** Emitir orden y registrar recepción con lotes e ingreso a inventario.
- **Salidas:** Orden, recepción y existencias actualizadas.
- **Controles observados o documentados:** El flujo documentado conecta recepción con creador de lotes e inventario.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Probar recepción parcial, exceso recibido y atomicidad recepción-stock.
- **Evidencia:** fuentes de PROC-COM; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-COM-03. Devoluciones y seguimiento

- **Responsable:** Compras / autorizador / almacén.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Recepción, artículos, cantidades y motivo.
- **Proceso / lógica:** Registrar devolución y su efecto operativo y documental.
- **Salidas:** Devolución y movimientos relacionados.
- **Controles observados o documentados:** Existen recursos de devolución y reportes de compras.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Comprobar cantidades ya consumidas y conciliación con proveedor.
- **Evidencia:** fuentes de PROC-COM; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

### PROC-INV. Inventarios y almacenes

**Macroproceso:** 4. **Objetivo:** Controlar existencias, lotes y movimientos físicos.

**Responsables funcionales:** Almacén / cocina / limpieza; el sistema ejecuta las validaciones y persistencia.

**Evidencia base:** [Documentacion/inventario/flujos.md](../../Documentacion/inventario/flujos.md), [app/Interactors/Inventario](../../app/Interactors/Inventario), [app/Repository/Models/Inventario/Stock.php](../../app/Repository/Models/Inventario/Stock.php), [Documentacion/auditoria/validacion-criticos.md](../../Documentacion/auditoria/validacion-criticos.md).

#### SUB-INV-01. Productos, variantes y composición

- **Responsable:** Almacén / cocina / limpieza.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Producto, unidad, variante y componentes.
- **Proceso / lógica:** Definir el artículo y su posible composición en kits.
- **Salidas:** Catálogo de inventario.
- **Controles observados o documentados:** Existen modelos y casos de uso de productos y variantes.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Verificar compatibilidad de unidades y composición sin ciclos.
- **Evidencia:** fuentes de PROC-INV; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-INV-02. Consumo y movimientos

- **Responsable:** Almacén / cocina / limpieza.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Producto, bodega, cantidad y documento de origen.
- **Proceso / lógica:** Seleccionar stock disponible, ordenar por vencimiento y descontar registrando movimiento.
- **Salidas:** Stock actualizado y trazabilidad de consumo.
- **Controles observados o documentados:** ConsumirStock usa transacción; consulta bloquea filas ordenadas; concurrencia sobre el mismo stock y rollback probados.
- **Estado actual:** Validado parcialmente.
- **Próxima verificación / criterio de aceptación:** Extender concurrencia a lotes compartidos por varias bodegas y otras rutas de consumo.
- **Evidencia:** fuentes de PROC-INV; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-INV-03. Lotes, caducidad y cuarentena

- **Responsable:** Almacén / cocina / limpieza.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Lote, fechas, estado y decisión de revisión.
- **Proceso / lógica:** Identificar caducidades y tramitar cuarentena, liberación o rechazo.
- **Salidas:** Estado de lote y movimientos asociados.
- **Controles observados o documentados:** Existen reglas de lote, procesos de cuarentena y jobs de caducidad.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Verificar ejecución efectiva del scheduler y exclusión de lotes no disponibles.
- **Evidencia:** fuentes de PROC-INV; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-INV-04. Traslados, conteo y conciliación

- **Responsable:** Almacén / cocina / limpieza.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Origen, destino y cantidades; conteo físico.
- **Proceso / lógica:** Trasladar existencias o comparar conteo y registrar diferencias.
- **Salidas:** Saldos por ubicación y ajustes trazables.
- **Controles observados o documentados:** La documentación describe traslado transaccional y conciliación de inventario físico.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Probar operaciones simultáneas, repetición de importaciones y autorización de ajustes.
- **Evidencia:** fuentes de PROC-INV; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

### PROC-ACT. Activos fijos y mantenimiento

**Macroproceso:** 4. **Objetivo:** Mantener trazabilidad del bien físico durante su vida útil.

**Responsables funcionales:** Responsable de activos / mantenimiento; el sistema ejecuta las validaciones y persistencia.

**Evidencia base:** [Documentacion/activos/flujos.md](../../Documentacion/activos/flujos.md), [app/Interactors/Activos](../../app/Interactors/Activos).

#### SUB-ACT-01. Registro e individualización

- **Responsable:** Responsable de activos / mantenimiento.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Bien, adquisición y datos identificadores.
- **Proceso / lógica:** Registrar el activo y generar identificación individual.
- **Salidas:** Ficha e identificador del activo.
- **Controles observados o documentados:** La documentación contempla códigos de barras e individualización.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Verificar unicidad y relación con la recepción de compra.
- **Evidencia:** fuentes de PROC-ACT; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-ACT-02. Asignación y mantenimiento

- **Responsable:** Responsable de activos / mantenimiento.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Activo, ubicación o responsable; plan o incidencia.
- **Proceso / lógica:** Asignar o trasladar el activo y registrar mantenimientos.
- **Salidas:** Asignación y mantenimiento con historial.
- **Controles observados o documentados:** Hay procesos de asignación y jobs preventivos, de aviso y sincronización.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Probar activo en mantenimiento, pérdida de ubicación y doble asignación.
- **Evidencia:** fuentes de PROC-ACT; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-ACT-03. Garantía, depreciación y baja

- **Responsable:** Responsable de activos / mantenimiento.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Activo, garantía, fecha de cálculo y motivo de baja.
- **Proceso / lógica:** Consultar garantía, calcular depreciación configurada y tramitar baja.
- **Salidas:** Valor registrado y estado final del bien.
- **Controles observados o documentados:** Procesos descritos en la documentación de activos.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Verificar método de depreciación con contabilidad y autorización de bajas.
- **Evidencia:** fuentes de PROC-ACT; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

### PROC-LIM. Limpieza y lavandería

**Macroproceso:** 4. **Objetivo:** Planificar y ejecutar limpieza y registrar sus recursos.

**Responsables funcionales:** Supervisor / personal asignado; el sistema ejecuta las validaciones y persistencia.

**Evidencia base:** [Documentacion/limpieza/flujos.md](../../Documentacion/limpieza/flujos.md), [app/Interactors/Limpieza](../../app/Interactors/Limpieza), [tests/Feature/Limpieza](../../tests/Feature/Limpieza).

#### SUB-LIM-01. Turnos, horarios y asignación

- **Responsable:** Supervisor / personal asignado.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Personal, turno, recurso y frecuencia.
- **Proceso / lógica:** Definir programación y materializar ejecuciones pendientes.
- **Salidas:** Trabajo asignado y avisos.
- **Controles observados o documentados:** Se documentan responsables, carritos y comprobación de ejecución existente por día y recurso.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Probar materialización simultánea y disponibilidad del personal.
- **Evidencia:** fuentes de PROC-LIM; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-LIM-02. Ejecución, checklist y reposición

- **Responsable:** Supervisor / personal asignado.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Trabajo asignado, tareas e insumos.
- **Proceso / lógica:** Registrar avance, consumos y conclusión; actualizar el recurso atendido.
- **Salidas:** Ejecución completada y consumo asociado.
- **Controles observados o documentados:** Hay estados de ejecución, checklist y casos de uso de stock de carrito.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Verificar autorización del asignado, faltantes y cierre con checklist incompleto.
- **Evidencia:** fuentes de PROC-LIM; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-LIM-03. Lavandería y abastecimiento

- **Responsable:** Supervisor / personal asignado.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Textiles o insumos, proceso y cantidades.
- **Proceso / lógica:** Registrar entradas, consumos y operación de lavandería.
- **Salidas:** Proceso y movimientos relacionados.
- **Controles observados o documentados:** Existen pruebas y casos de uso de lavandería y consumo de jornada.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Ejecutar el recorrido completo y conciliar consumo físico con sistema.
- **Evidencia:** fuentes de PROC-LIM; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

### PROC-COL. Colaboradores y datos laborales

**Macroproceso:** 4. **Objetivo:** Registrar al colaborador y mantener historial laboral.

**Responsables funcionales:** Administración de personal; el sistema ejecuta las validaciones y persistencia.

**Evidencia base:** [app/Interactors/Colaboradores](../../app/Interactors/Colaboradores), [app/Interactors/Colaboradores/CrearNuevoSalario.php](../../app/Interactors/Colaboradores/CrearNuevoSalario.php).

#### SUB-COL-01. Alta y datos del colaborador

- **Responsable:** Administración de personal.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Persona, puesto y documentación.
- **Proceso / lógica:** Relacionar identidad con ficha laboral y código.
- **Salidas:** Colaborador y datos de referencia.
- **Controles observados o documentados:** Existe dominio de colaboradores y generación de código.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Validar acceso restringido y consistencia persona-usuario-colaborador.
- **Evidencia:** fuentes de PROC-COL; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-COL-02. Historial salarial

- **Responsable:** Administración de personal.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Colaborador, salario y fecha de vigencia.
- **Proceso / lógica:** Registrar el nuevo salario y actualizar historial mediante caso de uso.
- **Salidas:** Historial salarial actualizado.
- **Controles observados o documentados:** CrearNuevoSalario utiliza transacción.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Probar períodos superpuestos y permisos; esto no acredita un sistema completo de nómina.
- **Evidencia:** fuentes de PROC-COL; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

### PROC-TEC. Auditoría, reportes y mantenimiento técnico

**Macroproceso:** 5. **Objetivo:** Conservar evidencias y sostener la operación del sistema.

**Responsables funcionales:** Administración técnica / responsables de módulo; el sistema ejecuta las validaciones y persistencia.

**Evidencia base:** [Documentacion/jobs/README.md](../../Documentacion/jobs/README.md), [routes/console.php](../../routes/console.php), [Documentacion/requerimientos/14_requerimientos_no_funcionales.md](../../Documentacion/requerimientos/14_requerimientos_no_funcionales.md), [Documentacion/auditoria/validacion-criticos.md](../../Documentacion/auditoria/validacion-criticos.md).

#### SUB-TEC-01. Reportes y exportaciones

- **Responsable:** Administración técnica / responsables de módulo.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Tipo de reporte, filtros y usuario.
- **Proceso / lógica:** Obtener datos y generar documento; gestionar trabajos pesados según configuración.
- **Salidas:** PDF, exportación o notificación de disponibilidad.
- **Controles observados o documentados:** Existen generadores, visor de reportes y procesamiento en segundo plano.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Validar permisos, token del visor, datos masivos y presentación de cada reporte.
- **Evidencia:** fuentes de PROC-TEC; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-TEC-02. Tareas programadas y colas

- **Responsable:** Administración técnica / responsables de módulo.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Evento, configuración de tarea y calendario.
- **Proceso / lógica:** Ejecutar jobs y registrar su resultado.
- **Salidas:** Recordatorios, actualizaciones y estado de trabajo.
- **Controles observados o documentados:** Hay scheduler, jobs y auditoría de ejecuciones; el código no demuestra que el worker de producción esté activo.
- **Estado actual:** Documentado / implementado; pendiente validación integral.
- **Próxima verificación / criterio de aceptación:** Verificar supervisión, reintentos, alertas y ausencia de duplicación.
- **Evidencia:** fuentes de PROC-TEC; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-TEC-03. Auditoría y registros seguros

- **Responsable:** Administración técnica / responsables de módulo.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Cambio de entidad, usuario y evento técnico.
- **Proceso / lógica:** Guardar trazabilidad de cambios y diagnóstico con datos mínimos.
- **Salidas:** Historial auditable y registros de operación.
- **Controles observados o documentados:** Factura implementa auditoría; se corrigieron logs de Google y se depuraron 1024 entradas locales.
- **Estado actual:** Validado parcialmente.
- **Próxima verificación / criterio de aceptación:** Definir política de logs y verificar cobertura y retención de copias externas.
- **Evidencia:** fuentes de PROC-TEC; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

#### SUB-TEC-04. Migraciones, respaldos y recuperación

- **Responsable:** Administración técnica / responsables de módulo.
- **Disparador:** solicitud del usuario autorizado, evento operativo o tarea programada según el flujo descrito.
- **Entradas:** Cambio de esquema o incidente; respaldo disponible.
- **Proceso / lógica:** Validar en entorno aislado y aplicar procedimiento autorizado; restaurar y comprobar consistencia.
- **Salidas:** Esquema validado o servicio recuperado.
- **Controles observados o documentados:** Migraciones y reversión específica de stocks probadas en PostgreSQL; RNF-11 exige respaldo diario y retención de 30 días.
- **Estado actual:** Validado parcialmente para migraciones; respaldos y recuperación pendientes de acreditar.
- **Próxima verificación / criterio de aceptación:** No se acreditó respaldo automático ni restauración integral; probar RTO menor de una hora y RPO menor de 24 horas como objetivos, no resultados.
- **Evidencia:** fuentes de PROC-TEC; para escenarios ejecutados, consultar [validación de críticos](validacion-criticos.md).

## 4. Matriz resumen de control

La aprobación integral queda a cargo de los responsables; la matriz no sustituye los resultados de pruebas ni certifica producción.

| ID | Subproceso | ¿Lógica completa? | ¿Validación de errores? | Transacciones / seguridad | Observaciones y pendientes |
| --- | --- | --- | --- | --- | --- |
| SUB-ACC-01 | Registro e identidad | No determinada | Parcial / pendiente de prueba | Autenticación / permisos; no toda acción requiere transacción | Probar duplicados, identidad conflictiva y altas simultáneas. |
| SUB-ACC-02 | Inicio y cierre de sesión | No determinada | Sí, en escenarios probados | Autenticación / permisos; no toda acción requiere transacción | Verificar límite efectivo, expiración, cierre de sesión y éxito de Google en entorno de integración. |
| SUB-ACC-03 | Roles, permisos y credenciales | No determinada | Parcial / pendiente de prueba | Autenticación / permisos; no toda acción requiere transacción | Validar una matriz de permisos por puesto, accesos directos y separación de funciones. |
| SUB-CLI-01 | Perfil y acompañantes | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Probar acceso cruzado a datos de otro cliente y consentimiento de uso. |
| SUB-CLI-02 | Reservas, pedidos y cuenta del cliente | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Comprobar pertenencia de cada recurso y concordancia con recepción y caja. |
| SUB-HAB-01 | Alta y actualización de recursos | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Validar claves únicas, referencias y cambios sobre recursos con reservas. |
| SUB-HAB-02 | Publicación y consulta | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Medir el listado de habitaciones sin paginación con volumen representativo. |
| SUB-HAB-03 | Mesas y mobiliario operativo | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Probar que un recurso inoperativo no pueda reservarse ni utilizarse. |
| SUB-RES-01 | Búsqueda y disponibilidad | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Probar reservas simultáneas del mismo recurso y límites exactos entre períodos. |
| SUB-RES-02 | Creación de reserva y complementos | No determinada | Parcial / pendiente de prueba | Transacción observada | Validar atomicidad de adicionales, vencimiento del bloqueo y recuperación tras cobro externo fallido. |
| SUB-RES-03 | Confirmación y avisos | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Comprobar idempotencia; un correo fallido requiere seguimiento o reintento, no implica que un pago externo pueda revertirse con una transacción SQL. |
| SUB-RES-04 | Ingreso y cambios de estancia | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Probar cambio de habitación ocupada, capacidad y diferencias de tarifa. |
| SUB-RES-05 | Salida, cancelación y no presentación | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Probar saldos impagos, cancelación repetida y conciliación de reembolsos. |
| SUB-RST-01 | Menú, recetas y precios | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Probar vigencias superpuestas, receta incompleta y cambios de costos. |
| SUB-RST-02 | Toma de pedido y mesas | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Probar repetición de envío, mesa no operativa y cambios concurrentes. |
| SUB-RST-03 | Preparación, consumo y servicio | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Validar receta con sustituciones, anulación y reversión de todos los movimientos. |
| SUB-RST-04 | Pedido público y zonas de entrega | No determinada | Sí, en escenarios probados | Seguridad por verificar; transacción no determinada | Probar recorrido público completo y fallos externos; una lista vacía de zonas activas no debe habilitar tarifas alternativas. |
| SUB-SRV-01 | Catálogo y tarifas de servicios | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Probar vigencias de tarifa y acceso a servicios desactivados. |
| SUB-SRV-02 | Solicitud durante estancia | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Probar cupos, servicio repetido, autorización y cargo correcto. |
| SUB-PRO-01 | Definición de promociones y paquetes | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Probar fechas inválidas, componentes desactivados y composición incompatible. |
| SUB-PRO-02 | Aplicación de beneficios y políticas | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Verificar acumulación, repetición, límites y reversión ante cancelación. |
| SUB-FIN-01 | Apertura y cargos de cuenta | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Comprobar monedas, redondeos, descuentos y asociación a estancia. |
| SUB-FIN-02 | Cobro y cierre de cuenta | No determinada | Sí, en escenarios probados | Transacción observada; bloqueo observado | Extender pruebas a abonos, pagos divididos, reintentos externos y cierres desde todos los canales. |
| SUB-FIN-03 | Intento y confirmación por Stripe | No determinada | Sí, en escenarios probados | Seguridad por verificar; transacción no determinada | Validar credenciales de prueba, firma inválida, eventos repetidos y eventos fuera de orden en integración externa. |
| SUB-FIN-04 | Anulación y reembolso | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Comprobar devolución parcial, fallo de proveedor y reintento sin duplicación. |
| SUB-FAC-01 | Series, autorizaciones y folios | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Verificar vigencias y permisos; los datos configurados no acreditan cumplimiento fiscal legal. |
| SUB-FAC-02 | Emisión de factura | No determinada | Sí, en escenarios probados | Transacción observada; bloqueo observado | Probar todas las monedas, notas y variantes de documento. |
| SUB-FAC-03 | Anulación y consulta documental | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Validar autorización, inmutabilidad histórica y tratamiento fiscal aplicable. |
| SUB-CAT-01 | Tipos y entradas de catálogo | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Verificar duplicados y uso de valores desactivados en operaciones históricas. |
| SUB-CAT-02 | Monedas y tasas de cambio | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Probar tasa faltante, cambio de moneda base y conservación de la tasa histórica. |
| SUB-COM-01 | Solicitud y cotizaciones | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Validar segregación solicitante-aprobador y criterios de comparación. |
| SUB-COM-02 | Orden y recepción | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Probar recepción parcial, exceso recibido y atomicidad recepción-stock. |
| SUB-COM-03 | Devoluciones y seguimiento | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Comprobar cantidades ya consumidas y conciliación con proveedor. |
| SUB-INV-01 | Productos, variantes y composición | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Verificar compatibilidad de unidades y composición sin ciclos. |
| SUB-INV-02 | Consumo y movimientos | No determinada | Sí, en escenarios probados | Transacción observada; bloqueo observado | Extender concurrencia a lotes compartidos por varias bodegas y otras rutas de consumo. |
| SUB-INV-03 | Lotes, caducidad y cuarentena | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Verificar ejecución efectiva del scheduler y exclusión de lotes no disponibles. |
| SUB-INV-04 | Traslados, conteo y conciliación | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Probar operaciones simultáneas, repetición de importaciones y autorización de ajustes. |
| SUB-ACT-01 | Registro e individualización | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Verificar unicidad y relación con la recepción de compra. |
| SUB-ACT-02 | Asignación y mantenimiento | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Probar activo en mantenimiento, pérdida de ubicación y doble asignación. |
| SUB-ACT-03 | Garantía, depreciación y baja | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Verificar método de depreciación con contabilidad y autorización de bajas. |
| SUB-LIM-01 | Turnos, horarios y asignación | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Probar materialización simultánea y disponibilidad del personal. |
| SUB-LIM-02 | Ejecución, checklist y reposición | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Verificar autorización del asignado, faltantes y cierre con checklist incompleto. |
| SUB-LIM-03 | Lavandería y abastecimiento | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Ejecutar el recorrido completo y conciliar consumo físico con sistema. |
| SUB-COL-01 | Alta y datos del colaborador | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Validar acceso restringido y consistencia persona-usuario-colaborador. |
| SUB-COL-02 | Historial salarial | No determinada | Parcial / pendiente de prueba | Transacción observada | Probar períodos superpuestos y permisos; esto no acredita un sistema completo de nómina. |
| SUB-TEC-01 | Reportes y exportaciones | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Validar permisos, token del visor, datos masivos y presentación de cada reporte. |
| SUB-TEC-02 | Tareas programadas y colas | No determinada | Parcial / pendiente de prueba | Seguridad por verificar; transacción no determinada | Verificar supervisión, reintentos, alertas y ausencia de duplicación. |
| SUB-TEC-03 | Auditoría y registros seguros | No determinada | Sí, en escenarios probados | Seguridad por verificar; transacción no determinada | Definir política de logs y verificar cobertura y retención de copias externas. |
| SUB-TEC-04 | Migraciones, respaldos y recuperación | No determinada | Sí, en escenarios probados | Entorno aislado validado; recuperación no acreditada | No se acreditó respaldo automático ni restauración integral; probar RTO menor de una hora y RPO menor de 24 horas como objetivos, no resultados. |

## 5. Controles transversales y tratamiento de fallos

1. Validar entrada y autorización en servidor; el control visual no sustituye la autorización sobre el recurso solicitado.
2. En operaciones críticas, conservar coherencia entre cabeceras, detalles, saldos y movimientos mediante transacciones y controles de concurrencia.
3. Distinguir errores de negocio de fallos inesperados; registrar contexto mínimo y devolver mensajes seguros.
4. Separar la atomicidad SQL de efectos externos: un rollback no deshace automáticamente un cobro o correo enviado. Definir reintentos, idempotencia y compensación según el caso.
5. Conservar evidencia de usuario, fecha, motivo y documento de origen cuando el proceso lo permita; comprobar la cobertura de auditoría por entidad.
6. Tratar archivos de prueba, credenciales y datos de PostgreSQL local como material excluido del repositorio.
7. No dar por ejecutados en producción el scheduler, los workers, los respaldos o las políticas de retención por el solo hecho de estar documentados.

## 6. Evidencia de pruebas y aceptación

El [informe de validación crítica](validacion-criticos.md) registra 14 pruebas y 62 comprobaciones en memoria; 5 pruebas y 27 comprobaciones seleccionadas en PostgreSQL; escenarios simultáneos de consumo, liquidación y factura; fallos inducidos con reversión; y ciclo de migración de stocks. Se utilizaron datos sintéticos, sin modificar la base real. No acredita todos los escenarios de Stripe ni equivale a una prueba de carga.

El repositorio contiene otras pruebas por dominio; su existencia no significa que hayan sido ejecutadas en este corte. Los requerimientos de 150 usuarios simultáneos, tiempos de respuesta, disponibilidad y recuperación son objetivos pendientes de medición, no resultados de las pruebas descritas.

Para cerrar cada subproceso, su responsable debe registrar: código SUB, caso ejecutado, entorno, datos utilizados, resultado esperado y observado, evidencia, incidencias, fecha y aprobador. «Terminado» solo procede después de aceptación funcional y técnica acordada.

## 7. Plan de cierre documental y operativo

| Prioridad | Actividad | Responsable funcional | Evidencia de cierre |
| --- | --- | --- | --- |
| Alta | Aprobar permisos por puesto y probar acceso a datos de terceros | Administración / seguridad | Matriz de permisos y resultados negativos de autorización |
| Alta | Completar integración Stripe y reembolsos con entorno de prueba externo | Desarrollo / caja | Eventos firmados, repetidos, fallidos y conciliación comprobada |
| Alta | Probar recuperación integral desde respaldo aislado | Soporte técnico | Restauración verificada y tiempos medidos frente a RNF-11 |
| Alta | Validar disponibilidad simultánea y liberación de reservas | Recepción / desarrollo | Prueba de mismo recurso y período sin sobreventa |
| Media | Ampliar concurrencia a lotes compartidos, abonos y otros canales | Almacén / caja / desarrollo | Consistencia de stock y saldo en esos escenarios |
| Media | Ejecutar recorridos completos de compras, activos, limpieza y personal | Responsables de módulo | Actas por código de subproceso |
| Media | Medir reportes, listados y rendimiento con volumen realista | Soporte / desarrollo | Medidas reproducibles y acciones de optimización justificadas |
| Media | Formalizar retención y depuración de logs y copias externas | Administración técnica | Política aprobada y evidencia de aplicación |

## 8. Control y actualización del manual

Responsable de custodia propuesto: administración del sistema. Revisores: recepción, caja, restaurante, almacén, limpieza, activos y personal. Aprobador: dirección del hotel. Todos pendientes de designación nominal.

Actualizar este manual al modificar un caso de uso, permisos, estados, relaciones de datos, integración externa o procedimiento de recuperación. Conservar versión, fecha, responsable y motivo del cambio. El respaldo diario con retención mínima de 30 días señalado en RNF-11 corresponde a respaldos; no debe interpretarse automáticamente como política de retención de logs.

| Versión | Fecha | Cambio | Aprobación |
| --- | --- | --- | --- |
| 1.0 | 2026-10-28 | Inventario inicial de procesos y matriz con evidencia disponible | Pendiente de responsables operativos |

> Actualización de evidencia: consultar la [revisión específica de cocina](revision-submodulo-cocina.md). Se reprodujeron inconsistencias en consumo, transformaciones, lotes y cargos; los estados del manual general no sustituyen ese diagnóstico.

## Actualización del circuito de cocina y buffet

Consultar [correcciones implementadas](correcciones-cocina-implementadas.md) y la [guía operativa](../restaurante/operacion-cocina-buffet.md). La implementación incorpora producción confirmada y buffet mixto, además de corregir los hallazgos de la revisión específica.
