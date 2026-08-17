<x-filament-panels::page>
    <div x-data x-on:open-new-tab.window="window.open($event.detail.url || $event.detail[0]?.url, '_blank')" class="w-full space-y-6">

        {{-- Grid Principal de 2 Columnas --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- Columna Izquierda: Formulario de Generación de Reportes --}}
            <div class="lg:col-span-7 xl:col-span-7 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-4 mb-6 border-b border-gray-100 dark:border-gray-800 pb-4">
                    <div class="p-3 bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 rounded-2xl shadow-xs ring-1 ring-primary-500/10">
                        <x-heroicon-o-document-chart-bar class="w-7 h-7" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-950 dark:text-white">Centro de Reportes de Activos Fijos</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Seleccione el tipo de informe y configure los parámetros de filtrado para exportar en PDF.</p>
                    </div>
                </div>

                <form wire:submit.prevent="descargarReporte" class="space-y-6">
                    <div class="space-y-4">
                        {{ $this->reportForm }}
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <x-filament::button type="submit" color="primary" icon="heroicon-o-document-arrow-down" class="hover:scale-[1.01] transition-transform duration-200 shadow-md">
                            Generar y Descargar PDF
                        </x-filament::button>
                    </div>
                </form>
            </div>

            {{-- Columna Derecha: Valorización Gráfica y Resumen --}}
            <div class="lg:col-span-5 xl:col-span-5 space-y-6">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-3 mb-4 border-b border-gray-100 dark:border-gray-800 pb-3">
                        <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-xl">
                            <x-heroicon-o-banknotes class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-950 dark:text-white">Valorización por Categoría</h3>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Distribución de activos en libros</p>
                        </div>
                    </div>

                    <div>
                        @livewire(\App\Filament\Pages\Activos\Widgets\ValorizacionActivosChart::class)
                    </div>
                </div>
            </div>

        </div>

    </div>
</x-filament-panels::page>
