<x-filament-panels::page>
    <div class="space-y-8 font-sans">
        {{-- Filtro de fechas premium --}}
        <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800/80 p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6 transition-all">
            <div class="space-y-1">
                <h2 class="text-lg font-black tracking-tight text-gray-900 dark:text-white">Filtrar Periodo</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold">Seleccione el rango de fechas para actualizar las métricas y los informes de ventas.</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="relative">
                    <span class="absolute top-2 left-3 text-[10px] uppercase font-bold text-gray-400">Desde</span>
                    <input type="date" wire:model="fechaInicio" wire:change="cargarReportes" 
                        class="pt-5 pb-2 px-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700/80 rounded-2xl text-xs font-black text-gray-800 dark:text-white focus:ring-2 focus:ring-[#6b003e] focus:border-[#6b003e] transition-all w-44">
                </div>
                <div class="relative">
                    <span class="absolute top-2 left-3 text-[10px] uppercase font-bold text-gray-400">Hasta</span>
                    <input type="date" wire:model="fechaFin" wire:change="cargarReportes" 
                        class="pt-5 pb-2 px-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700/80 rounded-2xl text-xs font-black text-gray-800 dark:text-white focus:ring-2 focus:ring-[#6b003e] focus:border-[#6b003e] transition-all w-44">
                </div>
            </div>
        </div>

        {{-- Resumen KPIs con diseño de tarjetas modernas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Total Pedidos --}}
            <div class="relative overflow-hidden bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800/80 rounded-3xl p-6 shadow-sm hover:shadow-md transition-shadow group">
                <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-24 h-24 text-gray-900 dark:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <span class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Total Pedidos</span>
                <p class="text-3xl font-black mt-2 text-gray-900 dark:text-white tracking-tight">
                    {{ $resumen['total_pedidos'] ?? 0 }}
                </p>
                <div class="mt-4 flex items-center gap-1.5 text-xs font-extrabold text-blue-600 bg-blue-500/10 px-2.5 py-1 rounded-full w-max">
                    <span>Ventas procesadas</span>
                </div>
            </div>

            {{-- Facturado --}}
            <div class="relative overflow-hidden bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800/80 rounded-3xl p-6 shadow-sm hover:shadow-md transition-shadow group">
                <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-24 h-24 text-gray-900 dark:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Facturado Total</span>
                <p class="text-3xl font-black mt-2 text-gray-900 dark:text-white tracking-tight">
                    C$ {{ number_format($resumen['total_facturado'] ?? 0, 2) }}
                </p>
                <div class="mt-4 flex items-center gap-1.5 text-xs font-extrabold text-[#6b003e] bg-[rgba(107,0,62,0.1)] px-2.5 py-1 rounded-full w-max">
                    <span>Ingresos brutos</span>
                </div>
            </div>

            {{-- Pagados --}}
            <div class="relative overflow-hidden bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800/80 rounded-3xl p-6 shadow-sm hover:shadow-md transition-shadow group">
                <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-24 h-24 text-gray-900 dark:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Pedidos Pagados</span>
                <p class="text-3xl font-black mt-2 text-[#6b003e] dark:text-[#e87faa] tracking-tight">
                    {{ $resumen['pedidos_pagados'] ?? 0 }}
                </p>
                <div class="mt-4 flex items-center gap-1.5 text-xs font-extrabold text-[#6b003e] bg-[rgba(107,0,62,0.1)] px-2.5 py-1 rounded-full w-max">
                    <span>Transacciones liquidadas</span>
                </div>
            </div>

            {{-- Pendientes --}}
            <div class="relative overflow-hidden bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800/80 rounded-3xl p-6 shadow-sm hover:shadow-md transition-shadow group">
                <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-24 h-24 text-gray-900 dark:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Pedidos Abiertos / Preparación</span>
                <p class="text-3xl font-black mt-2 text-[#6b003e] dark:text-[#e87faa] tracking-tight">
                    {{ $resumen['pedidos_pendientes'] ?? 0 }}
                </p>
                <div class="mt-4 flex items-center gap-1.5 text-xs font-extrabold text-[#6b003e] bg-[rgba(107,0,62,0.1)] px-2.5 py-1 rounded-full w-max">
                    <span>En cocina o abiertos</span>
                </div>
            </div>
        </div>

        {{-- Contenedor de Rankings e Ingresos --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Top Platos con diseño elegante --}}
            <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800/80 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100 dark:border-gray-800">
                    <div>
                        <h3 class="font-black text-lg text-gray-950 dark:text-white tracking-tight">Top 10 Platos Más Vendidos</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold">Platos preferidos y demandados por los clientes en el periodo.</p>
                    </div>
                </div>
                <div class="space-y-4">
                    @forelse($topPlatos as $i => $p)
                        <div class="flex items-center justify-between py-1.5 transition-transform hover:translate-x-1 duration-200">
                            <div class="flex items-center gap-3">
                                <span class="flex items-center justify-center text-xs font-black rounded-xl w-7 h-7 {{ $i === 0 ? 'bg-[rgba(107,0,62,0.2)] text-[#6b003e]' : ($i === 1 ? 'bg-gray-400/20 text-gray-600' : ($i === 2 ? 'bg-[rgba(107,0,62,0.12)] text-[#6b003e]' : 'bg-gray-100 dark:bg-gray-800 text-gray-500')) }}">
                                    {{ $i + 1 }}
                                </span>
                                <span class="text-sm font-extrabold text-gray-800 dark:text-gray-200">{{ $p['plato'] }}</span>
                            </div>
                            <div class="text-right flex items-center gap-4">
                                <span class="text-sm font-black text-gray-950 dark:text-white">×{{ $p['cantidad'] }}</span>
                                <span class="text-xs font-bold text-gray-400 min-w-[70px] text-right">C$ {{ number_format($p['total'], 2) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10">
                            <p class="text-xs text-gray-400 font-bold">Sin datos de platillos en este periodo.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Por Categoría --}}
            <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800/80 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100 dark:border-gray-800">
                    <div>
                        <h3 class="font-black text-lg text-gray-950 dark:text-white tracking-tight">Ingresos por Categoría</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold">Distribución financiera según la clasificación gastronómica.</p>
                    </div>
                </div>
                <div class="space-y-5">
                    @forelse($porCategoria as $cat)
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-gray-700 dark:text-gray-300">{!! $cat['categoria'] !!}</span>
                                <span class="text-gray-950 dark:text-white">C$ {{ number_format($cat['total'], 2) }} <span class="text-[10px] text-gray-400 ml-1">({{ $cat['cantidad'] }} platos)</span></span>
                            </div>
                            {{-- Barra de progreso estimativa --}}
                            @php
                                $maxTotal = count($porCategoria) > 0 ? max(array_column($porCategoria, 'total')) : 1;
                                $percentage = ($cat['total'] / ($maxTotal ?: 1)) * 100;
                            @endphp
                            <div class="w-full bg-gray-100 dark:bg-gray-800 h-2.5 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r from-[#6b003e] to-[#8a004e] h-full rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10">
                            <p class="text-xs text-gray-400 font-bold">Sin datos de categorías en este periodo.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Tabla de Pedidos --}}
        <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800/80 p-6 shadow-sm">
            <div class="mb-4">
                <h3 class="font-black text-lg text-gray-950 dark:text-white tracking-tight">Registro General de Pedidos</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold">Detalle de cada orden abierta, servida o pagada en tiempo real.</p>
            </div>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
