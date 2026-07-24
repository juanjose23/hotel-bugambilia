# Documentación de Flujos de Procesos: Módulo Promociones

## 1. Submódulo / Funcionalidad: Listado de Promociones (Admin)

- **Descripción de la Pantalla / Vista:** Tabla con todas las promociones. Columnas: imagen, código, nombre, tipo, fechas (inicio/fin), % descuento, estado (badge), web (boolean). Filtros por estado, tipo, web, eliminados.
- **Disparador (Trigger):** Acceso desde `Promociones > Promociones` en el panel admin.
- **Flujo Paso a Paso:**
    1. El administrador accede a la lista de promociones (nuevo grupo de navegación "Promociones").
    2. El sistema muestra todas las promociones con filtros de estado, tipo y visibilidad web.
    3. El administrador puede filtrar para ver solo promociones activas y visibles en web.

---

## 2. Submódulo / Funcionalidad: Crear / Editar Promoción

- **Descripción de la Pantalla / Vista:** Formulario con secciones: Datos (código auto-generado PROM-XXXX, nombre, tipo, estado, web, fechas, % descuento, monto descuento, orden), Contenido (descripción, términos y condiciones), Imagen (FileUpload polimórfico hasta 3 imágenes), Items del Paquete (Repeater con tipo + ítem + precio especial).
- **Disparador (Trigger):** Botón "Crear" en lista de promociones.
- **Flujo Paso a Paso:**
    1. El administrador hace clic en "Crear Promoción".
    2. El sistema genera código `PROM-XXXX` mediante `GenerarCodigoPromocion`.
    3. El administrador completa: nombre, tipo (select con filtro `TIPO_PROMOCION`), fechas de vigencia, descuento (% o monto), estado, web.
    4. **Items del Paquete:** El administrador agrega ítems mediante el Repeater:
        - Selecciona Tipo: `Servicio` o `Habitación`.
        - El sistema carga dinámicamente las opciones del ítem según el tipo seleccionado.
        - El administrador selecciona el ítem específico y opcionalmente un precio especial.
    5. El administrador sube imágenes para la galería.
    6. El sistema valida y crea la promoción con sus items (relación `HasMany` en `promocion_items` con `item_type`/`item_id` polimórfico).
    7. El sistema muestra notificación de éxito.

---

## 3. Submódulo / Funcionalidad: Relaciones de la Promoción

- **Descripción de la Pantalla / Vista:** Relation Managers en la página de edición: Stocks (inventario consumible asociado) y Políticas (políticas aplicables).
- **Disparador (Trigger):** Pestañas inferiores en la página de edición de promoción.
- **Flujo Paso a Paso:**
    1. El administrador está en la edición de una promoción.
    2. **StocksRelationManager:** Permite gestionar stock consumible asociado a la promoción (morphMany).
    3. **PoliticasRelationManager:** Permite asociar/desasociar políticas existentes (AttachAction/DetachAction).
    4. El sistema utiliza `Politica::promociones()` (relación inversa morphToMany agregada al modelo Politica).

---

## 4. Submódulo / Funcionalidad: Seeder de Promociones

- **Descripción de la Pantalla / Vista:** Seeders que crean 3 promociones demo con precios, items y datos realistas.
- **Disparador (Trigger):** `php artisan db:seed --class=PromocionSeeder` o `php artisan db:seed` (incluido en DatabaseSeeder).
- **Flujo Paso a Paso:**
    1. El sistema ejecuta el seeder.
    2. Crea 3 promociones:
        - **Escapada Romántica** (Paquete): Suite + Masaje + Jacuzzi + Cena, 20% OFF, C$5,300
        - **Estancia Prolongada 7x6** (Estancia): Deluxe + Tour gratis, 15% OFF
        - **Reserva Anticipada 15% OFF** (Temporada): descuento por anticipación
    3. Cada promoción incluye precios en NIO y USD (`es_oferta: true`), items polimórficos, y fechas de vigencia.

---

## Arquitectura del Módulo

```
app/
├── Repository/Models/Promociones/
│   ├── Promocion.php                        ← Modelo principal
│   └── PromocionItem.php                    ← Items del paquete (polimórfico)
├── Interactors/Promociones/
│   └── GenerarCodigoPromocion.php           ← Genera PROM-XXXX
├── Filament/Resources/Promociones/PromocionResource/
│   ├── PromocionResource.php                ← Resource con Stocks + Politicas RelationManagers
│   ├── Schemas/PromocionForm.php            ← Form con Repeater de items
│   ├── Schemas/PromocionInfolist.php        ← Vista de detalle
│   └── Tables/PromocionTable.php            ← Tabla con filtros
├── database/migrations/
│   ├── ..._create_promociones_table.php     ← Tabla promociones
│   └── ..._create_promocion_items_table.php ← Tabla promocion_items (polimórfica)
└── database/seeders/
    └── PromocionSeeder.php                  ← Datos demo
```
