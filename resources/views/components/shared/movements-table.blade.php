@props([
    'movimientos' => null,
])

<div {{ $attributes->class(['overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800']) }}>
    @if (! $movimientos || $movimientos->isEmpty())
        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
            No se han registrado movimientos.
        </p>
    @else
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
                @foreach ($movimientos as $mov)
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
                        <td class="px-6 py-4 text-xs">{{ $mov->usuario_nombre ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
