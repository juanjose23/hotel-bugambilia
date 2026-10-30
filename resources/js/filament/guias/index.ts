import type { Driver, DriveStep } from 'driver.js';
import 'driver.js/dist/driver.css';
import '../../../css/filament/guias.css';

type Paso = { titulo: string; descripcion: string; selector?: string };
type Guia = {
    id: string;
    titulo: string;
    pasos: Paso[];
    dialogos?: Record<string, Guia>;
};

function esObjeto(value: unknown): value is Record<string, unknown> {
    return typeof value === 'object' && value !== null;
}

function esGuia(value: unknown): value is Guia {
    return (
        esObjeto(value) &&
        typeof value.id === 'string' &&
        typeof value.titulo === 'string' &&
        Array.isArray(value.pasos) &&
        value.pasos.length > 0 &&
        value.pasos.every(
            (paso: unknown) =>
                esObjeto(paso) &&
                typeof paso.titulo === 'string' &&
                typeof paso.descripcion === 'string' &&
                (paso.selector === undefined ||
                    typeof paso.selector === 'string'),
        )
    );
}

function escapar(value: string): string {
    const element = document.createElement('span');
    element.textContent = value;

    return element.innerHTML;
}

function visible(element: Element): element is HTMLElement {
    return (
        element instanceof HTMLElement &&
        element.getClientRects().length > 0 &&
        getComputedStyle(element).visibility !== 'hidden'
    );
}

// Filament conserva el formulario abierto; solo cedemos el foco mientras se lee la guía.
function suspenderFocoModal(root: Element | null | undefined): () => void {
    const modal = root?.closest('.fi-modal');
    const alpine = (
        window as Window & {
            Alpine?: { $data: (element: Element) => Record<string, unknown> };
        }
    ).Alpine;

    if (!modal || !alpine) {
        return () => {};
    }

    const estado = alpine.$data(modal);
    const anterior = estado.isTrapActive;

    if (typeof anterior !== 'boolean') {
        return () => {};
    }

    estado.isTrapActive = false;

    return () => {
        if (modal.isConnected && estado.isOpen) {
            estado.isTrapActive = anterior;
        }
    };
}

function instalar(): () => void {
    let activo: Driver | undefined;
    let generacion = 0;
    let observador: MutationObserver | undefined;
    let frame = 0;
    let origen: HTMLElement | null = null;
    let restaurarFocoModal = () => {};
    const restaurarModal = () => {
        restaurarFocoModal();
        restaurarFocoModal = () => {};
    };

    const cerrar = () => {
        generacion++;
        observador?.disconnect();
        cancelAnimationFrame(frame);
        activo?.destroy();
        restaurarModal();
        activo = undefined;
    };

    const abrir = async (boton: HTMLElement, id?: string) => {
        cerrar();
        const turno = generacion;
        const feedback = boton.parentElement?.querySelector(
            '[data-guide-feedback]',
        );

        if (feedback) {
            feedback.textContent = '';
        }

        try {
            const datos: unknown = JSON.parse(boton.dataset.hotelGuide ?? '{}');

            if (!esGuia(datos)) {
                throw new Error('Guía no disponible');
            }

            let guia = datos;

            if (id) {
                const dialogos: unknown = datos.dialogos;

                if (!esObjeto(dialogos) || !esGuia(dialogos[id])) {
                    return;
                }

                guia = dialogos[id];
            }

            const modales = [
                ...document.querySelectorAll(
                    '[role="dialog"], .fi-modal-window',
                ),
            ].filter(visible);
            const root = id
                ? modales.at(-1)
                : document.querySelector('.fi-page');

            if (id && !root) {
                return;
            }

            const pasos: DriveStep[] = guia.pasos.flatMap((paso) => {
                const element = paso.selector
                    ? [
                          ...(root ?? document).querySelectorAll(paso.selector),
                      ].find(visible)
                    : undefined;

                if (paso.selector && !element) {
                    return [];
                }

                return [
                    {
                        element,
                        popover: {
                            title: escapar(paso.titulo),
                            description: escapar(paso.descripcion),
                        },
                    },
                ];
            });

            if (!pasos.length) {
                return;
            }

            origen =
                document.activeElement instanceof HTMLElement
                    ? document.activeElement
                    : boton;
            boton.setAttribute('aria-busy', 'true');
            const { driver } = await import('driver.js');

            if (
                turno !== generacion ||
                !boton.isConnected ||
                (id && root && !visible(root))
            ) {
                return;
            }

            restaurarFocoModal = suspenderFocoModal(root);
            await new Promise<void>((resolve) => queueMicrotask(resolve));

            if (turno !== generacion) {
                restaurarModal();

                return;
            }

            activo = driver({
                steps: pasos,
                animate: !matchMedia('(prefers-reduced-motion: reduce)')
                    .matches,
                smoothScroll: false,
                disableActiveInteraction: true,
                overlayClickBehavior: 'close',
                popoverClass: 'hotel-guia',
                showProgress: true,
                progressText: '{{current}} de {{total}}',
                nextBtnText: 'Siguiente',
                prevBtnText: 'Anterior',
                doneBtnText: 'Terminar guía',
                onPopoverRender: ({ wrapper, closeButton, nextButton }) => {
                    wrapper.setAttribute('role', 'dialog');
                    wrapper.setAttribute('aria-label', guia.titulo);
                    wrapper.setAttribute('aria-modal', 'true');
                    closeButton.setAttribute('aria-label', 'Cerrar guía');
                    nextButton.focus({ preventScroll: true });
                },
                onDestroyed: () => {
                    observador?.disconnect();
                    cancelAnimationFrame(frame);
                    restaurarModal();
                    activo = undefined;

                    if (origen?.isConnected) {
                        origen.focus({ preventScroll: true });
                    }
                },
            });
            activo.drive();

            if (root) {
                observador = new MutationObserver(() => {
                    cancelAnimationFrame(frame);
                    frame = requestAnimationFrame(() => {
                        const element = activo?.getActiveElement();

                        if (
                            element &&
                            (!element.isConnected || !visible(element))
                        ) {
                            cerrar();
                        } else {
                            activo?.refresh();
                        }
                    });
                });
                observador.observe(root, { childList: true, subtree: true });
            }
        } catch {
            restaurarModal();

            if (feedback) {
                feedback.textContent =
                    'No se pudo abrir la guía. Vuelve a intentarlo.';
            }
        } finally {
            boton.removeAttribute('aria-busy');
        }
    };

    const click = (event: MouseEvent) => {
        const boton =
            event.target instanceof Element
                ? event.target.closest<HTMLElement>('[data-hotel-guide]')
                : null;

        if (boton) {
            event.preventDefault();
            void abrir(boton);
        }
    };
    const modal = (event: Event) => {
        if (
            !(event instanceof CustomEvent) ||
            !esObjeto(event.detail) ||
            typeof event.detail.id !== 'string'
        ) {
            return;
        }

        const boton = document.querySelector<HTMLElement>('[data-hotel-guide]');

        if (boton) {
            void abrir(boton, event.detail.id);
        }
    };
    const teclado = (event: KeyboardEvent) => {
        if (!activo?.isActive()) {
            return;
        }

        const popover = document.querySelector('.driver-popover');

        if (event.key === 'Escape') {
            event.preventDefault();
            event.stopImmediatePropagation();
            cerrar();

            return;
        }

        if (event.key === 'Tab') {
            const botones = [...(popover?.querySelectorAll('button') ?? [])]
                .filter(visible)
                .filter(
                    (b) =>
                        !b.disabled &&
                        !b.classList.contains('driver-popover-btn-disabled'),
                );
            const index = botones.findIndex(
                (boton) => boton === document.activeElement,
            );
            const next = event.shiftKey
                ? index <= 0
                    ? botones.length - 1
                    : index - 1
                : (index + 1) % botones.length;
            event.preventDefault();
            event.stopImmediatePropagation();
            botones[next]?.focus();
        }

        if (
            (event.key === 'Enter' || event.key === ' ') &&
            !popover?.contains(event.target as Node)
        ) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    };
    document.addEventListener('click', click);
    document.addEventListener('hotel:guia', modal);
    document.addEventListener('livewire:navigating', cerrar);
    window.addEventListener('keydown', teclado, true);

    return () => {
        cerrar();
        document.removeEventListener('click', click);
        document.removeEventListener('hotel:guia', modal);
        document.removeEventListener('livewire:navigating', cerrar);
        window.removeEventListener('keydown', teclado, true);
    };
}

const contexto = window as Window & { hotelGuiasDispose?: () => void };
contexto.hotelGuiasDispose?.();
contexto.hotelGuiasDispose = instalar();

if (import.meta.hot) {
    import.meta.hot.dispose(() => contexto.hotelGuiasDispose?.());
}
