@php
    use Filament\Support\Enums\Alignment;
    use Filament\Support\Icons\Heroicon;
@endphp

<div
    x-data="{
        targetUrl: '',
        open(url) {
            this.targetUrl = url;
            $dispatch('open-modal', { id: 'unsaved-changes-modal' });
        },
        discard() {
            window.__bypassFilamentUnsavedChanges = true;
            setTimeout(() => {
                window.__bypassFilamentUnsavedChanges = false;
            }, 1000);

            const dest = this.targetUrl;
            $dispatch('close-modal', { id: 'unsaved-changes-modal' });

            if (dest === 'HISTORY_BACK') {
                window.history.back();
            } else if (dest) {
                if (window.Livewire && typeof window.Livewire.navigate === 'function') {
                    window.Livewire.navigate(dest);
                } else {
                    window.location.href = dest;
                }
            }
        }
    }"
    x-on:open-unsaved-changes-modal.window="open($event.detail.url)"
    x-on:discard-unsaved-changes.window="discard()"
>
    <x-filament::modal
        id="unsaved-changes-modal"
        :icon="Heroicon::OutlinedExclamationTriangle"
        icon-color="warning"
        width="md"
        alignment="start"
        :footer-actions-alignment="Alignment::End"
    >
        <x-slot name="heading">
            Cambios sin guardar
        </x-slot>

        <x-slot name="description">
            Tienes modificaciones pendientes en este formulario. Si decides abandonar la página ahora, todos los cambios no guardados se perderán permanentemente.
        </x-slot>

        <x-slot name="footerActions">
            <x-filament::button
                color="gray"
                :icon="Heroicon::OutlinedPencilSquare"
                x-on:click="$dispatch('close-modal', { id: 'unsaved-changes-modal' })"
            >
                Continuar editando
            </x-filament::button>

            <x-filament::button
                color="danger"
                :icon="Heroicon::OutlinedArrowRightOnRectangle"
                x-on:click="discard()"
            >
                Descartar y salir
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</div>

<script>
    (function () {
        // Bloquear completamente beforeunload para que el navegador NUNCA muestre su diálogo nativo feo
        const originalAddEventListener = window.addEventListener;
        window.addEventListener = function (type, listener, options) {
            if (type === 'beforeunload') {
                return;
            }
            return originalAddEventListener.call(this, type, listener, options);
        };

        try {
            Object.defineProperty(window, 'onbeforeunload', {
                get() { return null; },
                set(_) {},
                configurable: true
            });
        } catch (e) {}

        window.__bypassFilamentUnsavedChanges = false;

        const watchedComponents = new Set();

        const isComponentDirty = ($wire) => {
            try {
                if (typeof window.jsMd5 === 'function' && $wire && $wire.data && $wire.savedDataHash) {
                    return window.jsMd5(JSON.stringify($wire.data).replace(/\\/g, '')) !== $wire.savedDataHash;
                }
            } catch (e) {
                return false;
            }
            return false;
        };

        const hasAnyUnsavedChanges = () => {
            if (window.__bypassFilamentUnsavedChanges) {
                return false;
            }

            for (const comp of watchedComponents) {
                if (comp.$wire?.__instance?.effects?.redirect) {
                    continue;
                }
                if (isComponentDirty(comp.$wire)) {
                    return true;
                }
            }

            return false;
        };

        // Interceptar navegación interna SPA de Livewire
        document.addEventListener('livewire:navigate', (event) => {
            if (window.__bypassFilamentUnsavedChanges) {
                return;
            }

            if (hasAnyUnsavedChanges()) {
                event.preventDefault();
                const targetUrl = event.detail?.url || '';
                window.dispatchEvent(new CustomEvent('open-unsaved-changes-modal', {
                    detail: { url: targetUrl }
                }));
            }
        });

        // Interceptar clics en botones de "Cancelar" del formulario (en fase de captura antes de history.back)
        document.addEventListener('click', (event) => {
            if (window.__bypassFilamentUnsavedChanges) {
                return;
            }

            const btn = event.target.closest('button, a');
            if (!btn) return;

            const onClickAttr = btn.getAttribute('x-on:click') || '';
            const text = (btn.textContent || '').trim().toLowerCase();
            const isCancelButton = onClickAttr.includes('history.back') || text === 'cancelar';

            if (isCancelButton && hasAnyUnsavedChanges()) {
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();

                window.dispatchEvent(new CustomEvent('open-unsaved-changes-modal', {
                    detail: { url: 'HISTORY_BACK' }
                }));
            }
        }, true);

        // Registro de componentes Livewire con alertas de Filament
        const customSetup = function ({ body, resolveLivewireComponentUsing, $wire }) {
            const entry = { $wire, resolve: resolveLivewireComponentUsing };
            watchedComponents.add(entry);

            if ($wire?.__instance?.addCleanup) {
                $wire.__instance.addCleanup(() => {
                    watchedComponents.delete(entry);
                });
            }
        };

        window.setUpFilamentSpaModeUnsavedDataChangesAlert = customSetup;
        window.setUpFilamentUnsavedDataChangesAlert = customSetup;
        window.setUpFilamentUnsavedActionChangesAlert = function () {};

        try {
            Object.defineProperty(window, 'setUpFilamentSpaModeUnsavedDataChangesAlert', {
                get() { return customSetup; },
                set(_) {},
                configurable: true
            });
            Object.defineProperty(window, 'setUpFilamentUnsavedDataChangesAlert', {
                get() { return customSetup; },
                set(_) {},
                configurable: true
            });
            Object.defineProperty(window, 'setUpFilamentUnsavedActionChangesAlert', {
                get() { return function () {}; },
                set(_) {},
                configurable: true
            });
        } catch (e) {}
    })();
</script>
