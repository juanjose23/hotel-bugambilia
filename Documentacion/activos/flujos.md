# Documentación de Flujos de Procesos: Módulo Activos Fijos

## 1. Submódulo / Funcionalidad: Registro de Activos Fijos

- **Descripción de la Pantalla / Vista:** Tabla con todos los activos fijos del hotel. Formulario con: código de inventario (auto-generado), nombre, categoría, marca, modelo, número de serie, fecha de compra, valor de adquisición, ubicación, estado, asignación.
- **Disparador (Trigger):** Acceso desde `Activos > Activos Fijos` en el panel admin. También se crean automáticamente al recibir productos tipo activo fijo en compras.
- **Flujo Paso a Paso:**
    1. El usuario accede a la interfaz de Activos y visualiza la tabla con filtros (estado, categoría, ubicación).
    2. El sistema valida los permisos y muestra datos con relaciones (asignaciones, mantenimientos, garantías).
    3. El usuario crea un activo manualmente o el sistema lo crea automáticamente al recepcionar una compra (`ProcesadorIndividualizacionActivos` o `CreadorActivoConAsignacion`).
    4. El sistema genera el código de inventario con el `GeneradorCodigoInventario` (prefijo por categoría + año + correlativo: "TV-2026-0001").
    5. El `CreadorActivoConAsignacion` también crea el stock y registra el movimiento inicial.

---

## 2. Submódulo / Funcionalidad: Individualización de Activos

- **Descripción de la Pantalla / Vista:** Proceso de asignar códigos de inventario individuales a activos comprados en lote. Muestra el progreso de individualización (cantidad registrada vs total).
- **Disparador (Trigger):** Al recibir productos tipo activo fijo en una recepción de compra.
- **Flujo Paso a Paso:**
    1. Al recepcionar una compra con productos tipo activo fijo, el sistema ejecuta `ProcesadorIndividualizacionCompra`.
    2. El sistema encuentra o crea un registro de individualización con la cantidad total.
    3. `ProcesadorIndividualizacionActivos` crea los activos individuales:
        - Genera código de inventario único para cada activo.
        - Asigna a la ubicación correspondiente.
        - Crea registro de stock individual.
        - Registra movimiento de entrada.
    4. Las `ReglasIndividualizacion` validan que no se exceda la cantidad total.
    5. El sistema actualiza el estado: `EnProceso` → `Completado` cuando todos están individualizados.

---

## 3. Submódulo / Funcionalidad: Asignación de Activos

- **Descripción de la Pantalla / Vista:** Registro de a quién/pertenece cada activo. Puede asignarse a una Ubicación (habitación, espacio, bodega) o a un Colaborador.
- **Disparador (Trigger):** Manual desde el detalle del activo o automático al crear/individualizar.
- **Flujo Paso a Paso:**
    1. El usuario selecciona un activo y lo asigna a una ubicación o colaborador.
    2. El sistema valida disponibilidad del activo.
    3. Crea un registro `ActivoAsignacion` con fecha de asignación.
    4. Marca asignaciones anteriores como inactivas.
    5. Dispara evento `ActivoAsignado`.

---

## 4. Submódulo / Funcionalidad: Mantenimientos de Activos

- **Descripción de la Pantalla / Vista:** Tabla de mantenimientos programados y ejecutados. Formulario con: tipo (preventivo/correctivo), fecha programada, fecha ejecución, costo, proveedor, observaciones, estado.
- **Disparador (Trigger):** Manual desde `Activos > Mantenimientos` o automático vía jobs programados.
- **Flujo Paso a Paso:**
    1. El usuario programa un mantenimiento para un activo.
    2. El sistema notifica mantenimientos próximos (job `NotificarMantenimientosJob`, diario 07:00).
    3. El sistema verifica mantenimientos vencidos (job `VerificarMantenimientosPreventivosJob`, diario 06:00).
    4. El usuario registra la ejecución del mantenimiento: fecha, costo, proveedor, observaciones.
    5. El sistema cambia el estado del activo a "En Mantenimiento" durante el proceso.
    6. Al completar, el sistema restaura el estado del activo (`SincronizarEstadoActivoJob`).
    7. Notificaciones vía `ProcesadorNotificacionesMantenimiento`: futuros, atrasados, críticos, prolongados.

---

## 5. Submódulo / Funcionalidad: Garantías de Activos

- **Descripción de la Pantalla / Vista:** Seguimiento de garantías por activo. Alertas de garantías próximas a vencer.
- **Disparador (Trigger):** Job `VerificarGarantiasJob` (diario 06:15).
- **Flujo Paso a Paso:**
    1. Al registrar un activo, se ingresa la fecha de fin de garantía.
    2. El job programado verifica garantías a vencer en los próximos 30 días.
    3. El sistema notifica al administrador sobre garantías próximas a expirar.
    4. El administrador puede gestionar reclamos de garantía desde el detalle del activo.

---

## 6. Submódulo / Funcionalidad: Depreciación de Activos

- **Descripción de la Pantalla / Vista:** Cálculo automático de depreciación lineal por activo. Visualización del valor en libros actual.
- **Disparador (Trigger):** Al consultar el detalle del activo.
- **Flujo Paso a Paso:**
    1. El sistema calcula la depreciación con `CalcularDepreciacionActivo`.
    2. Fórmula: (valor_adquisición - valor_residual) / vida_útil_meses.
    3. Muestra: valor original, depreciación acumulada, valor en libros actual.
    4. Todo el cálculo es stateless (BusinessLogic puro, sin efectos secundarios).

---

## 7. Submódulo / Funcionalidad: Bajas de Activos

- **Descripción de la Pantalla / Vista:** Proceso de dar de baja un activo (venta, pérdida, obsolescencia, robo). Cambia el estado del activo y libera la asignación.
- **Disparador (Trigger):** Manual desde `Activos > Bajas`.
- **Flujo Paso a Paso:**
    1. El usuario selecciona la opción de dar de baja un activo.
    2. El sistema despliega el formulario con: motivo de baja, fecha, valor residual.
    3. El usuario completa los campos obligatorios.
    4. ¿Se cumple con las validaciones del sistema?
        - Si es **No**, el sistema muestra una alerta de error (activo ya dado de baja, sin permisos).
        - Si es **Sí**, el sistema registra la baja, cambia el estado del activo a "Dado de Baja", libera la asignación activa y dispara el evento `ActivoDadoDeBaja`.

---

## Arquitectura del Módulo

```
app/
├── Repository/Models/Activos/
│   ├── Activo.php                          ← Modelo principal
│   ├── ActivoAsignacion.php                ← Asignación a ubicación/colaborador
│   ├── ActivoMantenimiento.php             ← Mantenimientos
│   └── ActivoBaja.php                      ← Bajas
├── BusinessLogic/Activos/
│   ├── ReglasIndividualizacion.php         ← Reglas de cantidad y estado
│   ├── GeneradorCodigoInventario.php       ← Genera TV-2026-0001
│   ├── GeneradorPrefijo.php                ← Prefijo por categoría
│   ├── CalcularDepreciacionActivo.php      ← Cálculo de depreciación
│   ├── ProcesadorNotificacionesMantenimiento.php ← Notificaciones
│   └── ProcesadorIndividualizacionCompra.php    ← Individualización desde compras
├── Interactors/Activos/
│   ├── ProcesadorIndividualizacionActivos.php   ← Creación individual
│   └── CreadorActivoConAsignacion.php          ← Creación con asignación
├── Jobs/Activos/
│   ├── VerificarMantenimientosPreventivosJob.php
│   ├── NotificarMantenimientosJob.php
│   ├── SincronizarEstadoActivoJob.php
│   └── VerificarGarantiasJob.php
└── Filament/Resources/Activos/
    ├── ActivoResource/                     ← CRUD activos
    ├── ActivoAsignacionResource/           ← Gestión de asignaciones
    ├── ActivoMantenimientoResource/        ← CRUD mantenimientos
    └── ActivoBajaResource/                 ← CRUD bajas
```
