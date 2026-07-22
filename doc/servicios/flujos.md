# Documentación de Flujos de Procesos: Módulo Servicios

## 1. Submódulo / Funcionalidad: Listado de Servicios (Admin)

- **Descripción de la Pantalla / Vista:** Tabla con todos los servicios registrados. Muestra imagen circular, código, nombre, categoría, estado (badge), ícono web (boolean), y fechas de creación. Filtros por estado, categoría, web y eliminados.
- **Disparador (Trigger):** Acceso desde `Servicios > Servicios` en el panel admin.
- **Flujo Paso a Paso:**
    1. El usuario accede a la interfaz de Servicios y visualiza la tabla paginada con todos los servicios.
    2. El sistema valida los permisos (Filament Shield) y carga los servicios con relaciones `categoria`, `imagenes`.
    3. El usuario puede filtrar por estado, categoría o visibilidad web.
    4. El usuario hace clic en el botón de acciones (⋮) para Ver o Editar un servicio.
    5. El sistema redirige a la página de detalle o edición.

---

## 2. Submódulo / Funcionalidad: Crear / Editar Servicio

- **Descripción de la Pantalla / Vista:** Formulario con secciones: Información General (código auto-generado, nombre, categoría, estado, web), Icono Representativo (selector de Heroicons), Descripción, Galería de Imágenes (upload múltiple con reordenamiento).
- **Disparador (Trigger):** Botón "Crear" en la lista de servicios o "Editar" en el menú de acciones.
- **Flujo Paso a Paso:**
    1. El usuario hace clic en "Crear Servicio".
    2. El sistema genera automáticamente el código `SRV-XXXX` mediante `GenerarCodigoServicio`.
    3. El usuario completa: nombre, categoría (select con filtro `CATEGORIA_SERVICIO`), estado, toggle web.
    4. El usuario selecciona un icono del listado de Heroicons disponibles.
    5. El usuario sube hasta 5 imágenes para la galería (FileUpload polimórfico).
    6. El usuario escribe la descripción del servicio.
    7. El usuario hace clic en "Crear".
    8. El sistema valida restricciones (código único, nombre requerido).
    9. ¿Se cumple con las validaciones?
        - Si es **No**, muestra errores en los campos correspondientes.
        - Si es **Sí**, crea el registro, sincroniza imágenes vía `SincronizarGaleriaImagenes`, y muestra notificación de éxito.

---

## 3. Submódulo / Funcionalidad: Portal Web de Servicios

- **Descripción de la Pantalla / Vista:** Página pública `/servicios` con hero banner, filtros por categoría (pills), grid de 3 columnas con cards de servicios. Cada card muestra: imagen (o logo del hotel), categoría, precio, nombre, descripción.
- **Disparador (Trigger):** Navegación del huésped al menú "Servicios" o clic en card desde el Home.
- **Flujo Paso a Paso:**
    1. El usuario navega a `/servicios` desde el header del sitio público.
    2. El sistema ejecuta `ObtenerServiciosLanding::ejecutar()` que consulta servicios con `web = true` y `estado = activo`.
    3. El sistema carga las categorías disponibles y renderiza la página con paginación (9 por página).
    4. El usuario hace clic en un filtro de categoría (ej: "Bienestar y Relajación").
    5. El sistema recarga vía Inertia con `?categoria=Bienestar+y+Relajación` y aplica `whereHas('categoria', nombre)`.
    6. La paginación mantiene el query param `categoria`.
    7. El usuario hace clic en "Consultar" en un card.
    8. El sistema redirige a `/contacto` para que el huésped solicite información.

---

## 4. Submódulo / Funcionalidad: Detalle de Servicio (Web)

- **Descripción de la Pantalla / Vista:** Página de detalle de un servicio individual con galería de imágenes, descripción completa, precio, políticas asociadas.
- **Disparador (Trigger):** Navegación directa a `/servicios/{slug}` o desde un enlace profundo.
- **Flujo Paso a Paso:**
    1. El usuario accede a la URL del servicio (ej: `/servicios/masaje-relajante-60-min-4`).
    2. El sistema ejecuta `ObtenerServicioDetalleLanding::ejecutar($slug)`.
    3. El Interactor resuelve el servicio por ID (extraído del slug) o por código, con relaciones `categoria`, `imagenes`, `precios.moneda`, `politicas`.
    4. El sistema normaliza URLs de imágenes (prefijo `/storage/`).
    5. Si no hay imágenes, asigna una por defecto según categoría (restaurante → service-kitchen.png, bar → service-bartender.png).
    6. El sistema formatea políticas en array para el frontend.
    7. Si el servicio no existe, retorna 404.
    8. El sistema renderiza la página `servicios/ServicioDetalle` con todos los datos.

---

## Arquitectura del Módulo

```
app/
├── Repository/Models/Servicios/
│   └── Servicio.php                         ← Modelo (tabla servicios)
├── Interactors/Landing/
│   ├── ObtenerServiciosLanding.php          ← Lista paginada para web
│   └── ObtenerServicioDetalleLanding.php    ← Detalle para web
├── Interactors/Servicios/
│   ├── GenerarCodigoServicio.php            ← Genera SRV-XXXX
│   └── SincronizarGaleriaImagenes.php       ← Sincroniza imágenes polimórficas
├── Filament/Resources/Servicios/Servicios/
│   ├── ServicioResource.php                 ← Resource Filament
│   ├── Schemas/ServicioForm.php             ← Formulario admin
│   └── Tables/ServiciosTable.php            ← Tabla admin
└── resources/js/modules/servicios/
    ├── pages/Servicios.tsx                  ← Página web de listado
    ├── pages/ServicioDetalle.tsx            ← Página web de detalle
    ├── components/SeccionServicios.tsx       ← Componente de grid + filtros
    └── types.ts                             ← Interfaces TypeScript
```
