import { MessageCircle, Phone } from 'lucide-react';
import { usePropiedadesPagina } from '@/modules/shared/hooks/usePropiedadesPagina';
interface PropiedadesBotonWhatsApp {
    nombreItem: string;
    codigoItem?: string;
    tipo?: 'habitación' | 'servicio' | 'espacio' | 'habitacion';
}
export const BotonReservaWhatsApp = ({
    nombreItem,
    codigoItem = '',
    tipo = 'habitación',
}: PropiedadesBotonWhatsApp) => {
    const { hotel } = usePropiedadesPagina();
    const telefono = hotel?.telefono || '+505 8713 6805';
    const whatsappNum = telefono.replace(/[^0-9]/g, '');
    const mensaje = encodeURIComponent(
        `Hola, me interesa solicitar la ${tipo}: ${nombreItem}${codigoItem ? ` (Código: ${codigoItem})` : ''}. ¿Tienen disponibilidad?`,
    );
    const enlaceWhatsapp = `https://wa.me/${whatsappNum}?text=${mensaje}`;

    return (
        <div className="shadow-airbnb-hover space-y-4 rounded-3xl border border-border/80 bg-card p-6 font-sans sm:p-8">
            <h3 className="text-lg font-black text-foreground">
                Solicitar esta {tipo}
            </h3>
            <p className="text-xs leading-relaxed text-muted-foreground">
                Consulte disponibilidad y precios con atención personalizada
                enviando un mensaje directo a nuestra recepción.
            </p>

            <a
                href={enlaceWhatsapp}
                target="_blank"
                rel="noopener noreferrer"
                className="shadow-airbnb hover:shadow-airbnb-hover inline-flex w-full cursor-pointer items-center justify-center gap-2.5 rounded-full bg-emerald-600 py-4 text-xs font-extrabold tracking-wider text-white uppercase transition-all duration-300 hover:scale-[1.02] hover:bg-emerald-700"
            >
                <MessageCircle className="h-4 w-4 fill-white" />
                <span>Solicitar vía WhatsApp</span>
            </a>

            <a
                href={`tel:${telefono.replace(/[^0-9+]/g, '')}`}
                className="inline-flex w-full items-center justify-center gap-2 rounded-full border border-border/80 bg-card py-3.5 text-xs font-extrabold tracking-wider text-foreground uppercase transition-all duration-200 hover:bg-muted"
            >
                <Phone className="h-4 w-4 text-bugambilia-600 dark:text-bugambilia-400" />
                <span>Llamar a Recepción ({telefono})</span>
            </a>
        </div>
    );
};
