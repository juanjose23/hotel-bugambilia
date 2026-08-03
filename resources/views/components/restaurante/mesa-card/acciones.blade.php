@props([
    'mesa',
    'estados',
    'estadoActual',
    'estadoActualEnum',
    'tienePedidos',
])

@php
    $pedidoActivo = $tienePedidos ? $mesa->pedidosActivos->first() : null;
    $urlEditarPedido = $pedidoActivo !== null
        ? url('/admin/restaurante/pedidos/' . $pedidoActivo->id . '/edit')
        : null;
@endphp

<footer class="mt-auto space-y-2.5 border-t border-gray-200/80 pt-3 dark:border-gray-800/80">
    {{-- Selector de Estado Rápido --}}
    <div class="space-y-1.5">
        <div class="flex items-center justify-between gap-2">
            <span class="text-[9px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500">
                Estado de la mesa
            </span>

            <x-filament::dropdown placement="bottom-end" width="xs">
                <x-slot name="trigger">
                    <x-filament::icon-button
                        icon="heroicon-m-ellipsis-horizontal"
                        color="gray"
                        size="sm"
                        label="Más opciones de mesa"
                    />
                </x-slot>

                <x-filament::dropdown.list>
                    <x-filament::dropdown.list.item
                        icon="heroicon-o-arrow-path"
                        x-on:click="document.getElementById('mesa-state-{{ $mesa->id }}')?.click()"
                    >
                        Cambiar estado
                    </x-filament::dropdown.list.item>

                    @if ($urlEditarPedido)
                        <x-filament::dropdown.list.item
                            icon="heroicon-o-arrows-right-left"
                            :href="$urlEditarPedido"
                            tag="a"
                        >
                            Dividir cuenta
                        </x-filament::dropdown.list.item>

                        <x-filament::dropdown.list.item
                            icon="heroicon-o-chat-bubble-left-ellipsis"
                            :href="$urlEditarPedido"
                            tag="a"
                        >
                            Agregar notas
                        </x-filament::dropdown.list.item>
                    @endif

                    <x-filament::dropdown.list.item
                        icon="heroicon-o-arrow-right-circle"
                        x-on:click="$wire.set('mesaSeleccionadaId', {{ $mesa->id }}); $dispatch('open-modal', { id: 'modal-mover-cuenta' })"
                        :disabled="! $tienePedidos"
                    >
                        Mover de mesa
                    </x-filament::dropdown.list.item>
                </x-filament::dropdown.list>
            </x-filament::dropdown>
        </div>

        <x-filament::dropdown placement="bottom-start" width="sm">
            <x-slot name="trigger">
                <x-filament::button
                    id="mesa-state-{{ $mesa->id }}"
                    color="gray"
                    size="sm"
                    class="w-full justify-between"
                    icon="heroicon-m-chevron-down"
                    icon-alias="mesa-card-state-dropdown"
                    icon-position="after"
                >
                    <span class="flex items-center gap-1.5 truncate">
                        @if ($estadoActualEnum)
                            <x-filament::icon :icon="$estadoActualEnum->getIcon()" class="h-4 w-4 shrink-0" />
                            <span class="truncate">{{ $estadoActualEnum->getLabel() }}</span>
                        @else
                            <span>Cambiar Estado</span>
                        @endif
                    </span>
                </x-filament::button>
            </x-slot>

            <x-filament::dropdown.list>
                @foreach ($estados as $estado)
                    <x-filament::dropdown.list.item
                        :icon="$estado->getIcon()"
                        wire:key="mesa-{{ $mesa->id }}-estado-{{ $estado->value }}"
                        wire:click="cambiarEstadoMesa({{ $mesa->id }}, '{{ $estado->value }}')"
                        :disabled="$estadoActual === (int) $estado->value"
                    >
                        <span class="text-xs font-bold">{{ $estado->getLabel() }}</span>
                    </x-filament::dropdown.list.item>
                @endforeach
            </x-filament::dropdown.list>
        </x-filament::dropdown>
    </div>

    {{-- Acciones contextuales según estado usando Filament UI Buttons --}}
    @if ($estadoActual === 6 || $estadoActual === 3)
        {{-- En Limpieza / Pendiente Limpieza --}}
        <x-filament::button
            type="button"
            wire:click="marcarMesaLimpia({{ $mesa->id }})"
            color="warning"
            icon="heroicon-o-sparkles"
            class="w-full"
        >
            Marcar Mesa Limpia
        </x-filament::button>
    @elseif ($estadoActual === 4)
        {{-- Reservado --}}
        <div class="grid grid-cols-2 gap-2">
            <x-filament::button
                type="button"
                wire:click="confirmarLlegadaReserva({{ $mesa->id }})"
                color="primary"
                icon="heroicon-o-check-circle"
                size="sm"
            >
                Llegada
            </x-filament::button>

            <x-filament::button
                type="button"
                wire:click="cancelarReservaMesa({{ $mesa->id }})"
                wire:confirm="¿Está seguro de cancelar esta reservación y liberar la mesa?"
                color="gray"
                icon="heroicon-o-x-circle"
                size="sm"
            >
                Cancelar
            </x-filament::button>
        </div>
    @elseif ($estadoActual === 1)
        {{-- Disponible --}}
        <div class="grid grid-cols-2 gap-2">
            <x-filament::button
                tag="a"
                href="{{ url('/admin/restaurante/pedidos/create?mesa_id=' . $mesa->id) }}"
                color="primary"
                icon="heroicon-o-plus"
                size="sm"
            >
                Comanda
            </x-filament::button>

            <x-filament::button
                tag="a"
                href="{{ \App\Filament\Resources\Reservas\ReservaResource::getUrl('create', ['tipo_reserva' => 'restaurante', 'espacio_id' => $mesa->id]) }}"
                color="gray"
                icon="heroicon-o-calendar-days"
                size="sm"
            >
                Reservar
            </x-filament::button>
        </div>
    @elseif ($tienePedidos)
        {{-- Ocupado con pedidos --}}
        <div class="grid grid-cols-2 gap-2">
            <x-filament::button
                type="button"
                wire:click="iniciarCobroMesa({{ $mesa->id }})"
                wire:loading.attr="disabled"
                color="success"
                icon="heroicon-o-banknotes"
                size="sm"
            >
                <span wire:loading.remove wire:target="iniciarCobroMesa({{ $mesa->id }})">Cobrar</span>
                <span wire:loading wire:target="iniciarCobroMesa({{ $mesa->id }})">...</span>
            </x-filament::button>

            <x-filament::button
                tag="a"
                href="{{ url('/admin/restaurante/pedidos/create?mesa_id=' . $mesa->id) }}"
                color="gray"
                icon="heroicon-o-plus"
                size="sm"
            >
                Agregar
            </x-filament::button>
        </div>
    @else
        {{-- Otro Estado u Ocupado sin comanda --}}
        <x-filament::button
            tag="a"
            href="{{ url('/admin/restaurante/pedidos/create?mesa_id=' . $mesa->id) }}"
            color="primary"
            icon="heroicon-o-plus"
            class="w-full"
        >
            Nueva Comanda
        </x-filament::button>
    @endif
</footer>

