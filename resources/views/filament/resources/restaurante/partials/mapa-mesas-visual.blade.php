@php
    $zonasAgrupadas = $this->obtenerMesasAgrupadasPorZona();
    $totalMesas = $mesasFiltradas->count();
    $mesasLibres = $mesasFiltradas->filter(fn ($m) => $m->estado === \App\Enums\HabitacionesEspacios\EstadoEspacio::Disponible)->count();
    $mesasOcupadas = $mesasFiltradas->filter(fn ($m) => $m->estado === \App\Enums\HabitacionesEspacios\EstadoEspacio::Ocupado)->count();
    $mesasReservadas = $mesasFiltradas->filter(fn ($m) => $m->estado === \App\Enums\HabitacionesEspacios\EstadoEspacio::Reservado)->count();
    $mesasLimpieza = $mesasFiltradas->filter(fn ($m) => in_array($m->estado, [\App\Enums\HabitacionesEspacios\EstadoEspacio::Limpieza, \App\Enums\HabitacionesEspacios\EstadoEspacio::Sucio], true))->count();
    $capacidadTotal = $mesasFiltradas->sum(fn ($m) => (int) ($m->capacidad_personas ?? 0));
@endphp

<div class="space-y-6">
    {{-- Leyenda y estadísticas de ocupación del plano --}}
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-wider mr-2">
                Plano de Mesas:
            </span>

            {{-- Indicadores de estado --}}
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Disponibles ({{ $mesasLibres }})
            </span>

            <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800">
                <span class="size-2 rounded-full bg-rose-500"></span>
                Ocupadas ({{ $mesasOcupadas }})
            </span>

            <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-50 px-2.5 py-1 text-xs font-bold text-sky-700 border border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800">
                <span class="size-2 rounded-full bg-sky-500"></span>
                Reservadas ({{ $mesasReservadas }})
            </span>

            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                <span class="size-2 rounded-full bg-amber-500"></span>
                Limpieza ({{ $mesasLimpieza }})
            </span>
        </div>

        <div class="flex items-center gap-3 text-xs font-medium text-gray-500 dark:text-gray-400">
            <span>Capacidad activa: <strong class="font-bold text-gray-900 dark:text-white">{{ $capacidadTotal }} personas</strong></span>
            <span>&middot;</span>
            <span>Total: <strong class="font-bold text-gray-900 dark:text-white">{{ $totalMesas }} mesas</strong></span>
        </div>
    </div>

    {{-- Zonas arquitectónicas del restaurante --}}
    <div class="space-y-6">
        @forelse ($zonasAgrupadas as $zonaKey => $zonaData)
            @php
                $mesasZona = $zonaData['mesas'];
                $capZona = $mesasZona->sum(fn ($m) => (int) ($m->capacidad_personas ?? 0));
                $libresZona = $mesasZona->filter(fn ($m) => $m->estado === \App\Enums\HabitacionesEspacios\EstadoEspacio::Disponible)->count();
            @endphp

            <div class="rounded-3xl border border-gray-200 bg-gray-50/60 p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/40 space-y-4">
                {{-- Encabezado de la zona --}}
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 pb-3 dark:border-gray-800">
                    <div class="flex items-center gap-2.5">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-white text-gray-700 border border-gray-200 shadow-xs dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700">
                            <x-filament::icon :icon="$zonaData['icono']" class="size-5" />
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-950 dark:text-white tracking-tight">
                                {{ $zonaData['nombre'] }}
                            </h3>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                {{ $mesasZona->count() }} {{ $mesasZona->count() === 1 ? 'mesa' : 'mesas' }} configuradas &middot; Capacidad: {{ $capZona }} personas
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-white px-3 py-1 text-[11px] font-bold text-gray-700 border border-gray-200 shadow-xs dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
                            {{ $libresZona }} de {{ $mesasZona->count() }} libres
                        </span>
                    </div>
                </div>

                {{-- Matriz visual de mesas según su orden --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4 pt-1">
                    @foreach ($mesasZona as $mesa)
                        @php
                            $meta = is_array($mesa->meta_datos) ? $mesa->meta_datos : [];
                            $tipoMesa = strtolower((string) ($meta['tipo_mesa'] ?? 'cuadrada'));
                            $capacidad = (int) ($mesa->capacidad_personas ?? 2);
                            $ordenNum = (int) ($mesa->orden ?? $loop->iteration);
                            $tienePedidos = $mesa->relationLoaded('pedidosActivos') && $mesa->pedidosActivos->isNotEmpty();
                            $totalMesa = (float) ($mesa->total_mesa ?? 0);
                            $estilo = $this->obtenerConfiguracionEstiloMesa($mesa->estado);

                            $esSecundariaUnida = ! empty($meta['mesa_principal_id'] ?? null);
                            $mesasUnidas = is_array($meta['mesas_unidas'] ?? null) ? $meta['mesas_unidas'] : [];
                            $esPrincipalConUnidas = $mesasUnidas !== [];

                            $bgEstado = match ($mesa->estado) {
                                \App\Enums\HabitacionesEspacios\EstadoEspacio::Disponible => 'border-emerald-300 bg-white hover:border-emerald-500 hover:shadow-emerald-500/10 dark:border-emerald-900/60 dark:bg-gray-900',
                                \App\Enums\HabitacionesEspacios\EstadoEspacio::Ocupado => 'border-rose-300 bg-rose-50/30 hover:border-rose-500 hover:shadow-rose-500/10 dark:border-rose-900/60 dark:bg-rose-950/20',
                                \App\Enums\HabitacionesEspacios\EstadoEspacio::Reservado => 'border-sky-300 bg-sky-50/30 hover:border-sky-500 hover:shadow-sky-500/10 dark:border-sky-900/60 dark:bg-sky-950/20',
                                \App\Enums\HabitacionesEspacios\EstadoEspacio::Limpieza, \App\Enums\HabitacionesEspacios\EstadoEspacio::Sucio => 'border-amber-300 bg-amber-50/30 hover:border-amber-500 hover:shadow-amber-500/10 dark:border-amber-900/60 dark:bg-amber-950/20',
                                default => 'border-gray-300 bg-gray-50 hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900',
                            };

                            $iconoForma = match ($tipoMesa) {
                                'redonda' => 'rounded-full',
                                'barra' => 'rounded-2xl border-dashed',
                                'rectangular' => 'rounded-2xl',
                                default => 'rounded-xl',
                            };
                        @endphp

                        <div
                            wire:key="mapa-mesa-{{ $mesa->id }}"
                            class="relative flex flex-col justify-between rounded-2xl border p-4 shadow-xs transition-all duration-200 hover:-translate-y-1 hover:shadow-md cursor-pointer {{ $bgEstado }} {{ $esSecundariaUnida ? 'ring-2 ring-primary-500/50' : '' }}"
                            @if ($tienePedidos)
                                wire:click="verComandasMesa({{ $mesa->id }})"
                            @elseif ($mesa->estado === \App\Enums\HabitacionesEspacios\EstadoEspacio::Reservado)
                                wire:click="confirmarLlegadaReserva({{ $mesa->id }})"
                            @endif
                        >
                            {{-- Insignia de Orden Secuencial y Estado --}}
                            <div class="flex items-center justify-between gap-1 mb-2">
                                <span class="flex items-center gap-1 rounded-lg bg-gray-100 px-2 py-0.5 text-[10px] font-black text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                    #{{ $ordenNum }}
                                </span>

                                <div class="flex items-center gap-1">
                                    @if ($mesa->tipo === \App\Enums\HabitacionesEspacios\TipoEspacio::MESA && $mesa->tiene_activo_asignado === false)
                                        <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase tracking-wider rounded-full px-1.5 py-0.5 bg-rose-100 text-rose-700 border border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800">
                                            Sin Mobiliario
                                        </span>
                                    @endif

                                    <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider rounded-full px-2 py-0.5 {{ $estilo['borderCard'] ?? '' }}">
                                        <span class="size-1.5 rounded-full {{ $estilo['bgDot'] ?? 'bg-gray-400' }}"></span>
                                        {{ $estilo['badgeLabel'] ?? 'Estado' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Representación Geométrica de la Mesa y Sillas --}}
                            <div class="my-3 flex flex-col items-center justify-center py-2">
                                <div class="relative flex size-20 items-center justify-center border-2 border-gray-300 dark:border-gray-700 bg-card shadow-xs transition-transform {{ $iconoForma }}">
                                    {{-- Nombre de la mesa en el centro --}}
                                    <div class="text-center">
                                        <span class="text-xs font-black text-gray-900 dark:text-white block leading-tight">{{ $mesa->nombre }}</span>
                                        <span class="text-[9px] font-bold text-gray-500 dark:text-gray-400">{{ $capacidad }} pers.</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Información inferior y estados especiales --}}
                            <div class="border-t border-gray-200/80 pt-2 text-xs dark:border-gray-800/80 space-y-1">
                                @if ($tienePedidos)
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] text-gray-500 font-medium">Consumo:</span>
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-black text-rose-600 dark:text-rose-400">
                                                {{ $simboloMoneda }} {{ number_format($totalMesa, 2, ',', '.') }}
                                            </span>
                                            <button
                                                type="button"
                                                dusk="mesa-{{ $mesa->id }}-cobrar"
                                                wire:click.stop="verComandasMesa({{ $mesa->id }})"
                                                class="rounded-lg bg-rose-600 px-2 py-0.5 text-[10px] font-black text-white shadow-xs hover:bg-rose-500 transition-colors cursor-pointer"
                                            >
                                                Cobrar
                                            </button>
                                        </div>
                                    </div>
                                @elseif ($mesa->estado === \App\Enums\HabitacionesEspacios\EstadoEspacio::Reservado)
                                    <div class="flex items-center justify-between gap-1">
                                        <div class="text-[10px] text-sky-700 dark:text-sky-300 truncate font-semibold">
                                            👤 {{ $meta['nombre_cliente'] ?? 'Reserva' }} ({{ $meta['hora_reserva'] ?? 'Hoy' }})
                                        </div>
                                        <button
                                            type="button"
                                            dusk="mesa-{{ $mesa->id }}-llegada"
                                            wire:click.stop="confirmarLlegadaReserva({{ $mesa->id }})"
                                            class="rounded-lg bg-sky-600 px-2 py-0.5 text-[10px] font-black text-white shadow-xs hover:bg-sky-500 transition-colors cursor-pointer"
                                        >
                                            Llegada
                                        </button>
                                    </div>
                                @else
                                    <div class="flex items-center justify-between text-[10px] text-gray-400">
                                        <span>Tipo: {{ ucfirst($tipoMesa) }}</span>
                                        @if ($mesa->estado === \App\Enums\HabitacionesEspacios\EstadoEspacio::Disponible && $mesa->tiene_activo_asignado !== false)
                                            <a
                                                href="/admin/restaurante/pedidos/create?espacio_id={{ $mesa->id }}"
                                                dusk="mesa-{{ $mesa->id }}-comanda"
                                                class="rounded-lg bg-emerald-600 px-2 py-0.5 text-[10px] font-black text-white shadow-xs hover:bg-emerald-500 transition-colors cursor-pointer"
                                            >
                                                + Comanda
                                            </a>
                                        @endif
                                    </div>
                                @endif

                                @if ($esPrincipalConUnidas)
                                    <div class="rounded-md bg-primary/10 px-1.5 py-0.5 text-[9px] font-bold text-primary dark:text-rose-300">
                                        🔗 Mesas unidas: +{{ count($mesasUnidas) }}
                                    </div>
                                @elseif ($esSecundariaUnida)
                                    <div class="rounded-md bg-gray-100 px-1.5 py-0.5 text-[9px] font-bold text-gray-500 dark:bg-gray-800">
                                        🔗 Unida a mesa principal
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 p-8 text-center dark:border-gray-700">
                <p class="text-sm font-bold text-gray-600 dark:text-gray-400">
                    No se encontraron mesas para los filtros seleccionados.
                </p>
            </div>
        @endforelse
    </div>
</div>
