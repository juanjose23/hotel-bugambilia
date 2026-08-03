<x-filament-panels::page>
    <div class="space-y-6">
        <x-restaurante.page-header
            icon="hugeicons-restaurant-01"
            titulo="Restaurante"
            subtitulo="Gestión de mesas"
        />

        {{-- Barra de filtros y ordenamiento --}}
        <div
            class="flex flex-col gap-3 rounded-2xl border
                   border-gray-200 bg-white p-4
                   shadow-sm dark:border-gray-800
                   dark:bg-gray-900
                   sm:flex-row sm:items-center sm:justify-between"
        >
            {{-- Busqueda --}}
            <div class="flex flex-1 items-center gap-3">
                <div class="relative flex-1 sm:max-w-xs">
                    <x-filament::icon
                        icon="heroicon-m-magnifying-glass"
                        class="absolute left-3 top-1/2 h-4 w-4
                               -translate-y-1/2 text-gray-400"
                    />

                    <input
                        type="text"
                        wire:model.live.debounce.300ms="filtroMesa"
                        placeholder="Buscar por nombre o ID…"
                        class="w-full rounded-xl border border-gray-300
                               bg-white py-2 pl-9 pr-3
                               text-xs font-bold text-gray-700
                               shadow-sm
                               transition-colors
                               placeholder:text-gray-400
                               focus:border-[#6b003e]/50
                               focus:outline-none focus:ring-2
                               focus:ring-[#6b003e]/20
                               dark:border-gray-700
                               dark:bg-gray-800
                               dark:text-gray-200
                               dark:placeholder:text-gray-500
                               dark:focus:border-[#e87faa]/50
                               dark:focus:ring-[#e87faa]/20"
                    />
                </div>

                {{-- Filtro por estado --}}
                <div class="flex items-center gap-2">
                    <x-filament::icon
                        icon="heroicon-m-funnel"
                        class="h-4 w-4 shrink-0 text-gray-400"
                    />

                    <select
                        wire:model.live="filtroEstado"
                        class="rounded-xl border border-gray-300
                               bg-white px-3 py-2
                               text-xs font-bold text-gray-700
                               shadow-sm
                               transition-colors
                               focus:border-[#6b003e]/50
                               focus:outline-none focus:ring-2
                               focus:ring-[#6b003e]/20
                               dark:border-gray-700
                               dark:bg-gray-800
                               dark:text-gray-200
                               dark:focus:border-[#e87faa]/50
                               dark:focus:ring-[#e87faa]/20"
                    >
                        <option value="">Todos los estados</option>
                        @foreach ($estadosMesa as $estado)
                            @if (is_object($estado))
                                <option
                                    value="{{ $estado->value }}"
                                    {{ ($filtroEstado ?? '') == (string) $estado->value ? 'selected' : '' }}
                                >
                                    {{ $estado->getLabel() }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Ordenar --}}
            <div class="flex items-center gap-2">
                <x-filament::icon
                    icon="heroicon-m-arrows-up-down"
                    class="h-4 w-4 shrink-0 text-gray-400"
                />

                <select
                    wire:model.live="ordenarPor"
                    class="rounded-xl border border-gray-300
                           bg-white px-3 py-2
                           text-xs font-bold text-gray-700
                           shadow-sm
                           transition-colors
                           focus:border-[#6b003e]/50
                           focus:outline-none focus:ring-2
                           focus:ring-[#6b003e]/20
                           dark:border-gray-700
                           dark:bg-gray-800
                           dark:text-gray-200
                           dark:focus:border-[#e87faa]/50
                           dark:focus:ring-[#e87faa]/20"
                >
                    <option value="nombre">Nombre (A-Z)</option>
                    <option value="estado">Estado</option>
                    <option value="capacidad">Capacidad</option>
                </select>

                {{-- Contador de resultados --}}
                <span
                    class="ml-2 text-[10px] font-black
                           uppercase tracking-wider
                           text-gray-400
                           dark:text-gray-500"
                >
                    {{ $mesasFiltradas->count() }}
                    {{ $mesasFiltradas->count() === 1 ? 'mesa' : 'mesas' }}
                </span>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════ --}}
        {{-- Toolbar de operaciones                     --}}
        {{-- ═══════════════════════════════════════════ --}}
        <div class="flex flex-wrap items-center gap-2">
            <x-filament::button
                wire:click="$dispatch('open-modal', { id: 'modal-unir-mesas' })"
                icon="heroicon-o-link"
                color="primary"
                size="sm"
            >
                Unir Mesas
            </x-filament::button>

            <x-filament::button
                wire:click="$dispatch('open-modal', { id: 'modal-mover-cuenta' })"
                icon="heroicon-o-arrows-right-left"
                color="warning"
                size="sm"
            >
                Mover Cuenta
            </x-filament::button>

            <x-filament::button
                wire:click="$dispatch('open-modal', { id: 'modal-descuento' })"
                icon="heroicon-o-currency-dollar"
                color="danger"
                size="sm"
            >
                Aplicar Descuento
            </x-filament::button>
        </div>

        {{-- Grid de mesas --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            @forelse ($mesasFiltradas as $mesa)
                @php
                    $estilo = $this->obtenerConfiguracionEstiloMesa(
                        $mesa->estado
                    );

                    $estadoVal = $mesa->estado instanceof BackedEnum
                        ? $mesa->estado->value
                        : (int) $mesa->estado;
                @endphp

                <x-restaurante.mesa-card
                    wire:key="mesa-card-{{ $mesa->id }}"
                    :mesa="$mesa"
                    :estilo="$estilo"
                    :estado-val="$estadoVal"
                    :estados="$estadosMesa"
                    :simbolo-moneda="$simboloMoneda"
                />
            @empty
                <div
                    class="col-span-full rounded-2xl border border-dashed
                           border-gray-300 p-8 text-center
                           dark:border-gray-700"
                >
                    <x-filament::icon
                        icon="hugeicons-restaurant-table"
                        class="mx-auto h-8 w-8 text-gray-400"
                    />

                    <p class="mt-2 text-sm font-bold text-gray-700 dark:text-gray-300">
                        No se encontraron mesas
                    </p>

                    @if ($filtroMesa !== '' || $filtroEstado !== null)
                        <p class="mt-1 text-xs text-gray-500">
                            Intenta cambiar los filtros de búsqueda.
                        </p>
                    @endif
                </div>
            @endforelse
        </div>
    </div>

    {{-- ═══════════════════════════════════════════ --}}
    {{-- Modal: Detalle del pedido                  --}}
    {{-- ═══════════════════════════════════════════ --}}
    <x-filament::modal
        id="modal-detalle-pedido"
        width="2xl"
        x-on:open-modal.window="
            if ($event.detail.id === 'modal-detalle-pedido') {
                $dispatch('filament-modal-open', { id: 'modal-detalle-pedido' })
            }
        "
    >
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-filament::icon
                    icon="heroicon-o-document-text"
                    class="h-5 w-5 text-gray-500"
                />
                Detalle de la comanda
            </div>
        </x-slot>

        @if ($pedidoDetalle)
            @php
                $comandasMostradas = $comandasDetalle->isNotEmpty()
                    ? $comandasDetalle
                    : collect([$pedidoDetalle]);
                $itemsMostrados = $comandasMostradas->flatMap(
                    fn ($comanda) => $comanda->items->map(
                        fn ($item) => ['comanda' => $comanda, 'item' => $item]
                    )
                );
                $totalComandas = $comandasMostradas->sum(
                    fn ($comanda) => $comanda->calcularSubtotal()
                );
            @endphp
            <div class="space-y-4">
                {{-- Encabezado del pedido --}}
                <div
                    class="flex flex-col gap-3 rounded-xl
                           border border-gray-200 bg-gray-50
                           p-4
                           dark:border-gray-700 dark:bg-gray-800
                           sm:flex-row sm:items-center
                           sm:justify-between"
                >
                    <div>
                        <p
                            class="text-lg font-black
                                   text-gray-950 dark:text-white"
                        >
                            {{ $comandasMostradas->count() === 1
                                ? $pedidoDetalle->codigo
                                : $comandasMostradas->count() . ' comandas activas'
                            }}
                        </p>

                        <p
                            class="mt-1 text-xs text-gray-500
                                   dark:text-gray-400"
                        >
                            Mesa {{ $pedidoDetalle->mesa->nombre ?? '—' }}
                            &middot;
                            Abierto
                            {{ $pedidoDetalle->abierto_en?->diffForHumans() ?? '—' }}
                        </p>
                    </div>

                    @if (is_object($pedidoDetalle->estado))
                        <x-filament::badge
                            :color="$pedidoDetalle->estado->getColor()"
                            :icon="$pedidoDetalle->estado->getIcon()"
                            size="sm"
                        >
                            {{ $pedidoDetalle->estado->getLabel() }}
                        </x-filament::badge>
                    @endif
                </div>

                {{-- Items --}}
                <div>
                    <p
                        class="mb-2 text-[10px] font-black
                               uppercase tracking-widest
                               text-gray-400 dark:text-gray-500"
                    >
                        Todos los productos solicitados ({{ $itemsMostrados->count() }})
                    </p>

                    <div class="overflow-x-auto rounded-xl border
                                border-gray-200
                                dark:border-gray-700">
                        <table class="min-w-[680px] w-full text-left text-xs">
                            <thead>
                                <tr
                                    class="border-b border-gray-200
                                           bg-gray-50
                                           dark:border-gray-700
                                           dark:bg-gray-800"
                                >
                                    <th
                                        class="px-3 py-2 font-black
                                               uppercase tracking-wider
                                               text-gray-500
                                               dark:text-gray-400"
                                    >
                                        Cant.
                                    </th>

                                    <th
                                        class="px-3 py-2 font-black
                                               uppercase tracking-wider
                                               text-gray-500
                                               dark:text-gray-400"
                                    >
                                        Comanda
                                    </th>

                                    <th
                                        class="px-3 py-2 font-black
                                               uppercase tracking-wider
                                               text-gray-500
                                               dark:text-gray-400"
                                    >
                                        Platillo
                                    </th>

                                    <th
                                        class="px-3 py-2 text-right font-black
                                               uppercase tracking-wider
                                               text-gray-500
                                               dark:text-gray-400"
                                    >
                                        P. unitario
                                    </th>

                                    <th
                                        class="px-3 py-2 font-black
                                               uppercase tracking-wider
                                               text-gray-500
                                               dark:text-gray-400"
                                    >
                                        Estado
                                    </th>

                                    <th
                                        class="px-3 py-2 text-right font-black
                                               uppercase tracking-wider
                                               text-gray-500
                                               dark:text-gray-400"
                                    >
                                        Subtotal
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="divide-y divide-gray-100
                                       dark:divide-gray-700/50"
                            >
                                @forelse ($itemsMostrados as $fila)
                                    @php
                                        $item = $fila['item'];
                                        $comanda = $fila['comanda'];
                                        $esAnulado = $item->estado
                                            === \App\Enums\Restaurante\EstadoItemPedido::ANULADO;
                                    @endphp
                                    <tr
                                        @class([
                                            'bg-white dark:bg-gray-900',
                                            'opacity-50 line-through' => $esAnulado,
                                        ])
                                    >
                                        <td
                                            class="px-3 py-2
                                                   font-bold
                                                   text-gray-700
                                                   dark:text-gray-300"
                                        >
                                            @if ($esAnulado)
                                                <span
                                                    class="inline-flex h-5 w-5
                                                           items-center justify-center
                                                           rounded bg-red-100
                                                           text-red-600
                                                           dark:bg-red-950/40
                                                           dark:text-red-400"
                                                >
                                                    <x-filament::icon
                                                        icon="heroicon-o-x-mark"
                                                        class="h-3 w-3"
                                                    />
                                                </span>
                                            @endif

                                            {{ (int) $item->cantidad }}
                                        </td>

                                        <td
                                            class="px-3 py-2 font-mono text-[10px]
                                                   font-bold text-gray-600
                                                   dark:text-gray-300"
                                        >
                                            {{ $comanda->codigo }}
                                        </td>

                                        <td
                                            class="px-3 py-2
                                                   font-bold
                                                   text-gray-900
                                                   dark:text-white"
                                        >
                                            {{ $item->plato->nombre ?? 'Platillo' }}

                                            @if ($item->observaciones)
                                                <p
                                                    class="mt-0.5 text-[10px]
                                                           font-normal
                                                           text-[#6b003e]
                                                           dark:text-[#e87faa]"
                                                >
                                                    {{ $item->observaciones }}
                                                </p>
                                            @elseif ($item->notas)
                                                <p
                                                    class="mt-0.5 text-[10px]
                                                           font-normal
                                                           text-gray-500
                                                           dark:text-gray-400"
                                                >
                                                    {{ $item->notas }}
                                                </p>
                                            @endif
                                        </td>

                                        <td
                                            class="px-3 py-2 text-right
                                                   font-semibold
                                                   text-gray-700
                                                   dark:text-gray-300"
                                        >
                                            {{ $simboloMoneda }}
                                            {{ number_format((float) $item->precio_unitario, 2, ',', '.') }}
                                        </td>

                                        <td class="px-3 py-2">
                                            @if (is_object($item->estado))
                                                <x-filament::badge
                                                    :color="$item->estado->getColor()"
                                                    size="xs"
                                                >
                                                    {{ $item->estado->getLabel() }}
                                                </x-filament::badge>
                                            @else
                                                <span class="text-gray-500">—</span>
                                            @endif
                                        </td>

                                        <td
                                            class="px-3 py-2 text-right
                                                   font-black
                                                   text-gray-700
                                                   dark:text-gray-200"
                                        >
                                            {{ $simboloMoneda }}
                                            {{ number_format((float) $item->subtotal, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td
                                            colspan="6"
                                            class="px-3 py-6 text-center
                                                   text-xs text-gray-400"
                                        >
                                            Sin items registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Total --}}
                <div
                    class="flex items-center justify-between
                           rounded-xl border
                           border-[#6b003e]/20
                           bg-[#6b003e]/5 p-4
                           dark:border-[#e87faa]/20
                           dark:bg-[#6b003e]/10"
                >
                    <p
                        class="text-sm font-black uppercase
                               text-[#6b003e]
                               dark:text-[#e87faa]"
                    >
                        Total
                    </p>

                    <p
                        class="text-lg font-black
                               text-[#6b003e]
                               dark:text-[#e87faa]"
                    >
                        {{ $simboloMoneda }}
                        {{ number_format((float) $totalComandas, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        @endif

        <x-slot name="footer">
            <div class="flex items-center justify-end gap-2">
                <x-filament::button
                    wire:click="cerrarDetallePedido"
                    color="gray"
                    size="sm"
                >
                    Cerrar
                </x-filament::button>

                @if (
                    $pedidoDetalle
                    && is_object($pedidoDetalle->estado)
                    && ! in_array($pedidoDetalle->estado, [
                        \App\Enums\Restaurante\EstadoPedido::PAGADO,
                        \App\Enums\Restaurante\EstadoPedido::CARGADO_A_HABITACION,
                        \App\Enums\Restaurante\EstadoPedido::CANCELADO,
                    ], true)
                )
                    <x-filament::button
                        wire:click="irACobrarDesdeDetalle"
                        icon="heroicon-o-banknotes"
                        color="success"
                        size="sm"
                    >
                        Cobrar
                    </x-filament::button>
                @endif
            </div>
        </x-slot>
    </x-filament::modal>

    {{-- ═══════════════════════════════════════════ --}}
    {{-- Modal: Unir Mesas                           --}}
    {{-- ═══════════════════════════════════════════ --}}
    <x-filament::modal
        id="modal-unir-mesas"
        width="lg"
        x-on:open-modal.window="
            if ($event.detail.id === 'modal-unir-mesas') {
                $dispatch('filament-modal-open', { id: 'modal-unir-mesas' })
            }
        "
    >
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-link" class="h-5 w-5 text-gray-500" />
                Unir Mesas
            </div>
        </x-slot>

        <div class="space-y-4">
            <div>
                <label class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">
                    Mesa Principal
                </label>
                <select
                    wire:model.live="mesaSeleccionadaId"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-700 shadow-sm focus:border-[#6b003e]/50 focus:outline-none focus:ring-2 focus:ring-[#6b003e]/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                >
                    <option value="">Seleccionar mesa principal...</option>
                    @foreach ($mesas as $mesa)
                        @php
                            $meta = is_array($mesa->meta_datos) ? $mesa->meta_datos : [];
                            $yaEsUnida = ! empty($meta['mesa_principal_id'] ?? null);
                        @endphp
                        @if (! $yaEsUnida)
                            <option value="{{ $mesa->id }}">
                                {{ $mesa->nombre }}
                                @if ($mesa->estado instanceof \App\Enums\HabitacionesEspacios\EstadoEspacio)
                                    — {{ $mesa->estado->getLabel() }}
                                @endif
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">
                    Mesas a Unir (Secundarias)
                </label>
                <div class="max-h-48 space-y-1 overflow-y-auto rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800">
                    @foreach ($mesas as $mesa)
                        @php
                            $meta = is_array($mesa->meta_datos) ? $mesa->meta_datos : [];
                            $yaEsUnida = ! empty($meta['mesa_principal_id'] ?? null) || ! empty($meta['mesas_unidas'] ?? null);
                        @endphp
                        @if ($mesa->id !== $mesaSeleccionadaId && ! $yaEsUnida)
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-white dark:hover:bg-gray-700">
                                <input
                                    type="checkbox"
                                    wire:model.live="mesasParaUnir"
                                    value="{{ $mesa->id }}"
                                    class="rounded border-gray-300 text-[#6b003e] focus:ring-[#6b003e]/30 dark:border-gray-600"
                                />
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">
                                    {{ $mesa->nombre }}
                                    @if ($mesa->estado instanceof \App\Enums\HabitacionesEspacios\EstadoEspacio)
                                        <span class="text-gray-400">— {{ $mesa->estado->getLabel() }}</span>
                                    @endif
                                </span>
                            </label>
                        @endif
                    @endforeach

                    @if ($mesas->isEmpty())
                        <p class="py-2 text-center text-xs text-gray-400">No hay mesas disponibles.</p>
                    @endif
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">
                    Motivo de Unión
                </label>
                <select
                    wire:model="motivoUnion"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-700 shadow-sm focus:border-[#6b003e]/50 focus:outline-none focus:ring-2 focus:ring-[#6b003e]/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                >
                    <option value="uso_inmediato">Uso Inmediato</option>
                    <option value="reserva_grupal">Reserva Grupal</option>
                    <option value="evento_especial">Evento Especial</option>
                </select>
            </div>

            @if ($motivoUnion === 'reserva_grupal')
                <div>
                    <label class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">
                        Reserva Asociada (Opcional)
                    </label>
                    <select
                        wire:model="reservaIdParaUnion"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-700 shadow-sm focus:border-[#6b003e]/50 focus:outline-none focus:ring-2 focus:ring-[#6b003e]/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    >
                        <option value="">Sin reserva...</option>
                        @foreach ($reservasRestaurante as $reserva)
                            <option value="{{ $reserva->id }}">
                                {{ $reserva->codigo ?? 'Reserva #'.$reserva->id }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <x-slot name="footer">
            <div class="flex items-center justify-end gap-2">
                <x-filament::button
                    x-on:click="$dispatch('filament-modal-close', { id: 'modal-unir-mesas' })"
                    color="gray"
                    size="sm"
                >
                    Cancelar
                </x-filament::button>

                <x-filament::button
                    wire:click="unirMesas"
                    wire:loading.attr="disabled"
                    color="primary"
                    size="sm"
                    icon="heroicon-o-link"
                >
                    <span wire:loading.remove wire:target="unirMesas">Confirmar Unión</span>
                    <span wire:loading wire:target="unirMesas">Uniendo...</span>
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>

    {{-- ═══════════════════════════════════════════ --}}
    {{-- Modal: Mover Cuenta                         --}}
    {{-- ═══════════════════════════════════════════ --}}
    <x-filament::modal
        id="modal-mover-cuenta"
        width="lg"
        x-on:open-modal.window="
            if ($event.detail.id === 'modal-mover-cuenta') {
                $dispatch('filament-modal-open', { id: 'modal-mover-cuenta' })
            }
        "
    >
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-arrows-right-left" class="h-5 w-5 text-gray-500" />
                Mover Cuenta entre Mesas
            </div>
        </x-slot>

        <div class="space-y-4">
            <div>
                <label class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">
                    Mesa Origen
                </label>
                <select
                    wire:model.live="mesaSeleccionadaId"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-700 shadow-sm focus:border-[#6b003e]/50 focus:outline-none focus:ring-2 focus:ring-[#6b003e]/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                >
                    <option value="">Seleccionar mesa origen...</option>
                    @foreach ($mesas as $mesa)
                        @php
                            $tienePedidos = $mesa->relationLoaded('pedidosActivos') && $mesa->pedidosActivos->isNotEmpty();
                        @endphp
                        @if ($tienePedidos)
                            <option value="{{ $mesa->id }}">{{ $mesa->nombre }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">
                    Mesa Destino
                </label>
                <select
                    wire:model.live="mesaDestinoId"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-700 shadow-sm focus:border-[#6b003e]/50 focus:outline-none focus:ring-2 focus:ring-[#6b003e]/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                >
                    <option value="">Seleccionar mesa destino...</option>
                    @foreach ($mesas as $mesa)
                        @if ($mesa->id !== $mesaSeleccionadaId)
                            <option value="{{ $mesa->id }}">{{ $mesa->nombre }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex items-center justify-end gap-2">
                <x-filament::button
                    x-on:click="$dispatch('filament-modal-close', { id: 'modal-mover-cuenta' })"
                    color="gray"
                    size="sm"
                >
                    Cancelar
                </x-filament::button>

                <x-filament::button
                    wire:click="moverCuentaMesa"
                    wire:loading.attr="disabled"
                    color="warning"
                    size="sm"
                    icon="heroicon-o-arrows-right-left"
                >
                    <span wire:loading.remove wire:target="moverCuentaMesa">Mover Cuenta</span>
                    <span wire:loading wire:target="moverCuentaMesa">Moviendo...</span>
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>

    {{-- ═══════════════════════════════════════════ --}}
    {{-- Modal: Aplicar Descuento                    --}}
    {{-- ═══════════════════════════════════════════ --}}
    <x-filament::modal
        id="modal-descuento"
        width="lg"
        x-on:open-modal.window="
            if ($event.detail.id === 'modal-descuento') {
                $dispatch('filament-modal-open', { id: 'modal-descuento' })
            }
        "
    >
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-currency-dollar" class="h-5 w-5 text-gray-500" />
                Aplicar Descuento
            </div>
        </x-slot>

        <div class="space-y-4">
            <div>
                <label class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">
                    Pedido / Comanda
                </label>
                <select
                    wire:model.live="pedidoDescuentoId"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-700 shadow-sm focus:border-[#6b003e]/50 focus:outline-none focus:ring-2 focus:ring-[#6b003e]/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                >
                    <option value="">Seleccionar pedido...</option>
                    @foreach ($mesas as $mesa)
                        @if ($mesa->relationLoaded('pedidosActivos') && $mesa->pedidosActivos->isNotEmpty())
                            @foreach ($mesa->pedidosActivos as $pedido)
                                @php
                                    $estadoVal = $pedido->estado instanceof \BackedEnum ? $pedido->estado->value : (int) $pedido->estado;
                                @endphp
                                @if (! in_array($estadoVal, [
                                    \App\Enums\Restaurante\EstadoPedido::PAGADO->value,
                                    \App\Enums\Restaurante\EstadoPedido::CARGADO_A_HABITACION->value,
                                    \App\Enums\Restaurante\EstadoPedido::CANCELADO->value,
                                ]))
                                    <option value="{{ $pedido->id }}">
                                        {{ $pedido->codigo }} — Mesa {{ $mesa->nombre }} ({{ $simboloMoneda }}{{ number_format((float) $pedido->subtotal, 2) }})
                                    </option>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">
                        Descuento (%)
                    </label>
                    <input
                        type="number"
                        wire:model.live="descuentoPorcentaje"
                        min="0"
                        max="100"
                        step="0.01"
                        placeholder="Ej. 10"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-700 shadow-sm focus:border-[#6b003e]/50 focus:outline-none focus:ring-2 focus:ring-[#6b003e]/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">
                        Descuento (Monto {{ $simboloMoneda }})
                    </label>
                    <input
                        type="number"
                        wire:model.live="descuentoMonto"
                        min="0"
                        step="0.01"
                        placeholder="Ej. 50.00"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-700 shadow-sm focus:border-[#6b003e]/50 focus:outline-none focus:ring-2 focus:ring-[#6b003e]/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    />
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">
                    Motivo del Descuento
                </label>
                <input
                    type="text"
                    wire:model="motivoDescuento"
                    placeholder="Ej. Promoción / Cortesía / Queja del cliente..."
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-700 shadow-sm focus:border-[#6b003e]/50 focus:outline-none focus:ring-2 focus:ring-[#6b003e]/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                />
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex items-center justify-end gap-2">
                <x-filament::button
                    x-on:click="$dispatch('filament-modal-close', { id: 'modal-descuento' })"
                    color="gray"
                    size="sm"
                >
                    Cancelar
                </x-filament::button>

                <x-filament::button
                    wire:click="aplicarDescuento"
                    wire:loading.attr="disabled"
                    color="danger"
                    size="sm"
                    icon="heroicon-o-currency-dollar"
                >
                    <span wire:loading.remove wire:target="aplicarDescuento">Aplicar Descuento</span>
                    <span wire:loading wire:target="aplicarDescuento">Aplicando...</span>
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>

    {{-- ═══════════════════════════════════════════ --}}
    {{-- Modal: Cobro / Pago (Acciones Filament)    --}}
    {{-- ═══════════════════════════════════════════ --}}
    <x-filament-actions::modals />
</x-filament-panels::page>
