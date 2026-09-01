import { ShieldCheck, Info } from 'lucide-react';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/modules/shared/components/ui/accordion';
import type { PoliticaItem } from '../types';

interface PropsServicioPoliticas {
    politicas?: PoliticaItem[];
}

const POLITICAS_DEFAULT: PoliticaItem[] = [
    {
        id: 1,
        nombre: 'Condiciones de Reserva & Horarios',
        descripcion:
            'Los servicios de restaurante, eventos y cabina de relajación requieren coordinación previa sujeta a disponibilidad de horario del hotel.',
    },
    {
        id: 2,
        nombre: 'Cancelación y Reembolsos',
        descripcion:
            'Para modificaciones o cancelaciones de servicios privados o eventos, por favor notificar con al menos 24 horas de anticipación.',
    },
];

export const ServicioPoliticas = ({
    politicas = [],
}: PropsServicioPoliticas) => {
    const listaPoliticas = politicas.length > 0 ? politicas : POLITICAS_DEFAULT;

    return (
        <div className="mt-12 font-sans">
            <div className="mb-4 flex items-center gap-2">
                <ShieldCheck className="size-4 text-bugambilia-500" />
                <h3 className="text-base font-black text-foreground">
                    Políticas & Términos del Servicio
                </h3>
            </div>

            <Accordion className="w-full space-y-3">
                {listaPoliticas.map((pol, idx) => (
                    <AccordionItem
                        key={pol.id || idx}
                        value={`pol-${idx}`}
                        className="rounded-2xl border border-border bg-card px-5 shadow-xs"
                    >
                        <AccordionTrigger className="py-4 text-xs font-bold text-foreground hover:no-underline sm:text-sm">
                            <div className="flex items-center gap-2">
                                <Info className="size-3.5 text-muted-foreground" />
                                <span>{pol.nombre}</span>
                            </div>
                        </AccordionTrigger>
                        <AccordionContent className="pb-4 text-xs leading-relaxed text-muted-foreground">
                            {pol.descripcion}
                        </AccordionContent>
                    </AccordionItem>
                ))}
            </Accordion>
        </div>
    );
};

export default ServicioPoliticas;
