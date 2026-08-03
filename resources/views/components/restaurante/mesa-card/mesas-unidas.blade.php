@props([
    'mesa',
    'esSecundariaUnida',
    'esPrincipalConUnidas',
    'meta',
    'mesasUnidas',
    'motivoUnion',
    'codigoReserva',
])

@if ($esSecundariaUnida || $esPrincipalConUnidas)
    <section
        class="mb-4 flex items-start justify-between gap-3 rounded-xl border border-stone-200 bg-stone-50 px-3 py-2.5 dark:border-stone-700 dark:bg-stone-800/60"
    >
        <div class="flex min-w-0 items-start gap-2">
            <x-filament::icon
                icon="heroicon-o-link"
                class="mt-0.5 h-4 w-4 shrink-0 text-stone-500 dark:text-stone-400"
            />

            <div class="min-w-0">
                @if ($esSecundariaUnida)
                    <p
                        class="truncate text-[10px] font-semibold uppercase tracking-wide text-stone-700 dark:text-stone-200"
                    >
                        Unida a
                        {{ $meta['mesa_principal_nombre']
                            ?? 'mesa principal' }}
                    </p>
                @else
                    <p
                        class="text-[10px] font-semibold uppercase tracking-wide text-stone-700 dark:text-stone-200"
                    >
                        Mesa principal &middot;
                        {{ count($mesasUnidas) }}
                        anexadas
                    </p>
                @endif

                @if ($motivoUnion === 'reservacion')
                    <p
                        class="mt-0.5 text-[9px]
                               text-gray-500 dark:text-gray-400"
                    >
                        Reserva
                        {{ $codigoReserva ?: 'sin código' }}
                    </p>
                @endif
            </div>
        </div>

        <button
            type="button"
            wire:click="separarMesas({{ $mesa->id }})"
            wire:loading.attr="disabled"
            wire:target="separarMesas({{ $mesa->id }})"
            class="inline-flex h-8 w-8 shrink-0 items-center
                   justify-center rounded-lg text-stone-500
                   transition-colors
                   hover:bg-stone-200 hover:text-stone-900
                   focus:outline-none focus:ring-2
                   focus:ring-stone-500/30
                   disabled:cursor-not-allowed
                   disabled:opacity-50
                   dark:hover:bg-stone-700 dark:hover:text-white"
            title="Desvincular mesas"
            aria-label="Desvincular mesas"
        >
            <x-filament::icon
                icon="heroicon-o-x-mark"
                class="h-4 w-4"
            />
        </button>
    </section>
@endif
