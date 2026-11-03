import type { Driver } from 'driver.js';
import { CircleHelp } from 'lucide-react';
import { useEffect, useRef } from 'react';
import 'driver.js/dist/driver.css';
import { Button } from '@/modules/shared/components/ui/button';

export interface PasoGuia {
    elemento?: string;
    titulo: string;
    descripcion: string;
}

export function GuiaPasoAPaso({ pasos }: { pasos: PasoGuia[] }) {
    const recorrido = useRef<Driver | null>(null);
    const vigente = useRef(true);
    useEffect(() => {
        vigente.current = true;

        return () => {
            vigente.current = false;
            recorrido.current?.destroy();
        };
    }, []);
    const abrir = async () => {
        const { driver } = await import('driver.js');

        if (!vigente.current) {
            return;
        }

        recorrido.current?.destroy();
        recorrido.current = driver({
            showProgress: true,
            allowClose: true,
            disableActiveInteraction: true,
            nextBtnText: 'Siguiente',
            prevBtnText: 'Anterior',
            doneBtnText: 'Terminar',
            progressText: '{{current}} de {{total}}',
            steps: pasos.map((paso) => ({
                element:
                    paso.elemento &&
                    document.querySelector(paso.elemento)?.getClientRects()
                        .length
                        ? paso.elemento
                        : undefined,
                popover: { title: paso.titulo, description: paso.descripcion },
            })),
        });
        recorrido.current.drive();
    };

    return (
        <Button
            type="button"
            variant="outline"
            size="sm"
            onClick={() => void abrir()}
            aria-label="Ver guía paso a paso"
        >
            <CircleHelp className="mr-2 size-4" />
            Guía paso a paso
        </Button>
    );
}
