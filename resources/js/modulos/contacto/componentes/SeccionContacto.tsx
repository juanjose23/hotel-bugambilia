import { usePage } from '@inertiajs/react';
import { Phone, Mail, MapPin, MessageCircle } from 'lucide-react';
import { PortadaHeroGeneral } from '@/modulos/compartido/componentes/PortadaHeroGeneral';
import { Button } from '@/modulos/compartido/ui/boton';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';
import type { PropiedadesSeccionContacto } from '../interfaces/contactoInterfaces';
import FormularioContacto from './FormularioContacto';
import MapaContacto from './MapaContacto';
import PreguntasFrecuentesContacto from './PreguntasFrecuentesContacto';

export const SeccionContacto = ({ hotelInfo }: PropiedadesSeccionContacto) => {
    const pageProps = usePage().props;
    const pageHotel = pageProps.hotel as
        | {
              name?: string;
              telefono?: string;
              email?: string;
              direccion?: string;
          }
        | undefined;

    const telefono =
        hotelInfo?.telefono || pageHotel?.telefono || '+505 8713 6805';
    const email =
        hotelInfo?.email ||
        pageHotel?.email ||
        'recepcion@bugambiliashotel.com';
    const direccion =
        hotelInfo?.direccion ||
        pageHotel?.direccion ||
        'Salida Sur, Estelí, Nicaragua';

    const itemsContacto = [
        {
            icon: MessageCircle,
            title: 'WhatsApp 24/7',
            details: telefono,
            description:
                'Respuesta inmediata de recepción para reservas y consultas',
            actionText: 'Escribir por WhatsApp',
            actionUrl: `https://wa.me/${telefono.replace(/[^0-9]/g, '')}?text=Hola,%20quisiera%20consultar%20disponibilidad`,
            color: 'text-emerald-500 bg-emerald-500/10 border-emerald-500/20',
        },
        {
            icon: Phone,
            title: 'Llamada Directa',
            details: telefono,
            description:
                'Asistencia en tiempo real para eventos y cotizaciones corporativas',
            actionText: 'Llamar a Recepción',
            actionUrl: `tel:${telefono.replace(/[^0-9+]/g, '')}`,
            color: 'text-bugambilia-600 dark:text-bugambilia-400 bg-bugambilia-500/10 border-bugambilia-500/20',
        },
        {
            icon: Mail,
            title: 'Correo Concierge',
            details: email,
            description:
                'Solicitudes especiales y reservaciones de grupo con respuesta en < 2h',
            actionText: 'Enviar Correo',
            actionUrl: `mailto:${email}`,
            color: 'text-bugambilia-600 dark:text-bugambilia-400 bg-bugambilia-500/10 border-bugambilia-500/20',
        },
        {
            icon: MapPin,
            title: 'Ubicación Física',
            details: direccion,
            description: 'Check-in las 24 horas, parqueo privado vigilado',
            actionText: 'Ver en Mapa',
            actionUrl: '#mapa-ubicacion',
            color: 'text-blue-500 bg-blue-500/10 border-blue-500/20',
        },
    ];

    return (
        <section className="min-h-screen bg-background pt-3 pb-12 font-sans md:pt-4 md:pb-16">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                {/* Banner Hero Fotográfico Boutique */}
                <div className="mb-10">
                    <PortadaHeroGeneral
                        imagenFondo="/images/terrace.webp"
                        badgeLabel="Atención & Concierge 24/7"
                        badgeIcon={MessageCircle}
                        badgeStyle="border-bugambilia-500/40 bg-bugambilia-500/20 text-bugambilia-300 dark:text-bugambilia-200"
                        titulo="Estamos a su"
                        tituloEnfasis="Disposición"
                        descripcion="Escríbanos por WhatsApp, llámenos a recepción o envíenos sus consultas. Atendemos sus inquietudes las 24 horas del día."
                        alturaClass="min-h-[220px] sm:min-h-[260px] md:min-h-[300px] rounded-3xl"
                    />
                </div>

                {/* Tarjetas de Canales Directos */}
                <div className="mb-14 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    {itemsContacto.map((item, idx) => {
                        const IconComponent = item.icon;

                        return (
                            <Card
                                key={idx}
                                className="group flex flex-col justify-between rounded-3xl border border-border/80 bg-card p-6 shadow-xs transition-all duration-300 hover:-translate-y-1 hover:border-bugambilia-500/50 hover:shadow-lg"
                            >
                                <CardContent className="flex flex-col gap-4 p-0">
                                    <div
                                        className={`flex size-12 items-center justify-center rounded-2xl border ${item.color}`}
                                    >
                                        <IconComponent className="size-6" />
                                    </div>

                                    <div>
                                        <h3 className="text-base font-black text-foreground">
                                            {item.title}
                                        </h3>
                                        <p className="mt-1 truncate text-sm font-extrabold text-bugambilia-600 dark:text-bugambilia-400">
                                            {item.details}
                                        </p>
                                        <p className="mt-2 text-xs leading-relaxed text-muted-foreground">
                                            {item.description}
                                        </p>
                                    </div>

                                    <Button
                                        asChild
                                        variant="outline"
                                        size="xs"
                                        className="mt-2 w-full rounded-full border-border/80 font-bold hover:border-bugambilia-500/40 hover:bg-bugambilia-500/10 hover:text-bugambilia-600"
                                    >
                                        <a
                                            href={item.actionUrl}
                                            target={
                                                item.actionUrl.startsWith(
                                                    'http',
                                                )
                                                    ? '_blank'
                                                    : '_self'
                                            }
                                            rel="noreferrer"
                                        >
                                            {item.actionText}
                                        </a>
                                    </Button>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>

                {/* Sección Formulario + Información */}
                <div className="mb-16 grid grid-cols-1 gap-10 lg:grid-cols-12">
                    <div className="lg:col-span-7">
                        <FormularioContacto />
                    </div>
                    <div className="lg:col-span-5">
                        <PreguntasFrecuentesContacto />
                    </div>
                </div>

                {/* Mapa Interactivo */}
                <div
                    id="mapa-ubicacion"
                    className="overflow-hidden rounded-3xl border border-border/80 shadow-md"
                >
                    <MapaContacto direccion={direccion} />
                </div>
            </div>
        </section>
    );
};

export default SeccionContacto;
