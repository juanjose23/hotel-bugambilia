@php
    use App\Filament\Resources\Inventario\Lote\Widgets\LotesEnRiesgoChart;
    use App\Filament\Resources\Inventario\Lote\Widgets\StockPorCategoriaChart;
    use App\Filament\Resources\Inventario\Lote\Widgets\ValorizacionInventarioChart;
    use App\Filament\Resources\Inventario\MovimientoStock\Widgets\MermasPorCategoriaChart;
    use App\Filament\Resources\Inventario\MovimientoStock\Widgets\RotacionInventarioChart;
    use Carbon\Carbon;
@endphp
<x-filament-panels::page>
    <div x-data="{ activeTab: (new URLSearchParams(window.location.search).get('tab')) || 'reports' }" class="space-y-6">
        <x-reportes.descarga-alpine />

        {{-- ─── Subheader / Encabezado ─────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-3 gap-4 dark:border-gray-700">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Centro de Inteligencia de Inventario</h1>
                <p class="text-xs text-gray-500 mt-0.5">Control de existencias físicas, lotes en cuarentena, valorización y mermas operativas.</p>
            </div>

            <div class="inline-flex rounded-xl border border-gray-200 bg-gray-50 p-1 text-xs font-bold dark:border-gray-800 dark:bg-gray-950">
                <button type="button" x-on:click="activeTab = 'reports'"
                    class="rounded-lg px-4 py-2 transition flex items-center gap-1.5"
                    :class="activeTab === 'reports' ? 'bg-white text-[#711C37] shadow-sm dark:bg-gray-900 dark:text-[#e87faa]' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'">
                    <x-heroicon-s-arrow-down-tray class="h-4 w-4" />
                    <span>Descargar Reportes</span>
                </button>
                <button type="button" x-on:click="activeTab = 'dashboard'"
                    class="rounded-lg px-4 py-2 transition flex items-center gap-1.5"
                    :class="activeTab === 'dashboard' ? 'bg-white text-[#711C37] shadow-sm dark:bg-gray-900 dark:text-[#e87faa]' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'">
                    <x-heroicon-s-chart-bar class="h-4 w-4" />
                    <span>Panel Visual</span>
                </button>
                <button type="button" x-on:click="activeTab = 'control'"
                    class="rounded-lg px-4 py-2 transition relative flex items-center gap-1.5"
                    :class="activeTab === 'control' ? 'bg-white text-[#711C37] shadow-sm dark:bg-gray-900 dark:text-[#e87faa]' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'">
                    <x-heroicon-s-exclamation-triangle class="h-4 w-4" />
                    <span>Control de Alertas</span>
                    @if(($lotesVencidos?->count() ?? 0) > 0 || ($lotesCuarentena?->count() ?? 0) > 0)
                        <span
                            class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white ring-2 ring-white">
                            {{ ($lotesVencidos?->count() ?? 0) + ($lotesCuarentena?->count() ?? 0) }}
                        </span>
                    @endif
                </button>
            </div>
        </div>

        {{-- ─── PESTAÑA 1: CENTRO DE REPORTES (ESTÁNDAR HOTEL BUGAMBILIAS) ────────── --}}
        <div x-show="activeTab === 'reports'" x-transition class="space-y-6">
            @php
                $reporteKey = $reportData['reporte'] ?? 'stock';
                $reporteConfig = \App\Support\ReporteConfig::getReportes()['inventario'][$reporteKey] ?? null;
                $tituloReporte = $reporteConfig['titulo'] ?? ($reporteKey ?: 'Inventario de Productos');
                $codigoReporte = $reporteConfig['codigo'] ?? 'HTB-INV-001';
            @endphp

            <x-reportes.layout
                titulo="Configuración del Informe"
                subtitulo="Seleccione el modelo de datos base y ajuste los parámetros requeridos"
                modulo="Módulo de Inventario & Suministros"
                color="indigo"
                icon="heroicon-o-archive-box"
                :codigo="$codigoReporte"
                :tituloReporte="$tituloReporte"
                :reportData="$reportData"
                :tieneExcel="\App\Support\ReporteConfig::tieneFormatoExcel('inventario', $reporteKey)"
            >
                <x-slot:kpis>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        {{-- KPI 1: Productos Activos --}}
                        <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-2xl font-extrabold text-gray-950 dark:text-white">{{ $stockPorProducto?->count() ?? 0 }}</span>
                                <x-heroicon-o-cube class="w-5 h-5 text-indigo-500" />
                            </div>
                            <span class="text-xs font-semibold text-emerald-500">↗ Productos con stock disponible</span>
                        </div>

                        {{-- KPI 2: En Cuarentena --}}
                        <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-2xl font-extrabold text-amber-600 dark:text-amber-400">{{ $lotesCuarentena?->count() ?? 0 }}</span>
                                <x-heroicon-o-shield-exclamation class="w-5 h-5 text-amber-500" />
                            </div>
                            <span class="text-xs font-semibold text-amber-600/80 dark:text-amber-400/80">Lotes retenidos por calidad</span>
                        </div>

                        {{-- KPI 3: Próximos a Vencer --}}
                        <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-2xl font-extrabold text-orange-600 dark:text-orange-400">{{ $lotesProximosVencer?->count() ?? 0 }}</span>
                                <x-heroicon-o-clock class="w-5 h-5 text-orange-500" />
                            </div>
                            <span class="text-xs font-semibold text-orange-600/80 dark:text-orange-400/80">Expiran en menos de 30 días</span>
                        </div>

                        {{-- KPI 4: Valor Total Inventario --}}
                        <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-lg">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $monedaSimbolo }} {{ number_format((float) ($valorTotalInventario ?? 0), 2) }}</span>
                                <x-heroicon-o-banknotes class="w-5 h-5 text-emerald-500" />
                            </div>
                            <span class="text-xs font-semibold text-emerald-600/80 dark:text-emerald-400/80">Valorización total en almacén</span>
                        </div>
                    </div>
                </x-slot:kpis>

                {{ $this->reportForm }}

                <x-slot:sidebarExtra>
                    <div class="rounded-3xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-xl">
                        <div class="flex items-center gap-3 mb-4 border-b border-gray-100 dark:border-gray-800 pb-3">
                            <div class="p-2.5 bg-[#711C37]/10 text-[#711C37] dark:text-[#e87faa] rounded-xl">
                                <x-heroicon-o-academic-cap class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-950 dark:text-white">Descripción de Informes</h3>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Alcance de los reportes del módulo</p>
                            </div>
                        </div>

                        <ul class="space-y-3 text-xs text-gray-600 dark:text-gray-300">
                            <li class="flex items-start gap-2">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-500 mt-1.5"></span>
                                <span><strong>Stock y Movimientos:</strong> Control físico en tiempo real, trazabilidad de entradas, salidas y transferencias.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500 mt-1.5"></span>
                                <span><strong>Vencimientos y Cuarentena:</strong> Alerta FEFO anticipada para evitar pérdidas y control de retención preventiva.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5"></span>
                                <span><strong>Valorización y Costos:</strong> Inversión inmovilizada, costo de ventas y rotación periódica.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-rose-500 mt-1.5"></span>
                                <span><strong>Mermas y Ajustes:</strong> Auditoría de diferencias físicas, bajas y desperdicios operacionales.</span>
                            </li>
                        </ul>
                    </div>
                </x-slot:sidebarExtra>
            </x-reportes.layout>
        </div>

        {{-- ─── PESTAÑA 2: PANEL VISUAL (DASHBOARD) ─────────────────────────── --}}
        <div x-show="activeTab === 'dashboard'" x-transition class="space-y-6" style="display: none;">
            {{-- KPIs Clave del Dashboard --}}
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                <div class="rounded-2xl border border-gray-150 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-800 transition hover:shadow-md">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Productos Activos</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $stockPorProducto?->count() ?? 0 }}</p>
                    <p class="mt-1 text-[10px] text-gray-400">con inventario físico disponible</p>
                </div>

                <div class="rounded-2xl border border-amber-100 bg-amber-50/50 p-4 shadow-sm dark:border-amber-900/30 dark:bg-amber-950/20 transition hover:shadow-md">
                    <p class="text-xs font-semibold text-amber-700 dark:text-amber-300 uppercase tracking-wider">En Cuarentena</p>
                    <p class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $lotesCuarentena?->count() ?? 0 }}</p>
                    <p class="mt-1 text-[10px] text-amber-500">lotes retenidos por calidad</p>
                </div>

                <div class="rounded-2xl border border-orange-100 bg-orange-50/50 p-4 shadow-sm dark:border-orange-900/30 dark:bg-orange-950/20 transition hover:shadow-md">
                    <p class="text-xs font-semibold text-orange-700 dark:text-orange-300 uppercase tracking-wider">Próximos a Vencer</p>
                    <p class="mt-1 text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $lotesProximosVencer?->count() ?? 0 }}</p>
                    <p class="mt-1 text-[10px] text-orange-500">lotes expiran en 30 días</p>
                </div>

                <div class="rounded-2xl border border-red-100 bg-red-50/50 p-4 shadow-sm dark:border-red-900/30 dark:bg-red-950/20 transition hover:shadow-md">
                    <p class="text-xs font-semibold text-red-700 dark:text-red-300 uppercase tracking-wider">Lotes Vencidos</p>
                    <p class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">{{ $lotesVencidos?->count() ?? 0 }}</p>
                    <p class="mt-1 text-[10px] text-red-500">lotes ya expirados en stock</p>
                </div>

                <div class="col-span-2 sm:col-span-1 rounded-2xl border border-emerald-100 bg-emerald-50/50 p-4 shadow-sm dark:border-emerald-900/30 dark:bg-emerald-950/20 transition hover:shadow-md">
                    <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider">Valor Total</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $monedaSimbolo }} {{ number_format((float) ($valorTotalInventario ?? 0), 2) }}</p>
                    <p class="mt-1 text-[10px] text-emerald-500">capital total en mercadería</p>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-150 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                <div class="flex items-center gap-2 mb-4 border-b border-gray-100 pb-3 dark:border-gray-700">
                    <x-heroicon-o-presentation-chart-line class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Análisis Gráfico</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Distribución física, valorización y mermas del hotel.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    @livewire(StockPorCategoriaChart::class)
                    @livewire(ValorizacionInventarioChart::class)
                    @livewire(RotacionInventarioChart::class)
                    @livewire(MermasPorCategoriaChart::class)
                    <div class="lg:col-span-2">
                        @livewire(LotesEnRiesgoChart::class)
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── PESTAÑA 3: CONTROL DE ALERTAS (TABLAS OPERATIVAS) ────────────── --}}
        <div x-show="activeTab === 'control'" x-transition class="space-y-6" style="display: none;">

            {{-- 1. Lotes Vencidos --}}
            <div class="rounded-2xl border border-red-200 bg-white shadow-sm dark:border-red-950 dark:bg-gray-800 overflow-hidden">
                <div class="border-b border-red-100 bg-red-50/50 px-6 py-4 dark:border-red-950 dark:bg-red-950/10">
                    <div class="flex items-center gap-2">
                        <span class="flex h-2.5 w-2.5 rounded-full bg-red-600 animate-ping"></span>
                        <x-heroicon-o-clock class="h-5 w-5 text-red-600 dark:text-red-400" />
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Lotes Vencidos (Expirados)</h2>
                        <span class="ml-2 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800 dark:bg-red-900/40 dark:text-red-300">
                            {{ $lotesVencidos?->count() ?? 0 }} alertas
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Lotes que han superado su fecha límite de consumo. Requieren baja inmediata o traslado a merma.</p>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700/50 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Código de Lote</th>
                                    <th class="px-4 py-3">Producto</th>
                                    <th class="px-4 py-3">Ubicación Bodega</th>
                                    <th class="px-4 py-3 text-right">Stock Vencido</th>
                                    <th class="px-4 py-3 text-center">Venció el</th>
                                    <th class="px-4 py-3 text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($lotesVencidos ?? [] as $lote)
                                <tr class="hover:bg-red-50/30 dark:hover:bg-red-950/20">
                                    <td class="px-4 py-3 font-mono text-xs font-semibold text-red-700 dark:text-red-400">{{ $lote->codigo_lote }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $lote->producto?->nombre }}</td>
                                    <td class="px-4 py-3 text-xs">{{ $lote->ubicacion?->nombre }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-red-600 dark:text-red-400">{{ number_format($lote->cantidad_disponible, 2) }}</td>
                                    <td class="px-4 py-3 text-center text-xs">{{ $lote->fecha_vencimiento?->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800 dark:bg-red-900/40 dark:text-red-300">
                                            Expirado
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <x-heroicon-o-check-circle class="h-8 w-8 text-emerald-500" />
                                            <span class="font-medium text-emerald-600">¡Excelente! No hay lotes vencidos en stock.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 2. Lotes en Cuarentena --}}
            <div class="rounded-2xl border border-amber-200 bg-white shadow-sm dark:border-amber-950 dark:bg-gray-800 overflow-hidden">
                <div class="border-b border-amber-100 bg-amber-50/50 px-6 py-4 dark:border-amber-950 dark:bg-amber-950/10">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-shield-exclamation class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Lotes Retenidos en Cuarentena</h2>
                        <span class="ml-2 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                            {{ $lotesCuarentena?->count() ?? 0 }} lotes
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Mercancía retenida preventivamente para inspección de calidad o verificación de procedencia.</p>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700/50 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Código de Lote</th>
                                    <th class="px-4 py-3">Producto</th>
                                    <th class="px-4 py-3">Ubicación Bodega</th>
                                    <th class="px-4 py-3 text-right">Cantidad Retenida</th>
                                    <th class="px-4 py-3">Motivo Cuarentena</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($lotesCuarentena ?? [] as $lote)
                                <tr class="hover:bg-amber-50/20 dark:hover:bg-amber-950/10">
                                    <td class="px-4 py-3 font-mono text-xs font-semibold text-amber-700 dark:text-amber-400">{{ $lote->codigo_lote }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $lote->producto?->nombre }}</td>
                                    <td class="px-4 py-3 text-xs">{{ $lote->ubicacion?->nombre }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-amber-700 dark:text-amber-400">{{ number_format($lote->cantidad_disponible, 2) }}</td>
                                    <td class="px-4 py-3 text-xs italic text-gray-500">{{ $lote->motivo_cuarentena ?? 'En inspección preventiva' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <x-heroicon-o-check-circle class="h-8 w-8 text-emerald-500" />
                                            <span class="font-medium text-emerald-600">¡Ninguno! No hay lotes retenidos en cuarentena en este momento.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 3. Lotes Próximos a Vencer --}}
            <div class="rounded-2xl border border-orange-200 bg-white shadow-sm dark:border-orange-950 dark:bg-gray-800 overflow-hidden">
                <div class="border-b border-orange-100 bg-orange-50/50 px-6 py-4 dark:border-orange-950 dark:bg-orange-950/10">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-bell-alert class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Lotes Próximos a Vencer (Consumo Prioritario)</h2>
                        <span class="ml-2 rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-semibold text-orange-800 dark:bg-orange-900/40 dark:text-amber-300">
                            {{ $lotesProximosVencer?->count() ?? 0 }} en alerta
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Productos que están próximos a expirar en los siguientes 30 días. Se recomienda darles salida prioritaria siguiendo el criterio FEFO.</p>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700/50 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Código de Lote</th>
                                    <th class="px-4 py-3">Producto</th>
                                    <th class="px-4 py-3">Ubicación Bodega</th>
                                    <th class="px-4 py-3 text-right">Stock Disponible</th>
                                    <th class="px-4 py-3 text-center">Expira el</th>
                                    <th class="px-4 py-3 text-center">Días Restantes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($lotesProximosVencer ?? [] as $lote)
                                <tr class="hover:bg-orange-50/20 dark:hover:bg-orange-950/10">
                                    <td class="px-4 py-3 font-mono text-xs font-semibold text-orange-700 dark:text-orange-400">{{ $lote->codigo_lote }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $lote->producto?->nombre }}</td>
                                    <td class="px-4 py-3 text-xs">{{ $lote->ubicacion?->nombre }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">{{ number_format($lote->cantidad_disponible, 2) }}</td>
                                    <td class="px-4 py-3 text-center text-orange-600 dark:text-orange-400 font-medium">{{ $lote->fecha_vencimiento?->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-semibold text-orange-800 dark:bg-orange-900/40 dark:text-orange-300">
                                            Quedan {{ (int) abs(now()->diffInDays(Carbon::parse($lote->fecha_vencimiento))) }} días
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <x-heroicon-o-check-circle class="h-8 w-8 text-emerald-500" />
                                            <span class="font-medium text-emerald-600">¡Ninguno! No hay productos próximos a vencer en los siguientes 30 días.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>


    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
