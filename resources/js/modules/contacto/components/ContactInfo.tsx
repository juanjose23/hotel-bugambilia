import { usePage } from '@inertiajs/react';
import {
    MapPin,
    Phone,
    Mail,
    Clock,
    Wifi,
    Car,
    ShieldCheck,
    Sparkles,
} from 'lucide-react';

export default function ContactInfo() {
    const { hotel } = usePage().props;
    const telefono = hotel?.telefono || '+505 8713 6805';
    const email = hotel?.email || 'recepcion@bugambiliashotel.com';

    const blocks = [
        {
            icon: Phone,
            title: 'Teléfono & WhatsApp',
            lines: [telefono, 'Atención telefónica 24/7'],
        },
        {
            icon: Mail,
            title: 'Correo Electrónico',
            lines: [email, 'Respuesta garantizada'],
        },
        {
            icon: MapPin,
            title: 'Ubicación Privilegiada',
            lines: ['Estelí, Nicaragua', 'Salida Sur'],
        },
        {
            icon: Clock,
            title: 'Recepción & Horarios',
            lines: ['Entrada: 14:00', 'Salida: 12:00'],
        },
    ];

    const amenities = [
        { icon: Wifi, text: 'Fibra Óptica Wi-Fi Gratuito' },
        { icon: Car, text: 'Estacionamiento Privado Monitoreado 24/7' },
        { icon: ShieldCheck, text: 'Seguridad & Recepción las 24 Horas' },
    ];

    return (
        <div className="space-y-6 font-sans">
            <div className="space-y-4">
                {blocks.map((block, i) => (
                    <div
                        key={i}
                        className="shadow-airbnb hover:shadow-airbnb-hover rounded-3xl border border-border/80 bg-card p-5 transition-all duration-300 hover:-translate-y-0.5"
                    >
                        <div className="flex items-start gap-4">
                            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-bugambilia-500/20 bg-bugambilia-500/10">
                                <block.icon className="h-5 w-5 text-bugambilia-600 dark:text-bugambilia-400" />
                            </div>
                            <div>
                                <h3 className="mb-1 text-xs font-extrabold tracking-wider text-foreground uppercase">
                                    {block.title}
                                </h3>
                                {block.lines.map((line, j) => (
                                    <p
                                        key={j}
                                        className="text-xs font-medium text-muted-foreground"
                                    >
                                        {line}
                                    </p>
                                ))}
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            <div className="shadow-airbnb rounded-3xl border border-border/80 bg-card p-6">
                <h3 className="mb-3 flex items-center gap-2 text-xs font-extrabold tracking-wider text-foreground uppercase">
                    <Sparkles className="h-4 w-4 text-bugambilia-600 dark:text-bugambilia-400" />
                    Servicios Incluidos en su Visita
                </h3>
                <div className="space-y-2.5">
                    {amenities.map((a, i) => (
                        <div key={i} className="flex items-center gap-2.5">
                            <a.icon className="h-4 w-4 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                            <span className="text-xs font-medium text-muted-foreground">
                                {a.text}
                            </span>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}
