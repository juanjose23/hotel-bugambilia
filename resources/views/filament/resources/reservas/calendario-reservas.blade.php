<x-filament-panels::page>
    <div class="space-y-6">

        {{-- FORMULARIO NATIVO DE FILAMENT PARA FILTROS --}}
        <div>
            {{ $this->form }}
        </div>

        {{-- CALENDARIO COMPARTIDO --}}
        <x-shared.calendar-grid
            :nombre-mes="$calendarioData['nombreMes'] ?? ''"
            :year="$calendarioData['year'] ?? ''"
            description="Visualización mensual de reservaciones ocupadas por día de ingreso."
            :days="$calendarioData['days'] ?? []"
            :month="$calendarioData['month'] ?? now()->month"
            :current-year="$calendarioData['year'] ?? now()->year"
            :items="$calendarioData['reservasPorDia'] ?? collect()"
            :legend="[
                ['color' => 'bg-emerald-500', 'label' => 'Confirmada'],
                ['color' => 'bg-sky-500', 'label' => 'En Estancia (Checked In / Out)'],
                ['color' => 'bg-amber-500', 'label' => 'Pendiente'],
                ['color' => 'bg-rose-500', 'label' => 'Cancelada'],
            ]"
            item-count-label="res."
            item-view="filament.resources.reservas.partials.calendar-reserva-card"
        />

    </div>
</x-filament-panels::page>
