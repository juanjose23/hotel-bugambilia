@use('App\Models\Inventario\MovimientoStock')

<div class="space-y-6 text-gray-600 dark:text-gray-400">
    <div class="space-y-3">
        <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Inventario Actual en Carrito</h4>

        @livewire('limpieza.carrito-stock-table', ['carritoId' => $record->id])
    </div>

    <div class="space-y-3 pt-4 border-t border-gray-100 dark:border-gray-800">
        <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Historial de Movimientos de este Carrito</h4>

        @php
            $movs = MovimientoStock::with(['producto', 'lote', 'ubicacionOrigen', 'ubicacionDestino', 'creadoPor.persona'])
                ->where(function ($q) use ($record) {
                    $q->where('ubicacion_origen_id', $record->id)
                      ->orWhere('ubicacion_destino_id', $record->id);
                })
                ->latest()
                ->take(15)
                ->get();
        @endphp

        <x-shared.movements-table :movimientos="$movs" />
    </div>
</div>
