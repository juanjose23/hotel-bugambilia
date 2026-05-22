<x-filament-panels::page>
    <div class="space-y-6">

        <div class="rounded-2xl border border-gray-150 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
            <div class="flex items-center gap-2 mb-4 border-b border-gray-100 pb-3 dark:border-gray-700">
                <x-heroicon-o-currency-dollar class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Histórico de Servicios por Precio por Moneda</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Descarga el historial completo de precios de servicios agrupado por categoría, incluyendo vigencia y estado actual.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-2">

                <div class="flex flex-col justify-between rounded-2xl border border-gray-100 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-900/50 hover:shadow-md transition">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <x-heroicon-o-document-text class="h-5 w-5 text-red-500" />
                            <h3 class="text-sm font-bold text-gray-950 dark:text-white">PDF para Impresión</h3>
                        </div>
                        <p class="text-xs text-gray-500 mb-4 dark:text-gray-400">Documento formateado con diseño corporativo, listo para imprimir o archivar.</p>
                    </div>
                    <button wire:click="mountAction('descargar_pdf')"
                            class="w-full flex items-center justify-center gap-2 py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-xs font-bold text-white rounded-xl shadow-sm hover:shadow transition-all duration-200">
                        <x-heroicon-m-cog-6-tooth class="h-4 w-4" />
                        <span>Configurar y Descargar PDF</span>
                    </button>
                </div>

                <div class="flex flex-col justify-between rounded-2xl border border-gray-100 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-900/50 hover:shadow-md transition">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <x-heroicon-o-table-cells class="h-5 w-5 text-emerald-500" />
                            <h3 class="text-sm font-bold text-gray-950 dark:text-white">Exportar a Excel</h3>
                        </div>
                        <p class="text-xs text-gray-500 mb-4 dark:text-gray-400">Hoja de cálculo con todos los precios históricos para análisis y filtrado avanzado.</p>
                    </div>
                    <button wire:click="mountAction('descargar_excel')"
                            class="w-full flex items-center justify-center gap-2 py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-xs font-bold text-white rounded-xl shadow-sm hover:shadow transition-all duration-200">
                        <x-heroicon-m-cog-6-tooth class="h-4 w-4" />
                        <span>Configurar y Descargar Excel</span>
                    </button>
                </div>

            </div>
        </div>

    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
