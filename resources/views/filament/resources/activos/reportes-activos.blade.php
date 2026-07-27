<x-filament-panels::page>
    <div x-data x-on:open-new-tab.window="window.open($event.detail.url, '_blank')" class="space-y-6">

        <div class="rounded-2xl border border-gray-150 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800 max-w-3xl mx-auto">
            <div class="flex items-center gap-4 mb-6 border-b border-gray-50 dark:border-gray-850 pb-4">
                <div class="p-3 bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 rounded-xl shadow-sm ring-1 ring-primary-100/10">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-950 dark:text-white">Centro de Reportes y Análisis de Activos</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Genera y descarga informes de activos fijos, ubicaciones y mantenimientos en formato PDF.</p>
                </div>
            </div>

            <form wire:submit.prevent="descargarReporte" class="space-y-5 flex-grow flex flex-col justify-between">
                <div class="space-y-4">
                    {{ $this->reportForm }}
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800 mt-6">
                    <x-filament::button type="submit" color="primary" icon="heroicon-o-document-arrow-down" class="hover:scale-[1.02] transition-transform duration-200">
                        Generar y Descargar PDF
                    </x-filament::button>
                </div>
            </form>
        </div>

        {{-- ─── VALORIZACIÓN GRÁFICA ──────────────────────────────────── --}}
        <div class="rounded-2xl border border-gray-150 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800 max-w-3xl mx-auto">
            <div class="flex items-center gap-4 mb-4 border-b border-gray-50 dark:border-gray-850 pb-4">
                <div class="p-3 bg-success-50 dark:bg-success-950/40 text-success-600 dark:text-success-400 rounded-xl shadow-sm ring-1 ring-success-100/10">
                    <x-heroicon-o-banknotes class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-950 dark:text-white">Valorización de Activos</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Distribución del valor neto en libros por categoría de activo.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                @livewire(\App\Filament\Pages\Activos\Widgets\ValorizacionActivosChart::class)
            </div>
        </div>

    </div>
</x-filament-panels::page>
