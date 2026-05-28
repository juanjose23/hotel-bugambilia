<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mt-4">
        
        {{-- Inventario y Asignaciones --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6 flex flex-col h-full hover:shadow-md transition duration-200">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 rounded-lg">
                    <x-heroicon-o-cube class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Inventario General</h3>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 flex-grow">
                Reporte completo de todos los activos fijos registrados en el sistema, con filtros por estado y ubicación.
            </p>
            <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
                {{ $this->inventarioGeneralAction }}
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6 flex flex-col h-full hover:shadow-md transition duration-200">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 bg-success-50 dark:bg-success-500/10 text-success-600 dark:text-success-400 rounded-lg">
                    <x-heroicon-o-map-pin class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Activos por Ubicación</h3>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 flex-grow">
                Agrupa los activos según su asignación actual (habitaciones, áreas comunes, bodegas).
            </p>
            <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
                {{ $this->porUbicacionAction }}
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6 flex flex-col h-full hover:shadow-md transition duration-200">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 bg-info-50 dark:bg-info-500/10 text-info-600 dark:text-info-400 rounded-lg">
                    <x-heroicon-o-document-text class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Hoja de Habitación</h3>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 flex-grow">
                Genera el inventario de activos fijos asignados a una habitación o espacio en particular.
            </p>
            <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
                {{ $this->hojaHabitacionAction }}
            </div>
        </div>

        {{-- Espacios / Áreas Comunes --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6 flex flex-col h-full hover:shadow-md transition duration-200">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 bg-warning-50 dark:bg-warning-500/10 text-warning-600 dark:text-warning-400 rounded-lg">
                    <x-heroicon-o-building-storefront class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Activos por Espacio</h3>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 flex-grow">
                Lista todos los activos fijos asignados a cada espacio o área común (restaurante, salones, gimnasio, spa, etc.).
            </p>
            <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
                {{ $this->espaciosAsignadosAction }}
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6 flex flex-col h-full hover:shadow-md transition duration-200">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 bg-info-50 dark:bg-info-500/10 text-info-600 dark:text-info-400 rounded-lg">
                    <x-heroicon-o-squares-2x2 class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Ficha de Espacio</h3>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 flex-grow">
                Genera la hoja de inventario detallada de un espacio específico con todos sus activos asignados y firmas de control.
            </p>
            <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
                {{ $this->fichaEspacioAction }}
            </div>
        </div>

        {{-- Mantenimiento --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6 flex flex-col h-full hover:shadow-md transition duration-200">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 bg-warning-50 dark:bg-warning-500/10 text-warning-600 dark:text-warning-400 rounded-lg">
                    <x-heroicon-o-wrench-screwdriver class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">En Mantenimiento</h3>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 flex-grow">
                Lista todos los activos que se encuentran actualmente en reparación o mantenimiento.
            </p>
            <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
                {{ $this->enMantenimientoAction }}
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6 flex flex-col h-full hover:shadow-md transition duration-200">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 bg-danger-50 dark:bg-danger-500/10 text-danger-600 dark:text-danger-400 rounded-lg">
                    <x-heroicon-o-clock class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Mantenimientos Vencidos</h3>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 flex-grow">
                Reporte de todos los mantenimientos cuya fecha programada ya pasó y siguen pendientes.
            </p>
            <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
                {{ $this->manttosVencidosAction }}
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6 flex flex-col h-full hover:shadow-md transition duration-200">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 rounded-lg">
                    <x-heroicon-o-shield-check class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Garantías Próximas</h3>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 flex-grow">
                Encuentra los activos cuyas garantías están a punto de vencer en los próximos días.
            </p>
            <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
                {{ $this->garantiasAction }}
            </div>
        </div>

        {{-- Auditoría y Control --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6 flex flex-col h-full hover:shadow-md transition duration-200">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-lg">
                    <x-heroicon-o-arrow-path-rounded-square class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Historial de Movimientos</h3>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 flex-grow">
                Línea de tiempo completa de asignaciones, mantenimientos y bajas de un activo específico.
            </p>
            <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
                {{ $this->historialAction }}
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6 flex flex-col h-full hover:shadow-md transition duration-200">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 bg-danger-50 dark:bg-danger-500/10 text-danger-600 dark:text-danger-400 rounded-lg">
                    <x-heroicon-o-trash class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Activos de Baja</h3>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 flex-grow">
                Listado histórico de todos los activos fijos que han sido dados de baja en el hotel.
            </p>
            <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
                {{ $this->bajasAction }}
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6 flex flex-col h-full hover:shadow-md transition duration-200">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 bg-warning-50 dark:bg-warning-500/10 text-warning-600 dark:text-warning-400 rounded-lg">
                    <x-heroicon-o-magnifying-glass class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Extraviados / Sin Asignar</h3>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 flex-grow">
                Reportes rápidos para localizar activos extraviados o sin asignación vigente.
            </p>
            <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-800">
                {{ $this->sinAsignacionAction }}
                {{ $this->extraviadosAction }}
            </div>
        </div>

    </div>
</x-filament-panels::page>
