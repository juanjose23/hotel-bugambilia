<x-filament-panels::page>
    <div class="space-y-6 font-sans select-none">

        {{-- Encabezado Limpio Kiosko --}}
        <x-restaurante.page-header
            icon="heroicon-o-shopping-bag"
            titulo="Kiosko"
            subtitulo="Seleccione sus platillos preferidos y envíe la orden directo a cocina"
        >
            <x-slot name="actions">
                <x-restaurante.estado-badge estado="disponible" />
            </x-slot>
        </x-restaurante.page-header>

        {{-- Contenido Principal: Menú + Resumen Carrito --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- Columna Izquierda: Menú Digital (8 cols) --}}
            <div class="lg:col-span-8 space-y-5">

                {{-- Filtro de Categorías --}}
                <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                    <x-filament::button
                        wire:click="$set('categoriaSeleccionadaId', null)"

                        color="{{ $categoriaSeleccionadaId === null ? 'primary' : 'gray' }}"
                        size="sm"
                    >
                        Todos los Platillos
                    </x-filament::button>

                    @foreach($this->categorias as $catId => $catNombre)
                        <x-filament::button
                            wire:click="$set('categoriaSeleccionadaId', {{ $catId }})"
                            color="{{ $categoriaSeleccionadaId === $catId ? 'primary' : 'gray' }}"
                            size="sm"
                        >
                            {{ $catNombre }}
                        </x-filament::button>
                    @endforeach
                </div>

                {{-- Grid de Platillos --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                    @forelse($this->platos as $plato)
                        @php
                            $precioObj = $plato->precios()->latest()->first();
                            $precio = $precioObj ? (float) $precioObj->precio : 0.0;
                            $imagen = $plato->imagenes()->first()?->url;
                        @endphp

                        <x-filament::section class="flex flex-col justify-between group h-full">
                            <div class="space-y-3">
                                {{-- Imagen --}}
                                <div class="h-36 w-full bg-gray-100 dark:bg-gray-800 rounded-xl relative overflow-hidden flex items-center justify-center">
                                    @if($imagen)
                                        <img src="{{ asset('storage/' . $imagen) }}" alt="{{ $plato->nombre }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                    @else
                                        <x-filament::icon icon="heroicon-o-cake" class="w-10 h-10 text-gray-400 dark:text-gray-600" />
                                    @endif

                                    <div class="absolute top-2.5 right-2.5">
                                        <x-filament::badge color="primary">
                                            C$ {{ number_format($precio, 2) }}
                                        </x-filament::badge>
                                    </div>
                                </div>

                                {{-- Texto --}}
                                <div>
                                    <span class="text-[10px] font-bold text-primary-600 dark:text-primary-400 uppercase tracking-wide">
                                        {{ $plato->categoria?->nombre ?? 'Menú' }}
                                    </span>
                                    <h3 class="font-bold text-sm text-gray-900 dark:text-white leading-snug mt-0.5">
                                        {{ $plato->nombre }}
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                                        {{ $plato->descripcion ?? 'Preparación especial en cocina del hotel.' }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4">
                                <x-filament::button
                                    wire:click="agregarAlCarrito({{ $plato->id }})"
                                    color="primary"
                                    icon="heroicon-o-plus"
                                    size="sm"
                                    class="w-full"
                                >
                                    Agregar al Pedido
                                </x-filament::button>
                            </div>
                        </x-filament::section>
                    @empty
                        <div class="col-span-full py-12 text-center bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 text-gray-500">
                            <p class="text-xs font-semibold">Sin platillos disponibles en esta categoría.</p>
                        </div>
                    @endforelse
                </div>

            </div>

            {{-- Columna Derecha: Mi Orden (4 cols) --}}
            <div class="lg:col-span-4 sticky top-6">
                <x-filament::section>
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800 mb-4">
                        <div class="flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-shopping-cart" class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                            <h3 class="font-bold text-base text-gray-900 dark:text-white">Mi Comanda</h3>
                        </div>
                        @if(count($this->carrito) > 0)
                            <x-filament::button
                                wire:click="vaciarCarrito"
                                color="danger"
                                size="xs"
                                variant="tertiary"
                            >
                                Vaciar
                            </x-filament::button>
                        @endif
                    </div>

                    {{-- Selección Opcional de Mesa --}}
                    <div class="space-y-1 mb-4">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Asignar Mesa (Opcional)</label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model="mesaId">
                                <option value="">-- Sin mesa por el momento (Para Llevar / Libre) --</option>
                                @foreach($this->mesas as $mesaItem)
                                    <option value="{{ $mesaItem->id }}">{{ $mesaItem->nombre }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    {{-- Lista de Items --}}
                    <div class="space-y-2.5 max-h-87.5 overflow-y-auto pr-1 mb-4">
                        @forelse($this->carrito as $pId => $item)
                            <div class="bg-gray-50 dark:bg-gray-950 p-3 rounded-xl border border-gray-200/60 dark:border-gray-800/60 space-y-2">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="font-bold text-xs text-gray-900 dark:text-white leading-tight">
                                        {{ $item['nombre'] }}
                                    </span>
                                    <span class="font-bold text-xs text-primary-600 dark:text-primary-400 shrink-0">
                                        C$ {{ number_format($item['precio'] * $item['cantidad'], 2) }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between pt-1">
                                    <div class="flex items-center gap-1.5">
                                        <x-filament::button
                                            wire:click="cambiarCantidad({{ $pId }}, -1)"
                                            color="gray"
                                            size="xs"
                                        >
                                            -
                                        </x-filament::button>
                                        <span class="text-xs font-bold text-gray-900 dark:text-white px-2">{{ $item['cantidad'] }}</span>
                                        <x-filament::button
                                            wire:click="cambiarCantidad({{ $pId }}, 1)"
                                            color="gray"
                                            size="xs"
                                        >
                                            +
                                        </x-filament::button>
                                    </div>

                                    <x-filament::icon-button
                                        wire:click="eliminarDelCarrito({{ $pId }})"
                                        icon="heroicon-o-trash"
                                        color="danger"
                                        size="sm"
                                        label="Eliminar item"
                                    />
                                </div>

                                <x-filament::input.wrapper>
                                    <x-filament::input
                                        type="text"
                                        wire:model.live="carrito.{{ $pId }}.observaciones"
                                        placeholder="Especificación (ej. sin cebolla)..."
                                    />
                                </x-filament::input.wrapper>
                            </div>
                        @empty
                            <div class="py-10 text-center text-gray-400 space-y-1">
                                <p class="text-xs font-semibold">No ha agregado platillos a su comanda.</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Totales y Botón de Enviar --}}
                    @if(count($this->carrito) > 0)
                        <div class="border-t border-gray-100 dark:border-gray-800 pt-3 space-y-3">
                            <div class="flex justify-between text-sm font-bold text-gray-900 dark:text-white">
                                <span>TOTAL</span>
                                <span class="text-primary-600 dark:text-primary-400 text-base">C$ {{ number_format($this->calcularSubtotal(), 2) }}</span>
                            </div>

                            <x-filament::button
                                wire:click="confirmarYEnviarPedido"
                                wire:loading.attr="disabled"
                                color="primary"
                                icon="heroicon-o-paper-airplane"
                                size="lg"
                                class="w-full"
                            >
                                <span wire:loading.remove>Enviar Comanda a Cocina</span>
                                <span wire:loading>Enviando orden...</span>
                            </x-filament::button>
                        </div>
                    @endif
                </x-filament::section>
            </div>

        </div>

    </div>

    {{-- Script de Alerta Audio Confirmación --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('pedido-confirmado-audio', () => {
                try {
                    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();

                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(523.25, audioCtx.currentTime);
                    osc.frequency.setValueAtTime(659.25, audioCtx.currentTime + 0.12);
                    osc.frequency.setValueAtTime(783.99, audioCtx.currentTime + 0.24);

                    gain.gain.setValueAtTime(0.4, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.6);

                    osc.connect(gain);
                    gain.connect(audioCtx.destination);

                    osc.start();
                    osc.stop(audioCtx.currentTime + 0.6);
                } catch (e) {
                    console.log('Audio prevenido por navegador', e);
                }
            });
        });
    </script>
</x-filament-panels::page>
