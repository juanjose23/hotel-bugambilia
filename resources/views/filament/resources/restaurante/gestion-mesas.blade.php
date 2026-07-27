<x-filament-panels::page>
    <div 
        x-data="{
            handleKeydown(e) {
                const key = e.key;
                if (['F2','F3','F4','F5','F6','F7','F8','F9'].includes(key)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                if (key === 'F2') { window.location.href = '/admin/restaurante/pedidos/create'; }
                if (key === 'F3') { window.location.href = '/admin/personas'; }
                if (key === 'F4') { $dispatch('open-modal', { id: 'modal-mover-mesa' }); }
                if (key === 'F5') { $dispatch('open-modal', { id: 'modal-reimprimir-comanda' }); }
                if (key === 'F6') { window.location.href = '/admin/restaurante/pedidos'; }
                if (key === 'F8') { $dispatch('open-modal', { id: 'modal-descuento' }); }
                if (e.ctrlKey && key.toLowerCase() === 'n') {
                    e.preventDefault();
                    window.location.href = '/admin/restaurante/pedidos/create';
                }
            }
        }" 
        @keydown.window="handleKeydown($event)"
        class="space-y-8 font-sans select-none"
    >
        {{-- Barra de Atajos de Teclado POS Corporativa Alineada --}}
        <div class="bg-slate-950 text-slate-100 p-4 rounded-3xl border border-slate-800 flex flex-wrap items-center justify-between gap-4 shadow-xl">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-[#6b003e] animate-pulse"></span>
                <span class="font-black text-xs uppercase tracking-widest text-slate-300">Teclado POS Operativo</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2 text-xs font-mono">
                <a href="/admin/restaurante/pedidos/create" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 rounded-xl border border-slate-700 font-bold text-[#e87faa] flex items-center justify-between gap-1 shadow-sm transition-all">
                    <span class="bg-[rgba(107,0,62,0.3)] text-[#e87faa] px-1.5 py-0.5 rounded text-[10px]">F2</span>
                    <span class="text-slate-200 text-[11px] font-normal">Platillos</span>
                </a>
                <a href="/admin/personas" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 rounded-xl border border-slate-700 font-bold text-[#e87faa] flex items-center justify-between gap-1 shadow-sm transition-all">
                    <span class="bg-[rgba(107,0,62,0.3)] text-[#e87faa] px-1.5 py-0.5 rounded text-[10px]">F3</span>
                    <span class="text-slate-200 text-[11px] font-normal">Cliente</span>
                </a>
                <button @click="$dispatch('open-modal', { id: 'modal-mover-mesa' })" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 rounded-xl border border-slate-700 font-bold text-[#e87faa] flex items-center justify-between gap-1 shadow-sm transition-all cursor-pointer">
                    <span class="bg-[rgba(107,0,62,0.3)] text-[#e87faa] px-1.5 py-0.5 rounded text-[10px]">F4</span>
                    <span class="text-slate-200 text-[11px] font-normal">Mover</span>
                </button>
                <button @click="$dispatch('open-modal', { id: 'modal-reimprimir-comanda' })" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 rounded-xl border border-slate-700 font-bold text-[#e87faa] flex items-center justify-between gap-1 shadow-sm transition-all cursor-pointer">
                    <span class="bg-[rgba(107,0,62,0.3)] text-[#e87faa] px-1.5 py-0.5 rounded text-[10px]">F5</span>
                    <span class="text-slate-200 text-[11px] font-normal">Comanda</span>
                </button>
                <a href="/admin/restaurante/pedidos" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 rounded-xl border border-slate-700 font-bold text-[#e87faa] flex items-center justify-between gap-1 shadow-sm transition-all">
                    <span class="bg-[rgba(107,0,62,0.3)] text-[#e87faa] px-1.5 py-0.5 rounded text-[10px]">F6</span>
                    <span class="text-slate-200 text-[11px] font-normal">Cobrar</span>
                </a>
                <button @click="$dispatch('open-modal', { id: 'modal-descuento' })" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 rounded-xl border border-slate-700 font-bold text-[#e87faa] flex items-center justify-between gap-1 shadow-sm transition-all cursor-pointer">
                    <span class="bg-[rgba(107,0,62,0.3)] text-[#e87faa] px-1.5 py-0.5 rounded text-[10px]">F8</span>
                    <span class="text-slate-200 text-[11px] font-normal">Descuento</span>
                </button>
                <a href="/admin/restaurante/pedidos/create" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 rounded-xl border border-slate-700 font-bold text-slate-300 flex items-center justify-between gap-1 shadow-sm transition-all">
                    <span class="bg-slate-800 text-slate-300 px-1.5 py-0.5 rounded text-[10px]">Ctrl+N</span>
                    <span class="text-slate-400 text-[11px] font-normal">Nuevo</span>
                </a>
            </div>
        </div>

        {{-- Banner Principal y Control de Unión de Mesas --}}
        <x-filament::section icon="heroicon-o-table-cells">
            <x-slot name="heading">
                Gestión de Mesas & Distribución Física del Restaurante
            </x-slot>
            <x-slot name="description">
                Plano interactivo del salón interior, terraza y barra. Gestione comandas múltiples, cambie estados en tiempo real y agrupe mesas para reservaciones o uso inmediato.
            </x-slot>

            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-800 flex flex-wrap items-center justify-between gap-6">
                {{-- Leyenda de Estados Sobria (Sin Emojis) --}}
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
                        <x-filament::badge color="warning" size="sm">Pendiente Limpieza</x-filament::badge>
                        <span class="text-xs font-bold text-gray-600 dark:text-gray-300">En desinfección</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <x-filament::badge color="info" size="sm">Reservada</x-filament::badge>
                        <span class="text-xs font-bold text-gray-600 dark:text-gray-300">Apartada</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <x-filament::badge color="gray" size="sm">Fuera de Servicio</x-filament::badge>
                        <span class="text-xs font-bold text-gray-600 dark:text-gray-300">Mantenimiento</span>
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Botón Mover Mesa --}}
                    <x-filament::button 
                        @click="$dispatch('open-modal', { id: 'modal-mover-mesa' })" 
                        color="gray" 
                        icon="heroicon-o-arrows-right-left" 
                        size="sm"
                    >
                        Mover Cuenta de Mesa (F4)
                    </x-filament::button>

                    {{-- Modal Desplegable de Unión de Mesas --}}
                    <div x-data="{ open: false }" class="relative">
                        <x-filament::button 
                            @click="open = !open" 
                            color="gray" 
                            icon="heroicon-o-link" 
                            size="sm"
                        >
                            Unir / Agrupar Mesas
                        </x-filament::button>

                        <div 
                            x-show="open" 
                            @click.away="open = false" 
                            x-transition
                            class="absolute right-0 mt-2 w-96 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl shadow-2xl p-5 z-50 space-y-4"
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
                                    <span class="text-[11px] font-bold text-gray-700 dark:text-gray-300 block mb-2">Seleccionar Mesas a Anexar</span>
                                    <div class="max-h-48 overflow-y-auto space-y-1.5 border border-gray-200 dark:border-gray-800 rounded-xl p-2 bg-gray-50 dark:bg-gray-950">
                                        @foreach($mesas->where('id', '!=', $mesaSeleccionadaId) as $mSec)
                                            <label class="flex items-center gap-2 p-1.5 hover:bg-white dark:hover:bg-gray-900 rounded-lg cursor-pointer text-xs">
                                                <input 
                                                    type="checkbox" 
                                                    value="{{ $mSec->id }}" 
                                                    wire:model="mesasParaUnir"
                                                    class="rounded border-gray-300 dark:border-gray-700 text-[#6b003e] focus:ring-[#6b003e]"
                                                />
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $mSec->nombre }}</span>
                                                <span class="text-[10px] text-gray-400">({{ $mSec->capacidad_personas }} pax)</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="pt-2 flex justify-end gap-2">
                                    <x-filament::button 
                                        wire:click="unirMesas" 
                                        color="primary" 
                                        size="sm"
                                        icon="heroicon-o-link"
                                    >
                                        Confirmar Unión
                                    </x-filament::button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Plano Gráfico del Restaurante agrupado por Zona --}}
        @if($mesas->isEmpty())
            <div class="text-center py-16 bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800">
                <x-filament::icon icon="heroicon-o-table-cells" class="w-12 h-12 text-gray-400 mx-auto mb-2" />
                <p class="text-sm font-semibold text-gray-500">No hay mesas configuradas en el restaurante.</p>
            </div>
        @else
            <div class="space-y-10">
                @php
                    $mesasPorZona = $mesas->groupBy(function($m) {
                        $mMeta = is_array($m->meta_datos) ? $m->meta_datos : (is_string($m->meta_datos) && $m->meta_datos !== '' ? json_decode($m->meta_datos, true) : []);
                        return is_array($mMeta) && isset($mMeta['zona_restaurante']) ? $mMeta['zona_restaurante'] : 'interior';
                    });
                @endphp

                @foreach($mesasPorZona as $zona => $listaMesas)
                    <x-filament::section collapsible>
                        <x-slot name="heading">
                            <span class="uppercase tracking-wider font-black text-sm text-gray-900 dark:text-white">
                                ZONA: {{ $zona === 'interior' ? 'Salón Interior' : ($zona === 'terraza' ? 'Jardín & Terraza' : ($zona === 'vip' ? 'Zona VIP / Salón Privado' : 'Bar / Barra')) }}
                            </span>
                        </x-slot>
                        <x-slot name="description">
                            Contiene {{ $listaMesas->count() }} mesas activas configuradas
                        </x-slot>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mt-4">
                            @foreach($listaMesas as $mesa)
                                @php
                                    $estilo = $this->obtenerConfiguracionEstiloMesa($mesa->estado);
                                    $estadoVal = $mesa->estado->value;

                                    $capacidad = (int) $mesa->capacidad_personas;
                                    $rawMeta = is_array($mesa->meta_datos) ? $mesa->meta_datos : (is_string($mesa->meta_datos) && $mesa->meta_datos !== '' ? json_decode($mesa->meta_datos, true) : []);
                                    $meta = is_array($rawMeta) ? $rawMeta : [];
                                    $esSecundariaUnida = !empty($meta['mesa_principal_id']);
                                    $esPrincipalConUnidas = !empty($meta['mesas_unidas']) && is_array($meta['mesas_unidas']);
                                    $motivo = $meta['motivo_union'] ?? 'uso_inmediato';
                                    $codigoReserva = $meta['codigo_reserva'] ?? null;
                                    $tipoMesa = $meta['tipo_mesa'] ?? 'redonda';
                                @endphp

                                <div 
                                    class="rounded-3xl p-5 border transition-all duration-300 flex flex-col justify-between relative group hover:shadow-xl {{ $estilo['borderCard'] }} {{ $esSecundariaUnida ? 'ring-2 ring-[rgba(107,0,62,0.4)]' : '' }}"
                                >
                                    {{-- Indicadores de Unión --}}
                                    @if($esSecundariaUnida)
                                        <div class="mb-2">
                                            <span class="inline-flex items-center gap-1.5 text-[10px] font-black text-[#e87faa] bg-[rgba(107,0,62,0.15)] dark:bg-[rgba(107,0,62,0.3)] px-2.5 py-0.5 rounded-full shadow-sm">
                                                <x-filament::icon icon="heroicon-o-link" class="w-3 h-3 text-[#6b003e] dark:text-[#e87faa]" />
                                                <span>Unida a {{ $meta['mesa_principal_nombre'] ?? 'Mesa Principal' }}</span>
                                                @if($motivo === 'reservacion') <span>(Reserva {{ $codigoReserva ?? '' }})</span> @endif
                                                <button 
                                                    wire:click="separarMesas({{ $mesa->id }})" 
                                                    class="hover:text-red-500 font-bold ml-1 p-0.5" 
                                                    title="Desvincular mesa"
                                                >
                                                    <x-filament::icon icon="heroicon-o-x-mark" class="w-3.5 h-3.5 text-red-500 hover:text-red-700" />
                                                </button>
                                            </span>
                                        </div>
                                    @elseif($esPrincipalConUnidas)
                                        <div class="mb-2">
                                            <span class="inline-flex items-center gap-1.5 text-[10px] font-black text-[#e87faa] bg-[rgba(107,0,62,0.15)] dark:bg-[rgba(107,0,62,0.3)] px-2.5 py-0.5 rounded-full shadow-sm">
                                                <x-filament::icon icon="heroicon-o-sparkles" class="w-3 h-3 text-[#6b003e] dark:text-[#e87faa]" />
                                                <span>Mesa Principal ({{ count($meta['mesas_unidas']) }} anexadas)</span>
                                                @if($motivo === 'reservacion') <span>(Reserva {{ $codigoReserva ?? '' }})</span> @endif
                                                <button 
                                                    wire:click="separarMesas({{ $mesa->id }})" 
                                                    class="hover:text-red-500 font-bold ml-1 p-0.5" 
                                                    title="Desvincular todas las mesas anexas"
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
                                            {{ $estilo['badgeLabel'] }}
                                        </x-filament::badge>
                                    </div>

                                    {{-- Croquis Físico Visual según Tipo de Mesa (Redonda, Cuadrada, Rectangular, Barra) --}}
                                    <div class="relative w-full h-28 flex items-center justify-center my-2">
                                        @if($tipoMesa === 'barra')
                                            <div class="w-full h-12 bg-slate-900 dark:bg-slate-950 border-2 border-[rgba(107,0,62,0.5)] rounded-xl flex items-center justify-between px-3 shadow-md z-10">
                                                <span class="font-black text-[#e87faa] text-xs uppercase tracking-wider">{{ $mesa->nombre }}</span>
                                                <span class="text-[10px] text-slate-400 font-bold uppercase">{{ $capacidad }} taburetes</span>
                                            </div>
                                        @elseif($tipoMesa === 'rectangular')
                                            <div class="w-28 h-16 bg-white dark:bg-gray-800 border-[3px] {{ $estilo['borderCircle'] }} rounded-2xl flex flex-col items-center justify-center shadow-lg z-10">
                                                <span class="font-black text-gray-900 dark:text-white text-xs leading-none">{{ $mesa->nombre }}</span>
                                                <span class="text-[9px] text-gray-500 dark:text-gray-400 font-bold mt-1 uppercase">{{ $capacidad }} pax</span>
                                            </div>
                                        @else
                                            {{-- Redonda por defecto --}}
                                            <div class="rounded-full w-20 h-20 bg-white dark:bg-gray-800 border-[4px] {{ $estilo['borderCircle'] }} flex flex-col items-center justify-center shadow-lg transition-transform group-hover:scale-105 duration-300 z-10">
                                                <span class="font-black text-gray-900 dark:text-white text-xs leading-none">{{ $mesa->nombre }}</span>
                                                <span class="text-[9px] text-gray-500 dark:text-gray-400 font-bold mt-1 uppercase">{{ $capacidad }} pax</span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Cuentas / Comandas Activas --}}
                                    <div class="my-3 space-y-2">
                                        @if($mesa->relationLoaded('pedidosActivos') && $mesa->pedidosActivos->isNotEmpty())
                                            <div class="flex items-center justify-between text-[10px] font-black uppercase text-gray-500 dark:text-gray-400 tracking-wider">
                                                <span>Cuentas ({{ $mesa->cuentas_activas_count ?? $mesa->pedidosActivos->count() }})</span>
                                                <span class="text-[#6b003e] dark:text-[#e87faa] font-black">
                                                    Total: {{ $this->simboloMoneda }} {{ number_format((float)($mesa->total_mesa ?? 0), 2) }}
                                                </span>
                                            </div>

                                            <div class="max-h-36 overflow-y-auto space-y-1.5 pr-1">
                                                @foreach($mesa->pedidosActivos as $pedidoActivo)
                                                    <div class="bg-white dark:bg-gray-800 border border-[rgba(107,0,62,0.2)] rounded-2xl p-2.5 flex items-center justify-between text-xs shadow-sm">
                                                        <div>
                                                            <span class="font-black text-gray-900 dark:text-white block text-[11px]">{{ $pedidoActivo->codigo }}</span>
                                                            <x-filament::badge color="{{ $pedidoActivo->estado->getColor() }}" size="xs">
                                                                {{ $pedidoActivo->estado->getLabel() }}
                                                            </x-filament::badge>
                                                        </div>
                                                         <div class="flex items-center gap-1.5">
                                                             <span class="font-black text-[#6b003e] dark:text-[#e87faa] text-xs">
                                                                 {{ $this->simboloMoneda }} {{ number_format((float)$pedidoActivo->total, 2) }}
                                                             </span>
                                                             <button
                                                                 wire:click="verDetallePedido({{ $pedidoActivo->id }})"
                                                                 class="p-1 rounded text-gray-400 hover:text-blue-500 transition-colors"
                                                                 title="Ver Detalle del Pedido"
                                                             >
                                                                 <x-filament::icon icon="heroicon-o-eye" class="w-3.5 h-3.5" />
                                                             </button>
                                                             <button
                                                                 wire:click="reimprimir({{ $pedidoActivo->id }})"
                                                                 class="p-1 rounded text-gray-400 hover:text-[#6b003e] transition-colors"
                                                                 title="Imprimir Comanda (F5)"
                                                             >
                                                                 <x-filament::icon icon="heroicon-o-printer" class="w-3.5 h-3.5" />
                                                             </button>
                                                             <a
                                                                 href="/admin/restaurante/pedidos/{{ $pedidoActivo->id }}/edit"
                                                                 class="p-1 rounded text-gray-400 hover:text-[#6b003e] transition-colors"
                                                                 title="Editar cuenta"
                                                             >
                                                                 <x-filament::icon icon="heroicon-o-pencil-square" class="w-3.5 h-3.5" />
                                                             </a>
                                                         </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center py-2 text-[11px] text-gray-400 italic">
                                                Sin comandas abiertas
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Botones de Acción Rápida POS --}}
                                    <div class="pt-3 border-t border-gray-200/80 dark:border-gray-800/80 flex flex-wrap items-center justify-between gap-2">
                                        {{-- Selector de Estado Rápido --}}
                                        <div class="w-full">
                                            <x-filament::input.wrapper size="xs">
                                                <x-filament::input.select 
                                                    wire:change="cambiarEstadoMesa({{ $mesa->id }}, $event.target.value)"
                                                    class="text-[11px] py-1"
                                                >
                                                    <option value="1" @selected($estadoVal === 1)>Disponible</option>
                                                    <option value="5" @selected($estadoVal === 5)>Ocupada</option>
                                                    <option value="4" @selected($estadoVal === 4)>Reservada</option>
                                                    <option value="3" @selected($estadoVal === 3 || $estadoVal === 6)>Pendiente Limpieza</option>
                                                    <option value="2" @selected($estadoVal === 2)>Fuera de Servicio</option>
                                                </x-filament::input.select>
                                            </x-filament::input.wrapper>
                                        </div>

                                        <div class="w-full flex items-center justify-between gap-2 pt-1">
                                             @if($mesa->relationLoaded('pedidosActivos') && $mesa->pedidosActivos->isNotEmpty())
                                                 <button
                                                     wire:click="iniciarCobro(@js($mesa->pedidosActivos->pluck('id')->values()->toArray()))"
                                                     class="flex-1 text-center py-1.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl text-xs shadow transition-colors inline-flex items-center justify-center gap-1"
                                                 >
                                                     <x-filament::icon icon="heroicon-o-banknotes" class="w-3.5 h-3.5" />
                                                     Pagar Todo
                                                 </button>
                                             @endif
                                             <a 
                                                 href="/admin/restaurante/pedidos/create?mesa_id={{ $mesa->id }}" 
                                                 class="flex-1 text-center py-1.5 px-3 bg-[#6b003e] hover:bg-[#8a004e] text-white font-black rounded-xl text-xs shadow transition-colors inline-flex items-center justify-center gap-1"
                                             >
                                                 <x-filament::icon icon="heroicon-o-plus" class="w-3.5 h-3.5" />
                                                 Nueva Comanda
                                             </a>
                                         </div>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </x-filament::section>
                @endforeach
            </div>
        @endif

        {{-- Modal Mover Mesa --}}
        <x-filament::modal id="modal-mover-mesa" width="md">
            <x-slot name="heading">
                Mover Cuenta a Otra Mesa (F4)
            </x-slot>
            <div class="space-y-4">
                <div>
                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">Mesa Origen (Mesa Actual)</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="mesaSeleccionadaId">
                            <option value="">-- Seleccionar Mesa Origen --</option>
                            @foreach($mesas as $m)
                                <option value="{{ $m->id }}">{{ $m->nombre }} (Mesa #{{ $m->id }})</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">Mesa Destino</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model="mesaDestinoId">
                            <option value="">-- Seleccionar Mesa Destino --</option>
                            @foreach($mesas as $m)
                                <option value="{{ $m->id }}">{{ $m->nombre }} (Mesa #{{ $m->id }})</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <x-filament::button wire:click="moverCuentaMesa" color="primary" icon="heroicon-o-arrows-right-left">
                        Confirmar Traslado
                    </x-filament::button>
                </div>
            </div>
        </x-filament::modal>

        {{-- Modal Aplicar Descuento --}}
        <x-filament::modal id="modal-descuento" width="md">
            <x-slot name="heading">
                Aplicar Descuento a Cuenta (F8)
            </x-slot>
            <div class="space-y-4">
                <div>
                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">ID Pedido / Cuenta</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="number" wire:model="pedidoDescuentoId" placeholder="Ej. 10" />
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">Descuento (%)</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="number" step="0.01" wire:model="descuentoPorcentaje" placeholder="Ej. 10.00" />
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">Motivo del Descuento (Obligatorio para Auditoría)</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="text" wire:model="motivoDescuento" placeholder="Ej. Cortesía gerencia / Promoción" />
                    </x-filament::input.wrapper>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <x-filament::button wire:click="aplicarDescuento" color="primary" icon="heroicon-o-check">
                        Aplicar Descuento
                    </x-filament::button>
                </div>
            </div>
        </x-filament::modal>

        {{-- Modal Detalle del Pedido --}}
        <x-filament::modal id="modal-detalle-pedido" width="lg">
            <x-slot name="heading">
                Detalle del Pedido
            </x-slot>
            @if($pedidoDetalle)
                <div class="space-y-4">
                    {{-- Encabezado --}}
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-sm font-black text-gray-900 dark:text-white">{{ $pedidoDetalle->codigo }}</span>
                            <x-filament::badge color="{{ $pedidoDetalle->estado->getColor() }}" size="sm" class="ml-2">
                                {{ $pedidoDetalle->estado->getLabel() }}
                            </x-filament::badge>
                        </div>
                        <span class="text-xs text-gray-500">{{ $pedidoDetalle->created_at?->format('d/m/Y H:i') }}</span>
                    </div>

                    {{-- Info --}}
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-xs">
                        <div>
                            <span class="text-gray-500 font-bold uppercase text-[10px]">Mesa</span>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $pedidoDetalle->mesa->nombre ?? 'N/D' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500 font-bold uppercase text-[10px]">Cliente</span>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $pedidoDetalle->cliente?->nombre_completo ?? 'Sin cliente' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500 font-bold uppercase text-[10px]">Mesero</span>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $pedidoDetalle->mesero?->persona?->nombre_completo ?? 'N/D' }}</p>
                        </div>
                    </div>

                    {{-- Tabla de Items --}}
                    <div class="border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-900">
                                    <th class="text-left px-3 py-2 font-bold text-gray-500 uppercase text-[10px]">Cant</th>
                                    <th class="text-left px-3 py-2 font-bold text-gray-500 uppercase text-[10px]">Platillo</th>
                                    <th class="text-right px-3 py-2 font-bold text-gray-500 uppercase text-[10px]">Precio</th>
                                    <th class="text-right px-3 py-2 font-bold text-gray-500 uppercase text-[10px]">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pedidoDetalle->items as $item)
                                    @if($item->estado?->value !== 'cancelado')
                                        <tr class="border-t border-gray-100 dark:border-gray-800">
                                            <td class="px-3 py-2 font-bold">{{ (int) $item->cantidad }}</td>
                                            <td class="px-3 py-2">
                                                <span class="font-semibold text-gray-900 dark:text-white">{{ $item->plato->nombre ?? 'Platillo' }}</span>
                                                @if($item->observaciones)
                                                    <br><span class="text-[10px] text-gray-400">OBS: {{ $item->observaciones }}</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-right">{{ $this->simboloMoneda }} {{ number_format((float) $item->precio_unitario, 2) }}</td>
                                            <td class="px-3 py-2 text-right font-bold">{{ $this->simboloMoneda }} {{ number_format((float) $item->subtotal, 2) }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Totales --}}
                    @php
                        $sub = 0;
                        foreach($pedidoDetalle->items as $it) { if($it->estado?->value !== 'cancelado') { $sub += (float)$it->subtotal; } }
                        $desc = (float)$pedidoDetalle->descuento_monto;
                        $base = max(0, $sub - $desc);
                        $imp = (float)$pedidoDetalle->impuesto_monto;
                        $prop = (float)$pedidoDetalle->propina_monto;
                        $tot = $base + $imp + $prop;
                    @endphp
                    <div class="flex justify-end">
                        <div class="w-64 space-y-1 text-xs">
                            <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span class="font-bold">{{ $this->simboloMoneda }} {{ number_format($sub, 2) }}</span></div>
                            @if($desc > 0)
                                <div class="flex justify-between text-red-500"><span>Descuento</span><span class="font-bold">-{{ $this->simboloMoneda }} {{ number_format($desc, 2) }}</span></div>
                            @endif
                            @if($imp > 0)
                                <div class="flex justify-between"><span class="text-gray-500">Impuesto</span><span class="font-bold">{{ $this->simboloMoneda }} {{ number_format($imp, 2) }}</span></div>
                            @endif
                            @if($prop > 0)
                                <div class="flex justify-between"><span class="text-gray-500">Propina</span><span class="font-bold">{{ $this->simboloMoneda }} {{ number_format($prop, 2) }}</span></div>
                            @endif
                            <div class="flex justify-between border-t border-gray-200 dark:border-gray-700 pt-1">
                                <span class="font-black text-sm">TOTAL</span>
                                <span class="font-black text-sm text-[#6b003e] dark:text-[#e87faa]">{{ $this->simboloMoneda }} {{ number_format($tot, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Botones de acción --}}
                    <div class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-gray-200 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.restaurante.comanda', ['pedido' => $pedidoDetalle->id, 'tipo' => 'reimpresion']) }}"
                               target="_blank"
                               class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-xl text-xs inline-flex items-center gap-1">
                                <x-filament::icon icon="heroicon-o-printer" class="w-3.5 h-3.5" />
                                Imprimir Comanda
                            </a>
                            <a href="{{ route('admin.restaurante.voucher', ['pedido' => $pedidoDetalle->id, 'tipo' => 'pedido', 'formato' => 'html']) }}"
                               target="_blank"
                               class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-xl text-xs inline-flex items-center gap-1">
                                <x-filament::icon icon="heroicon-o-document-text" class="w-3.5 h-3.5" />
                                Voucher Pedido
                            </a>
                            <a href="{{ route('admin.restaurante.voucher', ['pedido' => $pedidoDetalle->id, 'tipo' => 'pedido', 'formato' => 'pdf']) }}"
                               target="_blank"
                               class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-xl text-xs inline-flex items-center gap-1">
                                <x-filament::icon icon="heroicon-o-arrow-down-tray" class="w-3.5 h-3.5" />
                                Voucher PDF
                            </a>
                        </div>
                        <button wire:click="irACobrarDesdeDetalle"
                                class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl text-xs inline-flex items-center gap-1">
                            <x-filament::icon icon="heroicon-o-banknotes" class="w-3.5 h-3.5" />
                            Cobrar Este Pedido
                        </button>
                    </div>
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Cargando detalle del pedido...</p>
            @endif
        </x-filament::modal>

        {{-- Modal Cobro / Pago --}}
        <x-filament::modal id="modal-cobro" width="lg">
            <x-slot name="heading">
                Cobrar Pedido
            </x-slot>
            <div class="space-y-5">

                {{-- Resumen a cobrar --}}
                <div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-black text-emerald-700 dark:text-emerald-300 uppercase tracking-wider">Total a Cobrar</span>
                        <span class="text-lg font-black text-emerald-700 dark:text-emerald-300">{{ $this->simboloMoneda }} {{ number_format($totalCobro, 2) }}</span>
                    </div>
                    @if($pedidosCobroIds)
                        <p class="text-[11px] text-emerald-600 dark:text-emerald-400">{{ count($pedidosCobroIds) }} pedido(s) incluido(s)</p>
                    @endif
                </div>

                {{-- Cliente --}}
                <div>
                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-2">Cliente (Opcional)</label>
                    @if($clienteSeleccionadoId)
                        <div class="flex items-center justify-between bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-xl px-3 py-2">
                            <div class="flex items-center gap-2">
                                <x-filament::icon icon="heroicon-o-user" class="w-4 h-4 text-blue-600" />
                                <span class="text-sm font-bold text-blue-700 dark:text-blue-300">{{ $clienteSeleccionadoNombre }}</span>
                            </div>
                            <button wire:click="limpiarClienteSeleccionado" class="text-gray-400 hover:text-red-500" title="Quitar cliente">
                                <x-filament::icon icon="heroicon-o-x-mark" class="w-4 h-4" />
                            </button>
                        </div>
                    @else
                        <div class="space-y-2">
                            <div class="flex gap-2">
                                <x-filament::input.wrapper class="flex-1">
                                    <x-filament::input
                                        type="text"
                                        wire:model.live.debounce.300ms="busquedaCliente"
                                        wire:input="buscarClientesAction"
                                        placeholder="Buscar por nombre, teléfono o identificación..."
                                    />
                                </x-filament::input.wrapper>
                            </div>

                            @if($resultadosClientes->isNotEmpty())
                                <div class="max-h-40 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-xl divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach($resultadosClientes as $persona)
                                        <button wire:click="seleccionarCliente({{ $persona->id }})"
                                                class="w-full text-left px-3 py-2 hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-colors flex items-center justify-between">
                                            <div>
                                                <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $persona->nombre_completo ?? $persona->primer_nombre }}</span>
                                                @if($persona->telefono)
                                                    <span class="text-[10px] text-gray-400 ml-2">{{ $persona->telefono }}</span>
                                                @endif
                                            </div>
                                            <x-filament::icon icon="heroicon-o-check" class="w-3.5 h-3.5 text-blue-500" />
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            <p class="text-[10px] text-gray-400">Si el cliente no existe, continúe sin seleccionar (venta anónima).</p>
                        </div>
                    @endif
                </div>

                {{-- Método de pago --}}
                <div>
                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-2">Método de Pago</label>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                        @foreach([
                            1 => ['label' => 'Efectivo', 'icon' => 'heroicon-o-banknotes'],
                            2 => ['label' => 'TC', 'icon' => 'heroicon-o-credit-card'],
                            3 => ['label' => 'TD', 'icon' => 'heroicon-o-credit-card'],
                            4 => ['label' => 'Transfer.', 'icon' => 'heroicon-o-arrows-right-left'],
                            6 => ['label' => 'QR', 'icon' => 'heroicon-o-qr-code'],
                            8 => ['label' => 'Cortesía', 'icon' => 'heroicon-o-gift'],
                        ] as $metodo => $info)
                            <button wire:click="$set('metodoPago', {{ $metodo }})"
                                    class="py-2 px-2 rounded-xl border text-[11px] font-bold transition-all inline-flex flex-col items-center gap-1 {{ $metodoPago === $metodo ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300 shadow-sm' : 'border-gray-200 dark:border-gray-700 text-gray-500 hover:border-gray-400' }}">
                                <x-filament::icon :icon="$info['icon']" class="w-4 h-4" />
                                {{ $info['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Monto (solo efectivo) --}}
                @if($metodoPago === 1)
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">Monto Recibido</label>
                            <x-filament::input.wrapper>
                                <x-filament::input type="number" step="0.01" wire:model.live="montoRecibido" placeholder="0.00" />
                            </x-filament::input.wrapper>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">Vuelto</label>
                            <div class="px-3 py-2 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-black {{ $this->getVuelto() > 0 ? 'text-emerald-600' : 'text-gray-400' }}">
                                {{ $this->simboloMoneda }} {{ number_format($this->getVuelto(), 2) }}
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Referencia (para otros métodos) --}}
                @if(!in_array($metodoPago, [1, 8]))
                    <div>
                        <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">Referencia / No. Autorización</label>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" wire:model="referenciaPago" placeholder="Ej. 123456" />
                        </x-filament::input.wrapper>
                    </div>
                @endif

                {{-- Checkbox imprimir voucher --}}
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model="imprimirVoucherTrasPago" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300">Imprimir voucher de pago al confirmar</span>
                </label>

                {{-- Botones --}}
                <div class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-gray-800">
                    <button wire:click="cerrarCobro"
                            class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-xl text-xs">
                        Cancelar
                    </button>
                    <button wire:click="confirmarPago"
                            class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl text-xs shadow-lg inline-flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-check-circle" class="w-4 h-4" />
                        Confirmar Pago — {{ $this->simboloMoneda }} {{ number_format($totalCobro, 2) }}
                    </button>
                </div>
            </div>
        </x-filament::modal>

    </div>
</x-filament-panels::page>
