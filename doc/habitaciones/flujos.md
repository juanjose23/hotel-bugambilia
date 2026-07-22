# Documentación de Flujos de Procesos: Módulo Habitaciones

## 1. Submódulo / Funcionalidad: Portal Web de Habitaciones (Agrupado por Categoría)

- **Descripción de la Pantalla / Vista:** Página `/habitaciones` con hero banner, grid de cards agrupadas por tipo de habitación (Suite, Deluxe, Estándar, etc.). Cada card muestra: imagen, badge de disponibilidad ("3 disponibles", "Última disponible", "Agotado"), capacidad, precio desde, total de habitaciones de ese tipo.
- **Disparador (Trigger):** Navegación desde el header "Habitaciones" o desde el Home.
- **Flujo Paso a Paso:**
    1. El usuario navega a `/habitaciones`.
    2. El sistema ejecuta `ObtenerHabitacionesLanding::ejecutar()` que consulta todas las habitaciones activas y las **agrupa por `categoria_id`**.
    3. Por cada grupo calcula: `precio_desde` (mínimo), `disponibles`, `total`, `capacidad` (rango), `ids[]`, `imagen` (primera disponible).
    4. El sistema pagina los grupos (6 por página).
    5. Cada card muestra un badge de disponibilidad con color semántico:
        - **Verde**: "3 de 5 disponibles" (más de 3 disponibles)
        - **Ámbar**: "Última disponible" (solo 1)
        - **Rojo**: "Agotado" (0 disponibles)
    6. El usuario hace clic en "Ver disponibilidad" en un card.
    7. El sistema redirige al detalle de la primera habitación de ese tipo.

---

## 2. Submódulo / Funcionalidad: Detalle de Habitación (Web)

- **Descripción de la Pantalla / Vista:** Página individual de habitación con galería de imágenes, información detallada (capacidad, medidas, vistas, camas), servicios incluidos, políticas, equipamiento, habitaciones similares.
- **Disparador (Trigger):** Click en card de habitación o URL directa `/habitaciones/{slug}`.
- **Flujo Paso a Paso:**
    1. El usuario accede a `/habitaciones/suite-presidencial-3`.
    2. El sistema ejecuta `ObtenerHabitacionDetalleLanding::ejecutar($slug)`.
    3. Resuelve la habitación por ID del slug o por `codigo`/`slug`, con 8 relaciones eager-loading.
    4. Procesa imágenes (normaliza URLs), calcula capacidades (adultos + niños), resuelve servicios incluidos (de `servicioAsignaciones` con fallback hardcoded), formatea políticas (con fallback de 3 políticas por defecto), resuelve equipamiento (de `inventarioFijo`).
    5. Consulta habitaciones similares (misma categoría, excluyendo actual, top 3).
    6. Renderiza `habitaciones/HabitacionDetalle` con `room` + `similarRooms`.

---

## 3. Submódulo / Funcionalidad: Gestión de Habitaciones (Admin)

- **Descripción de la Pantalla / Vista:** Tabla Filament con columnas: imagen, código, número, nombre, categoría, estado, precio, ubicación. Filtros por estado, categoría, ubicación. Acciones CRUD.
- **Disparador (Trigger):** Acceso desde `Habitaciones > Habitaciones` en el panel admin.
- **Flujo Paso a Paso:**
    1. El administrador accede a la lista de habitaciones.
    2. El sistema muestra todas las habitaciones con softDeletes (incluye eliminadas vía filtro).
    3. El administrador crea una nueva habitación con: código, número, nombre, categoría, ubicación, detalle (capacidades, medidas, vistas), imágenes, precios, políticas.
    4. El sistema asigna políticas mediante `PoliticasRelationManager` (Attach/Detach).
    5. El sistema gestiona precios con `PreciosRelationManager` (morphMany).

---

## 4. Submódulo / Funcionalidad: Clonación de Habitación

- **Descripción de la Pantalla / Vista:** Acción desde la tabla de habitaciones que duplica una habitación completa con todas sus relaciones.
- **Disparador (Trigger):** Botón "Clonar" en la tabla de habitaciones.
- **Flujo Paso a Paso:**
    1. El administrador selecciona una habitación y hace clic en "Clonar".
    2. El sistema ejecuta `ServicioClonacionHabitacion` que en una transacción:
        - Genera nuevo código (`GenerarCodigoHabitacion`) y slug (`GenerarSlugHabitacion`).
        - Crea nueva Habitación con los mismos datos base.
        - Replica: DetalleHabitacion, ServicioAsignaciones, Precios, Políticas (sync), Stocks.
    3. El sistema muestra notificación de éxito con el código de la nueva habitación.

---

## Arquitectura del Módulo

```
app/
├── Repository/Models/Habitaciones/
│   ├── Habitacion.php                       ← Modelo principal
│   └── DetalleHabitacion.php                ← Detalles (capacidad, medidas, vistas)
├── Interactors/Landing/
│   ├── ObtenerHabitacionesLanding.php       ← Lista agrupada por categoría
│   └── ObtenerHabitacionDetalleLanding.php  ← Detalle individual
├── BusinessLogic/Habitaciones/
│   ├── ServicioAsignacionPacks.php          ← Asigna kits a habitaciones
│   └── ServicioClonacionHabitacion.php      ← Clona habitación con relaciones
└── resources/js/modules/habitaciones/
    ├── pages/Habitaciones.tsx               ← Página web
    ├── pages/HabitacionDetalle.tsx          ← Detalle web
    └── components/TarjetaHabitacion.tsx     ← Card con badge de disponibilidad
```
