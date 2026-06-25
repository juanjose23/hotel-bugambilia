@php
    use App\Filament\Resources\Inventario\Lote\Widgets\StockPorCategoriaChart;
    use App\Filament\Resources\Inventario\Lote\Widgets\ValorizacionInventarioChart;
    use App\Filament\Resources\Inventario\MovimientoStock\Widgets\RotacionInventarioChart;
    use App\Filament\Resources\Inventario\MovimientoStock\Widgets\MermasPorCategoriaChart;
    use App\Filament\Resources\Inventario\Lote\Widgets\LotesEnRiesgoChart;
@endphp
<x-filament-panels::page>
    <div x-data="{ activeTab: 'dashboard' }" class="space-y-6">

        {{-- ─── Navegación de Pestañas (Modern Tabs) ─────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-3 gap-4 dark:border-gray-700">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Panel de Inteligencia de Inventario</h1>
                <p class="text-xs text-gray-500 mt-0.5">Control visual, alertas operativas y descarga de reportes para
                    administración.</p>
            </div>

            <div class="flex flex-wrap gap-1.5 bg-gray-100 p-1 rounded-xl dark:bg-gray-900 shadow-inner">
                <button
                        @click="activeTab = 'dashboard'"
                        :class="activeTab === 'dashboard' ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400 font-semibold' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50/50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800/30'"
                        class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200">
                    <x-heroicon-s-chart-bar class="h-4 w-4" />
                    <span>Panel Visual</span>
                </button>
                <button
                        @click="activeTab = 'control'"
                        :class="activeTab === 'control' ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400 font-semibold' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50/50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800/30'"
                        class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 relative">
                    <x-heroicon-s-exclamation-triangle class="h-4 w-4" />
                    <span>Control de Alertas</span>
                    @if(($lotesVencidos?->count() ?? 0) > 0 || ($lotesCuarentena?->count() ?? 0) > 0)
                        <span class="absolute -top-1.5 -right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white ring-2 ring-white">
                            {{ ($lotesVencidos?->count() ?? 0) + ($lotesCuarentena?->count() ?? 0) }}
                        </span>
                    @endif
                </button>
                <button
                        @click="activeTab = 'reports'"
                        :class="activeTab === 'reports' ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400 font-semibold' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50/50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800/30'"
                        class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200">
                    <x-heroicon-s-arrow-down-tray class="h-4 w-4" />
                    <span>Descargar Reportes</span>
                </button>
            </div>
        </div>

        {{-- ─── KPIs Clave (Siempre Visibles en la Cabecera) ─────────────────── --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            {{-- Total lotes disponibles --}}
            <div class="rounded-2xl border border-gray-150 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-800 transition hover:shadow-md">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Productos
                    Activos</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $stockPorProducto?->count() ?? 0 }}
                </p>
                <p class="mt-1 text-[10px] text-gray-400">con inventario físico disponible</p>
            </div>

            {{-- Lotes en cuarentena --}}
            <div class="rounded-2xl border border-amber-100 bg-amber-50/50 p-4 shadow-sm dark:border-amber-900/30 dark:bg-amber-950/20 transition hover:shadow-md">
                <p class="text-xs font-semibold text-amber-700 dark:text-amber-300 uppercase tracking-wider">En
                    Cuarentena</p>
                <p class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400">
                    {{ $lotesCuarentena?->count() ?? 0 }}
                </p>
                <p class="mt-1 text-[10px] text-amber-500">lotes retenidos por calidad</p>
            </div>

            {{-- Próximos a vencer --}}
            <div class="rounded-2xl border border-orange-100 bg-orange-50/50 p-4 shadow-sm dark:border-orange-900/30 dark:bg-orange-950/20 transition hover:shadow-md">
                <p class="text-xs font-semibold text-orange-700 dark:text-orange-300 uppercase tracking-wider">Próximos
                    a Vencer</p>
                <p class="mt-1 text-2xl font-bold text-orange-600 dark:text-orange-400">
                    {{ $lotesProximosVencer?->count() ?? 0 }}
                </p>
                <p class="mt-1 text-[10px] text-orange-500">lotes expiran en 30 días</p>
            </div>

            {{-- Lotes Vencidos --}}
            <div class="rounded-2xl border border-red-100 bg-red-50/50 p-4 shadow-sm dark:border-red-900/30 dark:bg-red-950/20 transition hover:shadow-md">
                <p class="text-xs font-semibold text-red-700 dark:text-red-300 uppercase tracking-wider">Lotes
                    Vencidos</p>
                <p class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">
                    {{ $lotesVencidos?->count() ?? 0 }}
                </p>
                <p class="mt-1 text-[10px] text-red-500">lotes ya expirados en stock</p>
            </div>

            {{-- Valor total del inventario --}}
            <div class="col-span-2 sm:col-span-1 rounded-2xl border border-emerald-100 bg-emerald-50/50 p-4 shadow-sm dark:border-emerald-900/30 dark:bg-emerald-950/20 transition hover:shadow-md">
                <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider">Valor
                    Monetario</p>
                <p class="mt-1 text-xl font-bold text-emerald-600 dark:text-emerald-400">
                    {{ $monedaSimbolo }} {{ number_format((float) ($valorTotalInventario ?? 0), 2) }}
                </p>
                <p class="mt-1 text-[10px] text-emerald-500">capital total en mercadería</p>
            </div>
        </div>

        {{-- ─── PESTAÑA 1: PANEL VISUAL (DASHBOARD) ─────────────────────────── --}}
        <div x-show="activeTab === 'dashboard'" x-transition class="space-y-6">
            <div class="rounded-2xl border border-gray-150 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                <div class="flex items-center gap-2 mb-4 border-b border-gray-100 pb-3 dark:border-gray-700">
                    <x-heroicon-o-presentation-chart-line class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Análisis Gráfico</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Distribución física, valorización y mermas del
                            hotel.</p>
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

        {{-- ─── PESTAÑA 2: CONTROL DE ALERTAS (TABLAS OPERATIVAS) ────────────── --}}
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
                    <p class="mt-1 text-xs text-gray-500">Estos productos ya expiraron. **No deben consumirse**. Por
                        favor, retíralos de las bodegas y regístralos como una merma.</p>
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
                                <th class="px-4 py-3 text-center">Venció el</th>
                                <th class="px-4 py-3 text-center">Días de Vencido</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($lotesVencidos ?? [] as $lote)
                                <tr class="hover:bg-red-50/20 dark:hover:bg-red-950/10">
                                    <td class="px-4 py-3 font-mono text-xs font-semibold text-red-700 dark:text-red-400">{{ $lote->codigo_lote }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $lote->producto?->nombre }}</td>
                                    <td class="px-4 py-3 text-xs">{{ $lote->ubicacion?->nombre }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">{{ number_format($lote->cantidad_disponible, 2) }}</td>
                                    <td class="px-4 py-3 text-center text-red-600 dark:text-red-400 font-medium">{{ $lote->fecha_vencimiento?->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800 dark:bg-red-900/40 dark:text-red-300">
                                            Hace {{ (int) abs(now()->diffInDays(\Carbon\Carbon::parse($lote->fecha_vencimiento))) }} días
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <x-heroicon-o-check-circle class="h-8 w-8 text-emerald-500" />
                                            <span class="font-medium text-emerald-600">¡Ninguno! No hay productos vencidos en stock actual.</span>
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
                        <x-heroicon-o-shield-check class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Lotes en Cuarentena
                            (Retenidos)</h2>
                        <span class="ml-2 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                            {{ $lotesCuarentena?->count() ?? 0 }} retenidos
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Productos ingresados recientemente en espera de aprobación de
                        calidad o revisión administrativa.</p>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700/50 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-3">Código de Lote</th>
                                <th class="px-4 py-3">Producto</th>
                                <th class="px-4 py-3">Proveedor</th>
                                <th class="px-4 py-3 text-right">Cantidad</th>
                                <th class="px-4 py-3 text-center">Fecha Ingreso</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($lotesCuarentena ?? [] as $lote)
                                <tr class="hover:bg-amber-50/20 dark:hover:bg-amber-950/10">
                                    <td class="px-4 py-3 font-mono text-xs font-semibold text-amber-700 dark:text-amber-400">{{ $lote->codigo_lote }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $lote->producto?->nombre }}</td>
                                    <td class="px-4 py-3 text-xs">{{ $lote->recepcionItem?->recepcion?->proveedor?->nombre_comercial ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">{{ number_format($lote->cantidad_disponible, 2) }}</td>
                                    <td class="px-4 py-3 text-center text-xs">{{ $lote->created_at?->format('d/m/Y H:i') ?? 'N/A' }}</td>
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
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Lotes Próximos a Vencer (Consumo
                            Prioritario)</h2>
                        <span class="ml-2 rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-semibold text-orange-800 dark:bg-orange-900/40 dark:text-amber-300">
                            {{ $lotesProximosVencer?->count() ?? 0 }} en alerta
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Productos que están próximos a expirar en los siguientes **30
                        días**. Se recomienda darles salida prioritaria siguiendo el criterio FEFO.</p>
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
                                            Quedan {{ (int) abs(now()->diffInDays(\Carbon\Carbon::parse($lote->fecha_vencimiento))) }} días
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

        {{-- ─── PESTAÑA 3: DESCARGAR REPORTES (CENTRO DE REPORTES) ────────────── --}}
        <div x-show="activeTab === 'reports'" x-transition class="space-y-6" style="display: none;">

            <div class="rounded-2xl border border-gray-150 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                <div class="flex items-center gap-2 mb-6 border-b border-gray-100 pb-3 dark:border-gray-700">
                    <x-heroicon-o-arrow-down-tray class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Centro de Descarga de
                            Documentos</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Configura formatos y filtros a medida antes de generar y
                            descargar tus archivos oficiales.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">

                    {{-- 1. Stock Físico --}}
                    <div class="flex flex-col justify-between rounded-2xl border border-gray-100 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-900/50 hover:shadow-md transition">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <x-heroicon-o-cube class="h-5 w-5 text-blue-500" />
                                <h3 class="text-sm font-bold text-gray-950 dark:text-white">Inventario de Productos</h3>
                            </div>
                            <p class="text-xs text-gray-500 mb-4 dark:text-gray-400">Listado detallado de todos los
                                productos y el stock físico actual que se encuentra en los almacenes.</p>
                        </div>
                        <button wire:click="mountAction('descargar_stock')"
                                class="w-full flex items-center justify-center gap-2 py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-xs font-bold text-white rounded-xl shadow-sm hover:shadow transition-all duration-200">
                            <x-heroicon-m-cog-6-tooth class="h-4 w-4" />
                            <span>Configurar y Descargar</span>
                        </button>
                    </div>

                    {{-- 2. Lotes Vencidos --}}
                    <div class="flex flex-col justify-between rounded-2xl border border-gray-100 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-900/50 hover:shadow-md transition">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <x-heroicon-o-clock class="h-5 w-5 text-red-500" />
                                <h3 class="text-sm font-bold text-gray-950 dark:text-white">Productos Vencidos</h3>
                            </div>
                            <p class="text-xs text-gray-500 mb-4 dark:text-gray-400">Listado de productos cuya fecha de
                                vencimiento ya expiró. Necesario para planificar destrucciones o descartes.</p>
                        </div>
                        <button wire:click="mountAction('descargar_vencidos')"
                                class="w-full flex items-center justify-center gap-2 py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-xs font-bold text-white rounded-xl shadow-sm hover:shadow transition-all duration-200">
                            <x-heroicon-m-cog-6-tooth class="h-4 w-4" />
                            <span>Configurar y Descargar</span>
                        </button>
                    </div>

                    {{-- 3. Próximos a Vencer --}}
                    <div class="flex flex-col justify-between rounded-2xl border border-gray-100 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-900/50 hover:shadow-md transition">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <x-heroicon-o-bell-alert class="h-5 w-5 text-orange-500" />
                                <h3 class="text-sm font-bold text-gray-950 dark:text-white">Próximos Vencimientos</h3>
                            </div>
                            <p class="text-xs text-gray-500 mb-4 dark:text-gray-400">Reporte de lotes que vencerán
                                próximamente. Ideal para darles salida rápida y prioritaria.</p>
                        </div>
                        <button wire:click="mountAction('descargar_proximos_vencer')"
                                class="w-full flex items-center justify-center gap-2 py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-xs font-bold text-white rounded-xl shadow-sm hover:shadow transition-all duration-200">
                            <x-heroicon-m-cog-6-tooth class="h-4 w-4" />
                            <span>Configurar y Descargar</span>
                        </button>
                    </div>

                    {{-- 4. Cuarentena --}}
                    <div class="flex flex-col justify-between rounded-2xl border border-gray-100 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-900/50 hover:shadow-md transition">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <x-heroicon-o-shield-check class="h-5 w-5 text-amber-500" />
                                <h3 class="text-sm font-bold text-gray-950 dark:text-white">Productos en Cuarentena</h3>
                            </div>
                            <p class="text-xs text-gray-500 mb-4 dark:text-gray-400">Lotes retenidos temporalmente por
                                revisiones de calidad. Estos productos no se pueden consumir todavía.</p>
                        </div>
                        <button wire:click="mountAction('descargar_cuarentena')"
                                class="w-full flex items-center justify-center gap-2 py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-xs font-bold text-white rounded-xl shadow-sm hover:shadow transition-all duration-200">
                            <x-heroicon-m-cog-6-tooth class="h-4 w-4" />
                            <span>Configurar y Descargar</span>
                        </button>
                    </div>

                    {{-- 5. Valorización Financiera --}}
                    <div class="flex flex-col justify-between rounded-2xl border border-gray-100 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-900/50 hover:shadow-md transition">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <x-heroicon-o-currency-dollar class="h-5 w-5 text-emerald-500" />
                                <h3 class="text-sm font-bold text-gray-950 dark:text-white">Valorización de Almacén</h3>
                            </div>
                            <p class="text-xs text-gray-500 mb-4 dark:text-gray-400">Costo real total de todo el stock
                                actual calculado a partir del valor de compra. Útil para contabilidad del hotel.</p>
                        </div>
                        <button wire:click="mountAction('descargar_valorizacion')"
                                class="w-full flex items-center justify-center gap-2 py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-xs font-bold text-white rounded-xl shadow-sm hover:shadow transition-all duration-200">
                            <x-heroicon-m-cog-6-tooth class="h-4 w-4" />
                            <span>Configurar y Descargar</span>
                        </button>
                    </div>

                    {{-- 6. Rotación --}}
                    <div class="flex flex-col justify-between rounded-2xl border border-gray-100 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-900/50 hover:shadow-md transition">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <x-heroicon-o-arrow-path class="h-5 w-5 text-indigo-500" />
                                <h3 class="text-sm font-bold text-gray-950 dark:text-white">Rotación de Inventario</h3>
                            </div>
                            <p class="text-xs text-gray-500 mb-4 dark:text-gray-400">Análisis del consumo promedio
                                mensual para ver qué productos se gastan más rápido y cuáles están estancados.</p>
                        </div>
                        <button wire:click="mountAction('descargar_rotacion')"
                                class="w-full flex items-center justify-center gap-2 py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-xs font-bold text-white rounded-xl shadow-sm hover:shadow transition-all duration-200">
                            <x-heroicon-m-cog-6-tooth class="h-4 w-4" />
                            <span>Configurar y Descargar</span>
                        </button>
                    </div>

                    {{-- 7. Registro de Mermas --}}
                    <div class="flex flex-col justify-between rounded-2xl border border-gray-100 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-900/50 hover:shadow-md transition text-left">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <x-heroicon-o-trash class="h-5 w-5 text-rose-500" />
                                <h3 class="text-sm font-bold text-gray-950 dark:text-white">Mermas y Pérdidas</h3>
                            </div>
                            <p class="text-xs text-gray-500 mb-4 dark:text-gray-400">Historial completo de productos
                                desechados por daños, robo o fecha de vencimiento expirada en el período.</p>
                        </div>
                        <button wire:click="mountAction('descargar_mermas')"
                                class="w-full flex items-center justify-center gap-2 py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-xs font-bold text-white rounded-xl shadow-sm hover:shadow transition-all duration-200">
                            <x-heroicon-m-cog-6-tooth class="h-4 w-4" />
                            <span>Configurar y Descargar</span>
                        </button>
                    </div>

                </div>
            </div>

        </div>

    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
