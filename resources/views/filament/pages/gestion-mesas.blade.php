<x-filament-panels::page>
    <div class="space-y-8 font-sans">
        {{-- Banner Principal y Control de Unión de Mesas --}}
        <x-filament::section icon="heroicon-o-table-cells">
            <x-slot name="heading">
                Gestión de Mesas & Distribución Física del Restaurante
            </x-slot>
            <x-slot name="description">
                Plano interactivo del salón interior, terraza y barra. Gestione comandas múltiples, cambie estados en tiempo real y agrupe mesas para reservaciones o uso inmediato.
            </x-slot>

            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-800 flex flex-wrap items-center justify-between gap-6">
                {{-- Leyenda de Estados Nivel Accesible --}}
                <div class="flex flex-wrap items-center gap-4 text-xs">
                    <span class="flex items-center gap-2">
                        <x-filament::badge color="success" size="sm">Disponible</x-filament::badge>
                        <span class="text-xs font-bold text-gray-600 dark:text-gray-300">Mesa Libre</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <x-filament::badge color="danger" size="sm">Ocupada</x-filament::badge>
                        <span class="text-xs font-bold text-gray-600 dark:text-gray-300">Consumiendo</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <x-filament::badge color="warning" size="sm">En Atención / Limpieza</x-filament::badge>
                        <span class="text-xs font-bold text-gray-600 dark:text-gray-300">En preparación</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <x-filament::badge color="info" size="sm">Reservada</x-filament::badge>
                        <span class="text-xs font-bold text-gray-600 dark:text-gray-300">Apartada</span>
                    </span>
                </div>

                {{-- Modal Desplegable de Unión de Mesas --}}
                <div x-data="{ open: false }" class="relative">
                    <x-filament::button 
                        @click="open = !open" 
                        color="gray" 
                        icon="heroicon-o-link" 
                        size="sm"
                        aria-expanded="open"
                        aria-label="Abrir modal para unir o agrupar mesas"
                    >
                        Unir / Agrupar Mesas
                    </x-filament::button>

                    <div 
                        x-show="open" 
                        @click.away="open = false" 
                        x-transition
                        class="absolute right-0 mt-2 w-96 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl shadow-2xl p-5 z-50 space-y-4"
                        role="dialog"
                        aria-label="Panel para agrupar mesas"
                    >
                        <h4 class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-wider">Unir Mesas del Salón</h4>
                        
                        <div>
                            <label for="select-motivo-union" class="text-[11px] font-bold text-gray-700 dark:text-gray-300 block mb-1">Motivo de Unión</label>
                            <x-filament::input.wrapper>
                                <x-filament::input.select 
                                    id="select-motivo-union"
                                    wire:model.live="motivoUnion" 
                                >
                                    <option value="uso_inmediato">Uso Inmediato (Servicio en vivo)</option>
                                    <option value="reservacion">Reservación Previa (Grupo)</option>
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>

                        @if($motivoUnion === 'reservacion')
                            <div>
                                <label for="select-reserva-union" class="text-[11px] font-bold text-gray-700 dark:text-gray-300 block mb-1">Seleccionar Reservación</label>
                                <x-filament::input.wrapper>
                                    <x-filament::input.select 
                                        id="select-reserva-union"
                                        wire:model="reservaIdParaUnion" 
                                    >
                                        <option value="">-- Seleccionar Reserva --</option>
                                        @foreach($reservasRestaurante as $r)
                                            <option value="{{ $r->id }}">Reserva #{{ $r->codigo_reserva }} - {{ $r->nombre_cliente }} ({{ $r->adultos }} pax)</option>
                                        @endforeach
                                    </x-filament::input.select>
                                </x-filament::input.wrapper>
                            </div>
                        @endif

                        <div>
                            <label for="select-mesa-principal" class="text-[11px] font-bold text-gray-700 dark:text-gray-300 block mb-1">Mesa Principal</label>
                            <x-filament::input.wrapper>
                                <x-filament::input.select 
                                    id="select-mesa-principal"
                                    wire:model.live="mesaSeleccionadaId" 
                                >
                                    <option value="">-- Seleccionar Mesa Principal --</option>
                                    @foreach($mesas as $m)
                                        <option value="{{ $m->id }}">{{ $m->nombre }} ({{ $m->capacidad_personas }} pax)</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>

                        @if($mesaSeleccionadaId)
                            <div>
                                <span class="text-[11px] font-bold text-gray-700 dark:text-gray-300 block mb-1">Mesas Secundarias a Anexar</span>
                                <div class="max-h-36 overflow-y-auto space-y-1.5 p-2.5 bg-gray-50 dark:bg-gray-800/80 rounded-xl border border-gray-200 dark:border-gray-700">
                                    @foreach($mesas->where('id', '!=', $mesaSeleccionadaId) as $mSec)
                                        <label class="flex items-center gap-2 text-xs font-semibold text-gray-800 dark:text-gray-200 cursor-pointer">
                                            <input 
                                                type="checkbox" 
                                                wire:model="mesasParaUnir" 
                                                value="{{ $mSec->id }}" 
                                                class="rounded text-amber-500 focus:ring-amber-500"
                                            />
                                            <span>{{ $mSec->nombre }} ({{ $mSec->capacidad_personas }} pax)</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <x-filament::button 
                                wire:click="unirMesas" 
                                color="warning" 
                                size="xs" 
                                class="w-full font-bold"
                                aria-label="Confirmar la unión de las mesas seleccionadas"
                            >
                                Confirmar Unión de Mesas
                            </x-filament::button>
                        @endif
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Contenido de Zonas y Mesas --}}
        @if($mesas->isEmpty())
            <x-filament::section class="text-center py-12">
                <div class="flex flex-col items-center justify-center space-y-4">
                    <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-full">
                        <x-filament::icon icon="heroicon-o-table-cells" class="w-8 h-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white">No hay mesas configuradas</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm">
                        Por favor ejecute el seeder del restaurante para generar las mesas demostrativas del hotel.
                    </p>
                </div>
            </x-filament::section>
        @else
            <div class="space-y-10">
                @php
                    $mesasPorZona = $mesas->groupBy(function($m) {
                        return $m->meta_datos['zona_restaurante'] ?? 'interior';
                    });
                @endphp

                @foreach($mesasPorZona as $zona => $listaMesas)
                    <x-filament::section collapsible>
                        <x-slot name="heading">
                            <span class="uppercase tracking-wider font-black text-sm text-gray-900 dark:text-white">
                                ZONA: {{ $zona === 'interior' ? 'Salón Interior' : ($zona === 'terraza' ? 'Jardín & Terraza' : 'Bar / Barra') }}
                            </span>
                        </x-slot>
                        <x-slot name="description">
                            Contiene {{ $listaMesas->count() }} mesas activas configuradas
                        </x-slot>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mt-4">
                            @foreach($listaMesas as $mesa)
                                @php
                                    $estadoVal = $mesa->estado->value;
                                    $estilo = $this->obtenerConfiguracionEstiloMesa($estadoVal);

                                    $capacidad = (int) $mesa->capacidad_personas;
                                    $meta = $mesa->meta_datos ?? [];
                                    $esSecundariaUnida = !empty($meta['mesa_principal_id']);
                                    $esPrincipalConUnidas = !empty($meta['mesas_unidas']);
                                    $motivo = $meta['motivo_union'] ?? 'uso_inmediato';
                                    $codigoReserva = $meta['codigo_reserva'] ?? null;
                                @endphp

                                <div 
                                    class="rounded-3xl p-5 border transition-all duration-300 flex flex-col justify-between relative group hover:shadow-xl {{ $estilo['borderCard'] }} {{ $esSecundariaUnida ? 'ring-2 ring-amber-400/40' : '' }}"
                                >
                                    {{-- Indicadores de Unión con Iconos Filament --}}
                                    @if($esSecundariaUnida)
                                        <div class="mb-2">
                                            <span class="inline-flex items-center gap-1.5 text-[10px] font-black text-amber-800 dark:text-amber-300 bg-amber-100 dark:bg-amber-900/60 px-2.5 py-0.5 rounded-full shadow-sm">
                                                <x-filament::icon icon="heroicon-o-link" class="w-3 h-3 text-amber-600 dark:text-amber-400" />
                                                <span>Unida a {{ $meta['mesa_principal_nombre'] ?? 'Mesa Principal' }}</span>
                                                @if($motivo === 'reservacion') <span>(Reserva {{ $codigoReserva ?? '' }})</span> @endif
                                                <button 
                                                    wire:click="separarMesas({{ $mesa->id }})" 
                                                    class="hover:text-red-500 font-bold ml-1 p-0.5" 
                                                    title="Desvincular mesa"
                                                    aria-label="Desvincular esta mesa"
                                                >
                                                    <x-filament::icon icon="heroicon-o-x-mark" class="w-3.5 h-3.5 text-red-500 hover:text-red-700" />
                                                </button>
                                            </span>
                                        </div>
                                    @elseif($esPrincipalConUnidas)
                                        <div class="mb-2">
                                            <span class="inline-flex items-center gap-1.5 text-[10px] font-black text-purple-800 dark:text-purple-300 bg-purple-100 dark:bg-purple-900/60 px-2.5 py-0.5 rounded-full shadow-sm">
                                                <x-filament::icon icon="heroicon-o-sparkles" class="w-3 h-3 text-purple-600 dark:text-purple-400" />
                                                <span>Mesa Principal ({{ count($meta['mesas_unidas']) }} anexadas)</span>
                                                @if($motivo === 'reservacion') <span>(Reserva {{ $codigoReserva ?? '' }})</span> @endif
                                                <button 
                                                    wire:click="separarMesas({{ $mesa->id }})" 
                                                    class="hover:text-red-500 font-bold ml-1 p-0.5" 
                                                    title="Desvincular todas las mesas anexas"
                                                    aria-label="Desvincular todas las mesas anexas"
                                                >
                                                    <x-filament::icon icon="heroicon-o-x-mark" class="w-3.5 h-3.5 text-red-500 hover:text-red-700" />
                                                </button>
                                            </span>
                                        </div>
                                    @endif

                                    {{-- Encabezado con Nombre y Badge de Estado --}}
                                    <div class="flex items-center justify-between mb-3 border-b border-gray-200/80 dark:border-gray-800/80 pb-2.5">
                                        <span class="text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                            MESA #{{ $mesa->id }}
                                        </span>
                                        <x-filament::badge color="{{ $estilo['colorBadge'] }}" size="sm">
                                            {{ $mesa->estado->getLabel() }}
                                        </x-filament::badge>
                                    </div>

                                    {{-- Croquis Físico Visual de la Mesa con Sillas Accesibles --}}
                                    <div class="relative w-full h-28 flex items-center justify-center my-2">
                                        {{-- Sillas alrededor de la Mesa --}}
                                        @if($capacidad <= 2)
                                            <span class="absolute left-5 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full shadow-md border-2 border-white dark:border-gray-900 {{ $estilo['bgDot'] }}" aria-hidden="true"></span>
                                            <span class="absolute right-5 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full shadow-md border-2 border-white dark:border-gray-900 {{ $estilo['bgDot'] }}" aria-hidden="true"></span>
                                        @else
                                            <span class="absolute left-5 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full shadow-md border-2 border-white dark:border-gray-900 {{ $estilo['bgDot'] }}" aria-hidden="true"></span>
                                            <span class="absolute right-5 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full shadow-md border-2 border-white dark:border-gray-900 {{ $estilo['bgDot'] }}" aria-hidden="true"></span>
                                            <span class="absolute top-1 left-1/2 -translate-x-1/2 w-4 h-4 rounded-full shadow-md border-2 border-white dark:border-gray-900 {{ $estilo['bgDot'] }}" aria-hidden="true"></span>
                                            <span class="absolute bottom-1 left-1/2 -translate-x-1/2 w-4 h-4 rounded-full shadow-md border-2 border-white dark:border-gray-900 {{ $estilo['bgDot'] }}" aria-hidden="true"></span>
                                        @endif

                                        {{-- Tablero de la Mesa --}}
                                        <div class="rounded-full w-20 h-20 bg-white dark:bg-gray-800 border-[4px] {{ $estilo['borderCircle'] }} flex flex-col items-center justify-center shadow-lg transition-transform group-hover:scale-105 duration-300 z-10">
                                            <span class="font-black text-gray-900 dark:text-white text-xs leading-none">{{ $mesa->nombre }}</span>
                                            <span class="text-[9px] text-gray-500 dark:text-gray-400 font-bold mt-1 uppercase">{{ $capacidad }} pax</span>
                                        </div>
                                    </div>

                                    {{-- Cuentas / Comandas Activas --}}
                                    <div class="my-3 space-y-2">
                                        @if($mesa->pedidosActivos->isNotEmpty())
                                            <div class="flex items-center justify-between text-[10px] font-black uppercase text-gray-500 dark:text-gray-400 tracking-wider">
                                                <span>Cuentas ({{ $mesa->cuentas_activas_count }})</span>
                                                <span class="text-rose-600 dark:text-rose-400 font-black">
                                                    Total: C$ {{ number_format((float)$mesa->total_mesa, 2) }}
                                                </span>
                                            </div>

                                            <div class="max-h-36 overflow-y-auto space-y-1.5 pr-1">
                                                @foreach($mesa->pedidosActivos as $pedidoActivo)
                                                    <div class="bg-white dark:bg-gray-800 border border-rose-500/30 rounded-2xl p-2.5 flex items-center justify-between text-xs shadow-sm">
                                                        <div>
                                                            <span class="font-black text-gray-900 dark:text-white block text-[11px]">{{ $pedidoActivo->codigo }}</span>
                                                            <x-filament::badge color="{{ $pedidoActivo->estado->getColor() }}" size="xs">
                                                                {{ $pedidoActivo->estado->getLabel() }}
                                                            </x-filament::badge>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <span class="font-black text-rose-600 dark:text-rose-400 text-xs">
                                                                C$ {{ number_format((float)$pedidoActivo->total, 2) }}
                                                            </span>
                                                            <a 
                                                                href="/admin/restaurante/pedidos/{{ $pedidoActivo->id }}/edit" 
                                                                class="p-1 rounded text-gray-400 hover:text-amber-500 transition-colors" 
                                                                title="Editar cuenta"
                                                                aria-label="Editar cuenta {{ $pedidoActivo->codigo }}"
                                                            >
                                                                <x-filament::icon icon="heroicon-o-pencil-square" class="w-4 h-4 text-gray-500 hover:text-amber-500" />
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="py-2 text-center text-xs font-bold text-gray-400 dark:text-gray-500 italic bg-white dark:bg-gray-950 border border-dashed border-gray-200 dark:border-gray-800 rounded-2xl">
                                                Sin cuentas activas
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Formulario Accesible de Estado y Botón de Nueva Cuenta con Filament Select Component --}}
                                    <div class="flex items-center gap-2 border-t border-gray-200/80 dark:border-gray-800/80 pt-3">
                                        <label for="select-estado-mesa-{{ $mesa->id }}" class="sr-only">
                                            Cambiar estado de {{ $mesa->nombre }}
                                        </label>
                                        
                                        <x-filament::input.wrapper size="sm" class="flex-1">
                                            <x-filament::input.select 
                                                id="select-estado-mesa-{{ $mesa->id }}"
                                                wire:change="cambiarEstadoMesa({{ $mesa->id }}, $event.target.value)" 
                                                aria-label="Seleccionar estado para {{ $mesa->nombre }}"
                                            >
                                                <option value="1" {{ $mesa->estado->value === 1 ? 'selected' : '' }}>Disponible</option>
                                                <option value="6" {{ $mesa->estado->value === 6 ? 'selected' : '' }}>Sucia / Limpieza</option>
                                                <option value="4" {{ $mesa->estado->value === 4 ? 'selected' : '' }}>Reservada</option>
                                                <option value="5" {{ $mesa->estado->value === 5 ? 'selected' : '' }}>Ocupada</option>
                                            </x-filament::input.select>
                                        </x-filament::input.wrapper>
                                        
                                        <x-filament::button 
                                            tag="a"
                                            href="/admin/restaurante/pedidos/create?mesa_id={{ $mesa->id }}"
                                            color="warning"
                                            size="xs"
                                            icon="heroicon-o-plus"
                                            aria-label="Aperturar otra cuenta en {{ $mesa->nombre }}"
                                        >
                                            Cuenta
                                        </x-filament::button>
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
