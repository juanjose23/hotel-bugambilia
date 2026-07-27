<x-filament-panels::page>
    <div class="space-y-6">

        {{-- CALENDARIO COMPARTIDO --}}
        <x-shared.calendar-grid
            :nombre-mes="$nombreMes"
            :year="$year"
            description="Control visual de órdenes de mantenimiento preventivo y correctivo."
            :days="$days"
            :month="$month"
            :current-year="$year"
            :items="$mantenimientos"
            empty-cell-min-height="min-h-[120px]"
            item-scroll-max-height="max-h-[85px]"
            item-view="filament.resources.activos.partials.calendar-mantenimiento-card"
        />

    </div>
</x-filament-panels::page>
