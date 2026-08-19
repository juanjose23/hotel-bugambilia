<x-filament-panels::page>
    <div x-data x-on:open-new-tab.window="window.open($event.detail.url || $event.detail[0]?.url, '_blank')" class="w-full space-y-6">

        {{-- ─── Subheader / Encabezado ─────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-950 dark:text-white">Centro de Inteligencia Gastronómica & Restaurante</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Control de comandas, recaudación diaria, ranking de platillos e ingresos por categorías.</p>
            </div>
        </div>

        {{-- Resumen KPIs --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Pedidos --}}
            <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-2xl font-extrabold text-gray-950 dark:text-white">
                        {{ $resumen['total_pedidos'] ?? 0 }}
                    </span>
                    <x-heroicon-o-document-text class="w-5 h-5 text-indigo-500" />
                </div>
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Total pedidos procesados</span>
            </div>

            {{-- Facturado Total --}}
            <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">
                        C$ {{ number_format((float) ($resumen['total_facturado'] ?? 0), 2) }}
                    </span>
                    <x-heroicon-o-banknotes class="w-5 h-5 text-emerald-500" />
                </div>
                <span class="text-xs font-semibold text-emerald-600/80 dark:text-emerald-400/80">Facturación acumulada</span>
            </div>

            {{-- Pedidos Pagados --}}
            <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-2xl font-extrabold text-teal-600 dark:text-teal-400">
                        {{ $resumen['pedidos_pagados'] ?? 0 }}
                    </span>
                    <x-heroicon-o-check-circle class="w-5 h-5 text-teal-500" />
                </div>
                <span class="text-xs font-semibold text-teal-600/80 dark:text-teal-400/80">Comandas liquidadas</span>
            </div>

            {{-- Pedidos Abiertos / Preparación --}}
            <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-2xl font-extrabold text-amber-600 dark:text-amber-400">
                        {{ $resumen['pedidos_pendientes'] ?? 0 }}
                    </span>
                    <x-heroicon-o-clock class="w-5 h-5 text-amber-500" />
                </div>
                <span class="text-xs font-semibold text-amber-600/80 dark:text-amber-400/80">En cocina o pendientes</span>
            </div>
        </div>

        {{-- Contenedor de Rankings e Ingresos --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Top Platos --}}
            <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between mb-2 pb-4 border-b border-gray-100 dark:border-gray-800">
                    <div>
                        <h3 class="font-black text-lg text-gray-950 dark:text-white tracking-tight">Top 10 Platos Más Vendidos</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold">Platos preferidos y demandados por los clientes en el periodo.</p>
                    </div>
                </div>
                <div class="space-y-4">
                    @forelse($topPlatos as $i => $p)
                        <div class="flex items-center justify-between py-1.5 transition-transform hover:translate-x-1 duration-200">
                            <div class="flex items-center gap-3">
                                <span class="flex items-center justify-center text-xs font-black rounded-xl w-7 h-7 {{ $i === 0 ? 'bg-[#711C37]/20 text-[#711C37] dark:text-[#e87faa]' : ($i === 1 ? 'bg-gray-400/20 text-gray-600' : ($i === 2 ? 'bg-amber-500/20 text-amber-600' : 'bg-gray-100 dark:bg-gray-800 text-gray-500')) }}">
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
            <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between mb-2 pb-4 border-b border-gray-100 dark:border-gray-800">
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
                            @php
                                $maxTotal = count($porCategoria) > 0 ? max(array_column($porCategoria, 'total')) : 1;
                                $percentage = ($cat['total'] / ($maxTotal ?: 1)) * 100;
                            @endphp
                            <div class="w-full bg-gray-100 dark:bg-gray-800 h-2.5 rounded-full overflow-hidden">
                                <div class="bg-[#711C37] h-full rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
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
        <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-xl space-y-4">
            <div class="mb-2">
                <h3 class="font-black text-lg text-gray-950 dark:text-white tracking-tight">Registro General de Pedidos</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold">Detalle de cada orden abierta, servida o pagada en tiempo real.</p>
            </div>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
