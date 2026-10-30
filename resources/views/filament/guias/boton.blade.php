@if ($guia)
    <div class="flex items-center gap-2" wire:ignore>
        <x-filament::button type="button" color="gray" icon="heroicon-o-question-mark-circle"
            data-hotel-guide="{{ json_encode($guia, JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) }}">
            Guía paso a paso
        </x-filament::button>
        <span data-guide-feedback role="status" aria-live="polite" class="text-xs text-danger-600 dark:text-danger-400"></span>
    </div>
@endif
