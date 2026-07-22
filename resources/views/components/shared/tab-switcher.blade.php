@props([
    'activeTab' => '',
    'tabs' => [],
])

<div {{ $attributes->class(['space-y-4']) }}>
    {{-- Botonera compacta — solo visible en <md --}}
    <div class="flex md:hidden rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
        @foreach ($tabs as $tab)
            @php
                $rawIcon = $tab['icon'] ?? null;
                $iconName = filled($rawIcon) ? (str_starts_with((string) $rawIcon, 'heroicon-') ? (string) $rawIcon : 'heroicon-o-' . $rawIcon) : null;
            @endphp
            <button
                wire:click="$set('activeTab', '{{ $tab['id'] }}')"
                class="flex-1 flex flex-row items-center justify-center gap-1.5 py-2.5 px-2 text-xs font-medium transition-colors
                    @if ($activeTab === $tab['id'])
                        bg-{{ $tab['activeColor'] ?? 'success' }}-600 text-white
                    @else
                        bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700
                    @endif
                    @if (! $loop->first) border-l border-gray-200 dark:border-gray-700 @endif"
            >
                @if ($iconName)
                    <x-dynamic-component :component="$iconName" class="w-4 h-4 shrink-0" />
                @endif
                <span>{{ $tab['label'] }}</span>
            </button>
        @endforeach
    </div>

    {{-- Tabs de Filament — solo visible en md+ --}}
    <div class="hidden md:block">
        <x-filament::tabs :label="$attributes->get('label', 'Opciones')">
            @foreach ($tabs as $tab)
                @php
                    $rawIcon = $tab['icon'] ?? null;
                    $iconName = filled($rawIcon) ? (str_starts_with((string) $rawIcon, 'heroicon-') ? (string) $rawIcon : 'heroicon-o-' . $rawIcon) : null;
                @endphp
                <x-filament::tabs.item
                    wire:click="$set('activeTab', '{{ $tab['id'] }}')"
                    :active="$activeTab === $tab['id']"
                    :icon="$iconName"
                >
                    {{ $tab['label'] }}
                </x-filament::tabs.item>
            @endforeach
        </x-filament::tabs>
    </div>
</div>
