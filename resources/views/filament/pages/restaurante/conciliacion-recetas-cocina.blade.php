<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-7">
            @foreach($this->diagnostico['resumen'] as $estado => $total)
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ str_replace('_', ' ', ucfirst($estado)) }}</div>
                    <div class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">{{ $total }}</div>
                </div>
            @endforeach
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Plato</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Ingrediente</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Stock</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Material bruto</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Detalle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($this->diagnostico['items'] as $item)
                            <tr>
                                <td class="px-4 py-3">
                                    <span class="rounded-md px-2 py-1 text-xs font-semibold
                                        {{ ($item['estado'] ?? '') === 'ok' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300' }}">
                                        {{ str_replace('_', ' ', (string) ($item['estado'] ?? '')) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $item['plato'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $item['ingrediente'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ number_format((float) ($item['disponible'] ?? 0), 2) }} /
                                    {{ number_format((float) ($item['requerido'] ?? 0), 2) }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ $item['bruto'] ?? '—' }}
                                    @if(isset($item['bruto_necesario']))
                                        <span class="block text-xs text-gray-500">
                                            {{ number_format((float) $item['bruto_disponible'], 2) }} /
                                            {{ number_format((float) $item['bruto_necesario'], 2) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $item['detalle'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No hay platos activos para diagnosticar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
