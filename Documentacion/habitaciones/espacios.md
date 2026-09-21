# Documentación de Espacios, Sub-espacios y Mobiliario Físico

## 1. Visión General y Modelo Conceptual

El submódulo de **Espacios** permite modelar cualquier área física del hotel, restaurante o instalaciones (salones, gimnasio, spa, terrazas, bares y mesas).

Soporta una **jerarquía multinivel recursiva** y una **vinculación directa con Activos Fijos (Mobiliario)** para garantizar que los recursos estén físicamente equipados antes de operar en el sistema (por ejemplo, al abrir comandas en el restaurante).

```
Hotel / Instalaciones
 └── Espacio Contenedor Principal (ej. Restaurante Bugambilias)
      ├── Sub-espacio / Zona A (ej. Salón Principal)
      │    ├── Mesa 01 ─── [Activos: AF-MESA-001 (Mesa 4P), AF-SILLA-001...004]
      │    └── Mesa 02 ─── [Activos: AF-MESA-002 (Mesa 2P), AF-SILLA-005...006]
      ├── Sub-espacio / Zona B (ej. Terraza Exterior)
      │    ├── Mesa T-01 ─── [Activos: AF-MESA-T01, AF-SOMBR-01, AF-SILLA-007...010]
      │    └── Mesa T-02 ─── [Activos: AF-MESA-T02, AF-SILLA-011...012]
      └── Sub-espacio / Zona C (ej. Barra Central)
           ├── Puesto 01 ─── [Activo: AF-TABURETE-01]
           └── Puesto 02 ─── [Activo: AF-TABURETE-02]
```

---

## 2. Tipos de Espacios (`TipoEspacio`)

| Tipo            | Clave Enum    | Es Contenedor | Requiere Activo para Operar | Descripción                                                             |
| --------------- | ------------- | :-----------: | :-------------------------: | ----------------------------------------------------------------------- |
| **Restaurante** | `RESTAURANTE` |      Sí       |             No              | Espacio principal de gastronomía con configuración de mesas y horarios. |
| **Ambiente**    | `AMBIENTE`    |      Sí       |             No              | Área o zona interior dentro de un restaurante (ej. Salón VIP).          |
| **Terraza**     | `TERRAZA`     |      Sí       |             No              | Área exterior al aire libre o techada.                                  |
| **Bar**         | `BAR`         |      Sí       |             No              | Área de coctelería o bebidas.                                           |
| **Mesa**        | `MESA`        |   No (Hoja)   |           **Sí**            | Punto de servicio individual en restaurante para comandas y clientes.   |
| **Salón**       | `SALON`       |      Sí       |             No              | Salón de eventos, conferencias o reuniones.                             |
| **Gimnasio**    | `GYM`         |      Sí       |             No              | Área de entrenamiento físico.                                           |
| **Spa**         | `SPA`         |      Sí       |             No              | Cabinas de masajes o tratamientos.                                      |
| **Piscina**     | `PISCINA`     |      Sí       |             No              | Zona acuática y camastros.                                              |
| **Cancha**      | `CANCHA`      |      No       |             No              | Instalaciones deportivas.                                               |
| **Otro**        | `OTRO`        |   Opcional    |             No              | Áreas comunes generales.                                                |

---

## 3. Jerarquía y Relaciones del Modelo (`Espacio`)

- **`padre_id` (BelongsTo `padre`):** Referencia al espacio superior que lo contiene.
- **`hijos` (HasMany):** Sub-espacios o mesas contenidos dentro de este espacio.
- **`inventarioFijo` (MorphMany `ActivoAsignacion`):** Activos físicos asignados con `fecha_fin IS NULL`.
- **`ubicacion_id` (BelongsTo `Ubicacion`):** Ubicación física en el organigrama del hotel (heredada automáticamente del padre si no se especifica).

---

## 4. Reglas de Negocio Clave

### A. Operatividad de Mesas basada en Activo Físico (`ValidarEspacioOperativoConActivo`)

- **Regla:** Ninguna mesa (`tipo === MESA`) puede recibir pedidos, abrir comandas o seleccionarse en el TPV de Restaurante si no cuenta con al menos un activo físico asignado en estado activo (`inventarioFijo`).
- **Comportamiento en UI (Mapa de Mesas):**
    - Muestra un badge rojo `Sin Mobiliario`.
    - Deshabilita el botón de apertura de comanda con un tooltip explicativo: _"Esta mesa no tiene mobiliario asignado en inventario. Asigne un activo antes de operar."_
- **Validación Backend:** Interactor `AbrirPedidoMesa` ejecuta `ValidarEspacioOperativoConActivo::validar($mesa)`.

### B. Capacidad Máxima de Mesas en Restaurante (`ValidarCapacidadMesasRestaurante`)

- Se define un límite en `meta_datos.capacidad_mesas` del Restaurante.
- Tanto `ConsultarCapacidadMesas` como `contarMesasEnRestaurante` cuentan recursivamente:
    1. Mesas hijas directas del restaurante.
    2. Mesas nietas ubicadas dentro de sub-espacios (Terrazas, Bares, Salones).
- Si se intenta crear una mesa superando el límite, Filament bloquea la acción con una notificación de advertencia.

---

## 5. Panel Administrativo en Filament

### SubEspaciosRelationManager (`RelationManager`)

Ubicado en `app/Filament/Resources/Habitaciones/EspacioResource/RelationManagers/SubEspaciosRelationManager.php`.

#### 1. Panel Lateral Deslizable (_Slide-Over_):

Reemplaza los modales grandes por un panel deslizable (`slideOver()`, ancho `2xl`) dividido en 4 pestañas:

1. **Datos Principales:** Nombre, tipo, código autogenerado opcional (`MESA-0001`, `TERR-0001`), capacidad de comensales, orden, estado y toggles web/reservas.
2. **Mobiliario / Activos:** Selector múltiple con búsqueda que filtra únicamente activos en estado `Activo` disponibles (sin asignación activa) o ya vinculados a este espacio.
3. **Configuración del Tipo:** Opciones compactas y condicionales (forma de mesa redonda/cuadrada/rectangular/barra; características de terrazas y salones).
4. **Descripción:** Notas adicionales.

#### 2. Sincronización Automática con Interactor:

Al guardar el formulario (crear o editar):

- Se invoca `SincronizarActivosEspacio::ejecutar()`.
- Activos seleccionados nuevos: Se asignan llamando a `AsignarActivo`.
- Activos desmarcados: Se cierra su asignación previa estableciendo `fecha_fin = now()` y estado `Cerrada`.

#### 3. Navegación Jerárquica Directa:

- La tabla de sub-espacios muestra:
    - **Sub-mesas / Hijos:** Contador de elementos dentro de la zona (ej. `4 elementos`).
    - **Mobiliario / Activos:** Muestra el activo principal con contador (ej. `AF-MESA-001 (+4)`), tooltip con desglose completo y alerta visual `Sin Mobiliario Asignado` en rojo si aplica.
    - **Botón "Administrar Mesas" (`↗`):** Abre directamente el sub-espacio (ej. Terraza) en una pestaña nueva para gestionar sus mesas hijas sin perder el contexto.

---

## 6. Arquitectura del Código

```text
app/
├── BusinessLogic/
│   ├── Espacios/
│   │   └── ValidarEspacioOperativoConActivo.php     ← Regla de negocio: mesa requiere activo físico
│   └── Restaurante/
│       └── Mesas/
│           └── ValidarCapacidadMesasRestaurante.php ← Regla de negocio: límite de capacidad
├── Interactors/
│   ├── Espacios/
│   │   ├── GenerarCodigoSubEspacio.php             ← Generador secuencial (MESA-0001, TERR-0001)
│   │   ├── SincronizarActivosEspacio.php           ← Sincronizador de asignaciones de activos
│   │   └── ValidarCapacidadMesas.php               ← Validación de capacidad antes de crear
│   └── Restaurante/
│       └── AbrirPedidoMesa.php                     ← Apertura de comanda con validación de activo
├── Repository/
│   ├── Models/
│   │   └── Espacios/
│   │       └── Espacio.php                         ← Modelo Eloquent (hijos, inventarioFijo, padre)
│   ├── Queries/
│   │   └── Espacios/
│   │       └── ConsultarCapacidadMesas.php         ← Consulta multinivel de mesas
│   └── Persistencia/
│       └── Restaurante/
│           └── RestauranteRepositorio.php          ← Métodos de acceso a datos y conteo multinivel
└── Filament/
    └── Resources/
        └── Habitaciones/
            └── EspacioResource/
                ├── EspacioResource.php
                └── RelationManagers/
                    └── SubEspaciosRelationManager.php ← Slide-Over, Tabs, columnas y navegación
```
