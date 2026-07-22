# Limpieza Blade Components

## `x-limpieza.execution-card`

Card para el tablero Kanban de Limpieza. Muestra el título, tipo de área, y tiene slots para contenido y acciones.

```blade
<x-limpieza.execution-card
    accentColor="amber"
    titulo="Habitación 101"
    :tipo="$ejecucion->limpiable_type"
    :url="EjecucionResource::getUrl('view', ['record' => $e->id])"
>
    {{-- Slot por defecto: contenido del cuerpo (info, metadatos) --}}
    <div class="space-y-1.5 text-xs">
        ...
    </div>

    {{-- Slot opcional: footer con botón de acción --}}
    <x-slot:footer>
        <button>Iniciar Limpieza</button>
    </x-slot:footer>
</x-limpieza.execution-card>
```

| Prop          | Tipo          | Default  | Descripción                                                         |
| ------------- | ------------- | -------- | ------------------------------------------------------------------- |
| `accentColor` | `string`      | `'gray'` | Color de la barra lateral. Valores: `amber`, `blue`, `green`, `red` |
| `titulo`      | `string`      | `''`     | Nombre del área a limpiar                                           |
| `tipo`        | `string`      | `''`     | FQCN del modelo limpiable (para `x-shared.type-badge`)              |
| `url`         | `string`      | `''`     | URL del enlace del título (recurso de ejecución)                    |
| `ejecucionId` | `int`\|`null` | `null`   | ID de la ejecución (por si se necesita en slots)                    |

| Slot                | Descripción                                       |
| ------------------- | ------------------------------------------------- |
| `default` (`$slot`) | Contenido del cuerpo de la card (metadatos, info) |
| `footer`            | Área de acciones (botones) debajo del contenido   |
