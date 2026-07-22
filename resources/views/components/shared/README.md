# Shared Blade Components

## `x-shared.type-badge`

Badge con color según el tipo de área limpiable (Habitación, Espacio Común, Ubicación Física).

```blade
<x-shared.type-badge :type="$ejecucion->limpiable_type" />
```

| Prop   | Tipo     | Default | Descripción                                                         |
| ------ | -------- | ------- | ------------------------------------------------------------------- |
| `type` | `string` | `''`    | FQCN del modelo (`App\Models\Habitaciones\Habitacion::class`, etc.) |

---

## `x-shared.metric-card`

Tarjeta de métrica numérica con valor y label.

```blade
<x-shared.metric-card
    value="42"
    label="Insumos"
    color="bg-primary-50 dark:bg-primary-900/20"
    valueColor="text-primary-600 dark:text-primary-400"
/>
```

| Prop         | Tipo     | Default                              | Descripción                                 |
| ------------ | -------- | ------------------------------------ | ------------------------------------------- |
| `value`      | `string` | `''`                                 | Valor a mostrar (número o texto)            |
| `label`      | `string` | `''`                                 | Texto descriptivo debajo del valor          |
| `color`      | `string` | `'bg-gray-100 dark:bg-gray-800/50'`  | Clases Tailwind para el fondo de la tarjeta |
| `valueColor` | `string` | `'text-gray-500 dark:text-gray-400'` | Clases Tailwind para el color del valor     |

---

## `x-shared.tab-switcher`

Selector de tabs responsive: botonera horizontal en móvil, tabs de Filament en desktop.

```blade
<x-shared.tab-switcher
    activeTab="abastecer"
    :tabs="[
        ['id' => 'abastecer', 'label' => 'Abastecer', 'icon' => 'plus-circle', 'activeColor' => 'success'],
        ['id' => 'devolver', 'label' => 'Devolver', 'icon' => 'arrow-uturn-left', 'activeColor' => 'warning'],
        ['id' => 'traspasar', 'label' => 'Traspasar', 'icon' => 'arrows-right-left', 'activeColor' => 'info'],
    ]"
/>
```

| Prop                | Tipo     | Default      | Descripción                                                                                                                                                                     |
| ------------------- | -------- | ------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `activeTab`         | `string` | `''`         | ID del tab activo actual                                                                                                                                                        |
| `tabs`              | `array`  | `[]`         | Array de tabs. Cada item: `id` (string), `label` (string), `icon?` (string, nombre Heroicon sin prefijo), `activeColor?` (string, color Filament: primary/success/warning/info) |
| `label` (attribute) | `string` | `'Opciones'` | Label accesible para el grupo de tabs (se pasa como atributo)                                                                                                                   |

El contenido de cada tab se renderiza por separado en el padre usando `@if($activeTab === '...')`.

---

## `x-shared.movements-table`

Tabla de historial de movimientos de inventario.

```blade
<x-shared.movements-table :movimientos="$movimientos" />
```

| Prop          | Tipo                  | Default | Descripción                                          |
| ------------- | --------------------- | ------- | ---------------------------------------------------- |
| `movimientos` | `Collection` o `null` | `null`  | Colección de `App\Models\Inventario\MovimientoStock` |

El componente espera que cada `MovimientoStock` tenga:

- `created_at` (Carbon/datetime)
- `cantidad` (float/int)
- `tipo` (string)
- `producto->nombre` (string, nullable)
- `ubicacionOrigen->nombre` (string, nullable)
- `ubicacionDestino->nombre` (string, nullable)
- `costo_unitario` (float, nullable)
- `costo_total` (float, nullable)
- `usuario_nombre` (string, accessor)
