<x-filament-panels::page>
    <div class="space-y-8">
        {{-- Leyenda de Estados con componente de Filament --}}
        <x-filament::section icon="heroicon-o-information-circle">
            <x-slot name="heading">
                Gestión de Mesas y Distribución Física
            </x-slot>
            <x-slot name="description">
                Plano de distribución interactivo de mesas. Seleccione el estado para actualizar la disponibilidad en tiempo real.
            </x-slot>

            <div class="flex flex-wrap items-center gap-6 mt-2">
                <span class="flex items-center gap-2">
                    <x-filament::badge color="success" size="sm">Disponible</x-filament::badge>
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold">Mesa libre para nuevos pedidos</span>
                </span>
                <span class="flex items-center gap-2">
                    <x-filament::badge color="warning" size="sm">Sucia / Limpieza</x-filament::badge>
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold">Requiere atención del personal de limpieza</span>
                </span>
                <span class="flex items-center gap-2">
                    <x-filament::badge color="info" size="sm">Reservada</x-filament::badge>
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold">Mesa apartada para una reservación</span>
                </span>
                <span class="flex items-center gap-2">
                    <x-filament::badge color="danger" size="sm">Ocupada</x-filament::badge>
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold">Clientes consumiendo actualmente</span>
                </span>
            </div>
        </x-filament::section>

        @if($mesas->isEmpty())
            <x-filament::section class="text-center py-12">
                <div class="flex flex-col items-center justify-center space-y-4">
                    <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-full">
                        <x-filament::icon icon="heroicon-o-table-cells" class="w-8 h-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white">No hay mesas configuradas</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm">
                        Por favor ejecute el seeder del restaurante para generar los ambientes y mesas demostrativas del hotel.
                    </p>
                </div>
            </x-filament::section>
        @else
            {{-- Mesas organizadas por ambiente usando componentes nativos de Filament --}}
            <div class="space-y-12">
                @php
                    // Agrupar mesas por zona / ubicación
                    $mesasPorZona = $mesas->groupBy(function($m) {
                        return $m->meta_datos['zona_restaurante'] ?? 'interior';
                    });
                @endphp

                @foreach($mesasPorZona as $zona => $listaMesas)
                    <x-filament::section collapsible>
                        <x-slot name="heading">
                            <span class="uppercase tracking-wider font-extrabold text-sm text-gray-900 dark:text-white">
                                ZONA: {{ $zona === 'interior' ? 'Salón Interior' : ($zona === 'terraza' ? 'Jardín & Terraza' : 'Bar / Barra') }}
                            </span>
                        </x-slot>
                        <x-slot name="description">
                            Contiene un total de {{ $listaMesas->count() }} mesas activas configuradas
                        </x-slot>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8 mt-6">
                            @foreach($listaMesas as $mesa)
                                @php
                                    $estadoVal = $mesa->estado->value;
                                    $estadoColor = match($estadoVal) {
                                        1 => 'success', // Disponible
                                        3, 6 => 'warning', // Sucio / Limpieza
                                        4 => 'info', // Reservado
                                        5 => 'danger', // Ocupado
                                        default => 'gray'
                                    };
                                    
                                    // Mapear color crudo de Tailwind para el croquis
                                    $rawTailwindColor = match($estadoVal) {
                                        1 => 'emerald',
                                        3, 6 => 'amber',
                                        4 => 'blue',
                                        5 => 'rose',
                                        default => 'gray'
                                    };
                                    
                                    $capacidad = (int) $mesa->capacidad_personas;
                                @endphp
                                
                                <div class="bg-gray-50 dark:bg-gray-900/50 border border-gray-200/60 dark:border-gray-800 rounded-3xl p-5 hover:shadow-lg transition-all duration-300 flex flex-col justify-between relative group">
                                    
                                    {{-- Titulo y Estado superior --}}
                                    <div class="flex items-center justify-between mb-4 border-b border-gray-200 dark:border-gray-800/40 pb-2">
                                        <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Mesa</span>
                                        <x-filament::badge color="{{ $estadoColor }}" size="sm">
                                            {{ $mesa->estado->getLabel() }}
                                        </x-filament::badge>
                                    </div>

                                    {{-- Croquis / Renderizado Físico de la Mesa --}}
                                    <div class="relative w-full h-32 flex items-center justify-center mb-4">
                                        
                                        {{-- Sillas alrededor de la mesa --}}
                                        @if($capacidad <= 2)
                                            {{-- 2 Sillas: Izquierda y Derecha --}}
                                            <span class="absolute left-6 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full shadow-sm bg-{{ $rawTailwindColor }}-500 border border-white dark:border-gray-950 transition-all duration-300"></span>
                                            <span class="absolute right-6 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full shadow-sm bg-{{ $rawTailwindColor }}-500 border border-white dark:border-gray-950 transition-all duration-300"></span>
                                        @elseif($capacidad > 2 && $capacidad <= 4)
                                            {{-- 4 Sillas: Cruz --}}
                                            <span class="absolute left-6 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full shadow-sm bg-{{ $rawTailwindColor }}-500 border border-white dark:border-gray-950 transition-all duration-300"></span>
                                            <span class="absolute right-6 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full shadow-sm bg-{{ $rawTailwindColor }}-500 border border-white dark:border-gray-950 transition-all duration-300"></span>
                                            <span class="absolute top-2 left-1/2 -translate-x-1/2 w-4 h-4 rounded-full shadow-sm bg-{{ $rawTailwindColor }}-500 border border-white dark:border-gray-950 transition-all duration-300"></span>
                                            <span class="absolute bottom-2 left-1/2 -translate-x-1/2 w-4 h-4 rounded-full shadow-sm bg-{{ $rawTailwindColor }}-500 border border-white dark:border-gray-950 transition-all duration-300"></span>
                                        @elseif($capacidad > 4 && $capacidad <= 6)
                                            {{-- 6 Sillas --}}
                                            <span class="absolute top-1 left-10 w-4 h-4 rounded-full shadow-sm bg-{{ $rawTailwindColor }}-500 border border-white dark:border-gray-950 transition-all duration-300"></span>
                                            <span class="absolute top-1 left-1/2 -translate-x-1/2 w-4 h-4 rounded-full shadow-sm bg-{{ $rawTailwindColor }}-500 border border-white dark:border-gray-950 transition-all duration-300"></span>
                                            <span class="absolute top-1 right-10 w-4 h-4 rounded-full shadow-sm bg-{{ $rawTailwindColor }}-500 border border-white dark:border-gray-950 transition-all duration-300"></span>
                                            <span class="absolute bottom-1 left-10 w-4 h-4 rounded-full shadow-sm bg-{{ $rawTailwindColor }}-500 border border-white dark:border-gray-950 transition-all duration-300"></span>
                                            <span class="absolute bottom-1 left-1/2 -translate-x-1/2 w-4 h-4 rounded-full shadow-sm bg-{{ $rawTailwindColor }}-500 border border-white dark:border-gray-950 transition-all duration-300"></span>
                                            <span class="absolute bottom-1 right-10 w-4 h-4 rounded-full shadow-sm bg-{{ $rawTailwindColor }}-500 border border-white dark:border-gray-950 transition-all duration-300"></span>
                                        @else
                                            {{-- 8 Sillas --}}
                                            <span class="absolute top-1 left-10 w-4 h-4 rounded-full shadow-sm bg-{{ $rawTailwindColor }}-500 border border-white dark:border-gray-950 transition-all duration-300"></span>
                                            <span class="absolute top-1 left-1/2 -translate-x-1/2 w-4 h-4 rounded-full shadow-sm bg-{{ $rawTailwindColor }}-500 border border-white dark:border-gray-950 transition-all duration-300"></span>
                                            <span class="absolute top-1 right-10 w-4 h-4 rounded-full shadow-sm bg-{{ $rawTailwindColor }}-500 border border-white dark:border-gray-950 transition-all duration-300"></span>
                                            <span class="absolute bottom-1 left-10 w-4 h-4 rounded-full shadow-sm bg-{{ $rawTailwindColor }}-500 border border-white dark:border-gray-950 transition-all duration-300"></span>
                                            <span class="absolute bottom-1 left-1/2 -translate-x-1/2 w-4 h-4 rounded-full shadow-sm bg-{{ $rawTailwindColor }}-500 border border-white dark:border-gray-950 transition-all duration-300"></span>
                                            <span class="absolute bottom-1 right-10 w-4 h-4 rounded-full shadow-sm bg-{{ $rawTailwindColor }}-500 border border-white dark:border-gray-950 transition-all duration-300"></span>
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full shadow-sm bg-{{ $rawTailwindColor }}-500 border border-white dark:border-gray-950 transition-all duration-300"></span>
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full shadow-sm bg-{{ $rawTailwindColor }}-500 border border-white dark:border-gray-950 transition-all duration-300"></span>
                                        @endif

                                        {{-- La Mesa Física en sí misma --}}
                                        @if($capacidad <= 4)
                                            {{-- Mesa Redonda --}}
                                            <div class="rounded-full w-20 h-20 bg-white dark:bg-gray-800 border-[5px] border-{{ $rawTailwindColor }}-500 flex flex-col items-center justify-center shadow-lg transition-transform group-hover:scale-105 duration-300 z-10">
                                                <span class="font-black text-gray-900 dark:text-white text-sm leading-none">{{ $mesa->nombre }}</span>
                                                <span class="text-[8px] text-gray-400 dark:text-gray-500 font-bold mt-1 uppercase">{{ $capacidad }} pax</span>
                                            </div>
                                        @else
                                            {{-- Mesa Rectangular --}}
                                            <div class="rounded-2xl w-28 h-16 bg-white dark:bg-gray-800 border-[5px] border-{{ $rawTailwindColor }}-500 flex flex-col items-center justify-center shadow-lg transition-transform group-hover:scale-105 duration-300 z-10">
                                                <span class="font-black text-gray-900 dark:text-white text-sm leading-none">{{ $mesa->nombre }}</span>
                                                <span class="text-[8px] text-gray-400 dark:text-gray-500 font-bold mt-1 uppercase">{{ $capacidad }} pax</span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Detalles de pedidos --}}
                                    <div class="mb-4">
                                        @if($mesa->pedido_abierto_id)
                                            <div class="bg-rose-500/5 dark:bg-rose-500/10 border border-rose-500/10 dark:border-rose-500/20 rounded-2xl p-3 text-center space-y-1">
                                                <span class="text-[9px] font-black text-rose-500 uppercase tracking-widest block">Orden Activa</span>
                                                <div class="flex items-center justify-between text-xs font-black">
                                                    <span class="text-gray-600 dark:text-gray-400">{{ $mesa->pedido_abierto_codigo }}</span>
                                                    <span class="text-rose-600 dark:text-rose-400 font-black">C$ {{ number_format((float)$mesa->pedido_abierto_total, 2) }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="py-3 text-center text-xs font-bold text-gray-400 dark:text-gray-500 italic bg-white dark:bg-gray-950 border border-dashed border-gray-200 dark:border-gray-800 rounded-2xl">
                                                Mesa Libre
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Acciones y Select de Cambio de Estado --}}
                                    <div class="flex items-center gap-2 border-t border-gray-200 dark:border-gray-800/40 pt-4">
                                        <select wire:change="cambiarEstadoMesa({{ $mesa->id }}, $event.target.value)" 
                                            class="flex-1 text-[11px] font-black py-2 px-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-amber-500 focus:border-amber-500 transition-all text-gray-800 dark:text-white cursor-pointer shadow-sm">
                                            <option value="1" {{ $mesa->estado->value === 1 ? 'selected' : '' }}>Disponible</option>
                                            <option value="6" {{ $mesa->estado->value === 6 ? 'selected' : '' }}>Sucia / Limpieza</option>
                                            <option value="4" {{ $mesa->estado->value === 4 ? 'selected' : '' }}>Reservada</option>
                                            <option value="5" {{ $mesa->estado->value === 5 ? 'selected' : '' }}>Ocupada</option>
                                        </select>
                                        
                                        @if($mesa->pedido_abierto_id)
                                            <x-filament::icon-button
                                                href="/admin/restaurante/pedidos/{{ $mesa->pedido_abierto_id }}/edit"
                                                tag="a"
                                                icon="heroicon-m-pencil-square"
                                                color="danger"
                                                tooltip="Editar Pedido"
                                            />
                                        @else
                                            <x-filament::icon-button
                                                href="/admin/restaurante/pedidos/create?mesa_id={{ $mesa->id }}"
                                                tag="a"
                                                icon="heroicon-m-plus"
                                                color="success"
                                                tooltip="Nuevo Pedido"
                                            />
                                        @endif
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </x-filament::section>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
