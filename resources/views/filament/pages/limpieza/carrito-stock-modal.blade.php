<div class="space-y-6 text-gray-600 dark:text-gray-400">
    <!-- Infolist Section (Current Stock) -->
    <div class="space-y-3">
        <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Inventario Actual en Carrito</h4>
        
        @php
            $insumos = \App\Models\Inventario\Stock::with(['variante.producto', 'lote'])
                ->where('ubicacion_id', $record->id)
                ->where('cantidad', '>', 0)
                ->get();
        @endphp

        @if($insumos->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">Este carrito no tiene insumos cargados en este momento.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($insumos as $insumo)
                    <div class="p-4 bg-gray-50 dark:bg-gray-950 rounded-lg border border-gray-100 dark:border-gray-800 flex justify-between items-center">
                        <div>
                            <div class="font-bold text-gray-900 dark:text-white">{{ $insumo->variante?->producto?->nombre ?? 'Insumo' }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                @if($insumo->variante?->nombre_variante)
                                    Var: {{ $insumo->variante->nombre_variante }} |
                                @endif
                                Lote: <span class="font-mono">{{ $insumo->lote?->codigo_lote ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-extrabold text-primary-600 dark:text-primary-400">{{ $insumo->cantidad }}</span>
                            <span class="text-xs text-gray-450 dark:text-gray-500 block">unidades</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Movements History Section below -->
    <div class="space-y-3 pt-4 border-t border-gray-100 dark:border-gray-800">
        <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Historial de Movimientos de este Carrito</h4>
        
        @php
            $movs = \App\Models\Inventario\MovimientoStock::with(['producto', 'lote', 'ubicacionOrigen', 'ubicacionDestino', 'creadoPor.persona'])
                ->where(function ($q) use ($record) {
                    $q->where('ubicacion_origen_id', $record->id)
                      ->orWhere('ubicacion_destino_id', $record->id);
                })
                ->latest()
                ->take(15)
                ->get();
        @endphp

        @if($movs->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No se han registrado movimientos para este carrito.</p>
        @else
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
                <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">Fecha</th>
                            <th scope="col" class="px-6 py-3">Tipo</th>
                            <th scope="col" class="px-6 py-3">Insumo</th>
                            <th scope="col" class="px-6 py-3 text-right">Cantidad</th>
                            <th scope="col" class="px-6 py-3">Origen</th>
                            <th scope="col" class="px-6 py-3">Destino</th>
                            <th scope="col" class="px-6 py-3 text-right">Costo Unit.</th>
                            <th scope="col" class="px-6 py-3 text-right">Costo Total</th>
                            <th scope="col" class="px-6 py-3">Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movs as $mov)
                            @php
                                $user = 'N/A';
                                if ($mov->creadoPor) {
                                    $p = $mov->creadoPor->persona;
                                    $user = $p ? trim($p->primer_nombre . ' ' . ($p->personaNatural->primer_apellido ?? '')) : $mov->creadoPor->name;
                                }
                            @endphp
                            <tr class="bg-white border-b dark:bg-gray-950 dark:border-gray-800">
                                <td class="px-6 py-4 whitespace-nowrap text-xs">{{ $mov->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded {{ $mov->cantidad < 0 ? 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300' : 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300' }}">
                                        {{ $mov->tipo }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs font-medium text-gray-900 dark:text-white">{{ $mov->producto?->nombre ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-xs">{{ $mov->cantidad }}</td>
                                <td class="px-6 py-4 text-xs">{{ $mov->ubicacionOrigen?->nombre ?? '-' }}</td>
                                <td class="px-6 py-4 text-xs">{{ $mov->ubicacionDestino?->nombre ?? '-' }}</td>
                                <td class="px-6 py-4 text-right text-xs">${{ number_format((float) $mov->costo_unitario, 2) }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-xs text-gray-900 dark:text-white">${{ number_format((float) $mov->costo_total, 2) }}</td>
                                <td class="px-6 py-4 text-xs">{{ $user }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
