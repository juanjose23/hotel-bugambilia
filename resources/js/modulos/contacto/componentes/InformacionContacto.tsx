import { usePage } from '@inertiajs/react';
import {
    MapPin,
    Phone,
    Mail,
    Clock,
    Wifi,
    Car,
    ShieldCheck,
    BadgeCheck,
} from 'lucide-react';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';
import { TarjetaBloqueContacto } from './secciones/TarjetaBloqueContacto';

export default function InformacionContacto() {
    const pageProps = usePage().props;
    const hotel = pageProps.hotel as
        { telefono?: string; email?: string } | undefined;
    const telefono = hotel?.telefono || '+505 8713 6805';
    const email = hotel?.email || 'recepcion@bugambiliashotel.com';

    const bloques = [
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

    const serviciosIncluidos = [
        { icon: Wifi, text: 'Fibra Óptica Wi-Fi Gratuito' },
        { icon: Car, text: 'Estacionamiento Privado Monitoreado 24/7' },
        { icon: ShieldCheck, text: 'Seguridad & Recepción las 24 Horas' },
    ];

    return (
        <div className="flex flex-col gap-6 font-sans">
            <div className="flex flex-col gap-4">
                {bloques.map((bloque, index) => (
                    <TarjetaBloqueContacto
                        key={index}
                        Icono={bloque.icon}
                        titulo={bloque.title}
                        lineas={bloque.lines}
                    />
                ))}
            </div>

            <Card className="rounded-3xl border-border/80 bg-card p-6">
                <CardContent className="p-0">
                    <h3 className="mb-3 flex items-center gap-2 text-xs font-extrabold tracking-wider text-foreground uppercase">
                        <BadgeCheck className="size-4 text-bugambilia-600 dark:text-bugambilia-400" />
                        Servicios Incluidos en su Visita
                    </h3>
                    <div className="flex flex-col gap-2.5">
                        {serviciosIncluidos.map((servicio, index) => (
                            <div
                                key={index}
                                className="flex items-center gap-2.5"
                            >
                                <servicio.icon className="size-4 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                                <span className="text-xs font-medium text-muted-foreground">
                                    {servicio.text}
                                </span>
                            </div>
                        ))}
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
