# Documentación de Flujos de Procesos: Módulo Catálogos

## 1. Submódulo / Funcionalidad: Tipos de Catálogo

- **Descripción de la Pantalla / Vista:** Tabla con todos los tipos de catálogo registrados (Cargo, Departamento, Tipo Cliente, Categoría Producto, etc.). Cada fila muestra código, nombre y estado. Permite crear, editar y eliminar tipos.
- **Disparador (Trigger):** Acceso desde el menú lateral `Catálogos > Tipos de Catálogo`.
- **Flujo Paso a Paso:**
    1. El usuario accede a la interfaz de Tipos de Catálogo y visualiza la lista de tipos existentes con su código y nombre.
    2. El sistema valida los permisos (Filament Shield: `ViewAny:CatalogoTipo`) y muestra los datos disponibles.
    3. El usuario hace clic en "Crear" para registrar un nuevo tipo.
    4. El sistema muestra el formulario con los campos: código (único), nombre, estado (Activo/Inactivo).
    5. El usuario completa los campos y hace clic en "Crear".
    6. El sistema valida que el código no esté duplicado y crea el registro. Muestra notificación de éxito.

---

## 2. Submódulo / Funcionalidad: Gestión de Catálogos (Entradas)

- **Descripción de la Pantalla / Vista:** Tabla con todos los catálogos organizados por tipo. Incluye filtro por tipo de catálogo, búsqueda global, y columnas con código, nombre, tipo, estado.
- **Disparador (Trigger):** Acceso desde `Catálogos > Catálogos`. También desde la creación de servicios, habitaciones, promociones donde se requiere seleccionar una categoría.
- **Flujo Paso a Paso:**
    1. El usuario accede a la interfaz de Catálogos y visualiza la lista paginada.
    2. El sistema carga los catálogos agrupados por `catalogo_tipo_id` con filtro de estado.
    3. El usuario filtra por tipo usando el SelectFilter (ej: "Categoría de Servicio").
    4. El usuario hace clic en "Crear" para agregar una nueva entrada.
    5. El sistema despliega el formulario con: código, nombre, tipo de catálogo (FK), estado.
    6. El usuario completa los campos obligatorios.
    7. ¿Se cumple con las validaciones del sistema?
        - Si es **No**, el sistema muestra una alerta de error y solicita corregir los campos (código duplicado).
        - Si es **Sí**, el sistema registra la entrada y muestra notificación de éxito.

---

## 3. Submódulo / Funcionalidad: Tipos de Promoción

- **Descripción de la Pantalla / Vista:** Catálogos filtrados por tipo `TIPO_PROMOCION`. Muestra las entradas: Descuento por Temporada, Paquete/Combo, Estancia Prolongada, Reserva Anticipada, Evento Especial.
- **Disparador (Trigger):** Se visualiza al crear/editar una Promoción en el campo `tipo_promocion_id`.
- **Flujo Paso a Paso:**
    1. El usuario está en el formulario de creación de Promoción.
    2. El sistema carga las opciones del Select `tipo_promocion_id` consultando catálogos con `catalogo_tipo.codigo = 'TIPO_PROMOCION'`.
    3. El usuario selecciona el tipo deseado (ej: "Paquete / Combo").
    4. El sistema asigna el `catalogo_id` correspondiente a la promoción.

---

## 4. Submódulo / Funcionalidad: Categorías de Servicio y Habitación

- **Descripción de la Pantalla / Vista:** Catálogos filtrados por `CATEGORIA_SERVICIO` y `CATEGORIA_HABITACION` respectivamente.
- **Disparador (Trigger):** Creación/edición de Servicios y Habitaciones.
- **Flujo Paso a Paso:**
    1. El usuario crea un Servicio o Habitación desde su recurso Filament.
    2. El sistema precarga las categorías disponibles según el `CatalogoTipo` correspondiente.
    3. El usuario selecciona la categoría (ej: "Bienestar y Relajación" para servicio).
    4. El sistema asigna el `categoria_id` al modelo correspondiente (Servicio/Habitacion).

---

## 5. Submódulo / Funcionalidad: Unidades de Medida y Condiciones de Pago

- **Descripción de la Pantalla / Vista:** Catálogos planos para UNIDAD_MEDIDA (Unidad, Kilogramo, Litro, Caja, Paquete) y CONDICION_PAGO (Contado, 15/30/45/60/90 días).
- **Disparador (Trigger):** Utilizados en los módulos de Compras e Inventario al registrar productos, cotizaciones y órdenes de compra.
- **Flujo Paso a Paso:**
    1. El usuario registra un producto en el módulo de Inventario.
    2. El sistema precarga las unidades de medida desde el catálogo `UNIDAD_MEDIDA`.
    3. El usuario selecciona la unidad correspondiente al producto.
    4. Al crear una cotización, el sistema precarga las condiciones de pago desde `CONDICION_PAGO`.
    5. El usuario selecciona los términos financieros aplicables.

---

## Arquitectura del Módulo

```
app/
├── Enums/Catalogos/CatalogoTipo.php        ← Enum con todos los tipos de catálogo
├── Repository/Models/Catalogos/
│   ├── Catalogo.php                        ← Modelo principal (tabla catalogos)
│   └── CatalogoTipo.php                    ← Modelo del tipo (tabla catalogo_tipos)
├── Filament/Resources/
│   ├── Catalogos/Catalogo/                 ← Resource Filament para catalogos
│   └── Catalogos/CatalogoTipo/             ← Resource Filament para tipos
└── database/seeders/
    ├── CatalogoTipoSeeder.php              ← Siembra tipos predefinidos
    └── CatalogoSeeder.php                  ← Siembra entradas de catálogo
```
