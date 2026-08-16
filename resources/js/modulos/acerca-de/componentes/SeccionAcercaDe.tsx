import { Link } from '@inertiajs/react';
import {
    ShieldCheck,
    Award,
    Heart,
    Clock,
    Lock,
    BadgeCheck,
    Sparkles,
    CheckCircle2,
    Building2,
    ArrowRight,
} from 'lucide-react';
import { PortadaHeroGeneral } from '@/modulos/compartido/componentes/PortadaHeroGeneral';
import { Button } from '@/modulos/compartido/ui/boton';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';
import type { PropiedadesSeccionAcercaDe } from '../interfaces/acercaDeInterfaces';
import { TarjetaEstadisticaItem } from './secciones/TarjetaEstadisticaItem';

export const SeccionAcercaDe = ({ hotelInfo }: PropiedadesSeccionAcercaDe) => {
    const hotelName = hotelInfo?.name || 'Hotel Bugambilias';
    const fundado = hotelInfo?.fundado || '1989';

    const pilaresConfianza = [
        {
            titulo: 'Mejor Tarifa Garantizada',
            descripcion:
                'Al reservar directamente en nuestro sitio web oficial obtiene el precio mínimo garantizado sin comisiones de intermediarios.',
            icono: ShieldCheck,
            destacado: 'Directo del Hotel',
        },
        {
            titulo: 'Pagos 100% Encritados y Seguros',
            descripcion:
                'Procesamos sus datos bajo estándares de seguridad bancaria SSL de 256 bits y pasarelas verificadas como Stripe.',
            icono: Lock,
            destacado: 'Seguridad Bancaria',
        },
        {
            titulo: 'Atención Personalizada 24/7',
            descripcion:
                'Nuestro equipo multilingüe en Estelí está listo para atenderle en recepción, WhatsApp y atención a habitaciones las 24 horas.',
            icono: Clock,
            destacado: 'Soporte Continuo',
        },
        {
            titulo: 'Confirmación Inmediata por WhatsApp',
            descripcion:
                'Reciba su número de confirmación, itinerario y comprobante digital al instante en su teléfono móvil.',
            icono: CheckCircle2,
            destacado: 'Auto Check-in',
        },
    ];

    const estadisticas = [
        {
            valor: '35+',
            etiqueta: 'Años de Trayectoria',
            subetiqueta: 'Fundado en ' + fundado,
            icono: Award,
        },
        {
            valor: '99%',
            etiqueta: 'Clientes Satisfechos',
            subetiqueta: 'Calificación 5 estrellas',
            icono: Heart,
        },
        {
            valor: '24/7',
            etiqueta: 'Recepción & Concierge',
            subetiqueta: 'Atención continua',
            icono: Clock,
        },
        {
            valor: '100%',
            etiqueta: 'Transacciones Seguras',
            subetiqueta: 'Cifrado SSL bancario',
            icono: ShieldCheck,
        },
    ];

    return (
        <section className="min-h-screen bg-background pt-3 pb-12 font-sans md:pt-4 md:pb-16">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                {/* Banner Hero Fotográfico Boutique */}
                <div className="mb-10">
                    <PortadaHeroGeneral
                        imagenFondo="/images/hero-main.webp"
                        badgeLabel="Nuestra Historia & Compromiso"
                        badgeIcon={Sparkles}
                        badgeStyle="border-bugambilia-500/40 bg-bugambilia-500/20 text-bugambilia-300 dark:text-bugambilia-200"
                        titulo="Hospitalidad Boutique en"
                        tituloEnfasis="Estelí"
                        descripcion="Combinamos la calidez tradicional nicaragüense con los más altos estándares de servicio corporativo y tranquilidad."
                        alturaClass="min-h-[220px] sm:min-h-[260px] md:min-h-[300px] rounded-3xl"
                    />
                </div>

                {/* Pilares de Confianza de Alto Impacto */}
                <div className="mb-16">
                    <div className="mx-auto mb-10 max-w-2xl text-center">
                        <Badge
                            variant="outline"
                            className="mb-2 border-bugambilia-500/30 bg-bugambilia-500/10 text-xs font-extrabold text-bugambilia-600 dark:text-bugambilia-400"
                        >
                            <BadgeCheck className="mr-1.5 size-3.5" />{' '}
                            Estándares de Garantía
                        </Badge>
                        <h2 className="text-3xl font-black text-foreground sm:text-4xl">
                            ¿Por qué reservar con nosotros?
                        </h2>
                        <p className="mt-2 text-xs font-medium text-muted-foreground sm:text-sm">
                            Cuatro razones fundamentales por las que nuestros
                            huéspedes nos eligen año tras año.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        {pilaresConfianza.map((pilar, idx) => {
                            const Icono = pilar.icono;

                            return (
                                <Card
                                    key={idx}
                                    className="group relative flex flex-col justify-between rounded-3xl border border-border/80 bg-card p-6 shadow-xs transition-all duration-300 hover:-translate-y-1 hover:border-bugambilia-500/50 hover:shadow-lg"
                                >
                                    <CardContent className="flex flex-col gap-4 p-0">
                                        <div className="flex size-12 items-center justify-center rounded-2xl bg-bugambilia-500/10 text-bugambilia-600 transition-colors group-hover:bg-bugambilia-600 group-hover:text-white dark:text-bugambilia-400">
                                            <Icono className="size-6" />
                                        </div>
                                        <div>
                                            <Badge
                                                variant="secondary"
                                                className="mb-2 rounded-full text-[10px] font-extrabold text-bugambilia-600 dark:text-bugambilia-400"
                                            >
                                                {pilar.destacado}
                                            </Badge>
                                            <h3 className="text-lg font-black text-foreground">
                                                {pilar.titulo}
                                            </h3>
                                            <p className="mt-2 text-xs leading-relaxed text-muted-foreground">
                                                {pilar.descripcion}
                                            </p>
                                        </div>
                                    </CardContent>
                                </Card>
                            );
                        })}
                    </div>
                </div>

                {/* Sección de Historia & Compromiso Social */}
                <div className="mb-16 grid grid-cols-1 items-center gap-10 lg:grid-cols-2">
                    <div className="relative overflow-hidden rounded-3xl border border-border/80 shadow-xl">
                        <img
                            src="/images/hotel-front.webp"
                            alt="Fachada Hotel Bugambilias"
                            className="h-[380px] w-full object-cover sm:h-[420px]"
                        />
                        <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent" />
                        <div className="absolute right-6 bottom-6 left-6 text-white">
                            <span className="block text-xs font-black tracking-widest text-bugambilia-300 uppercase">
                                Nuestra Sede Principal
                            </span>
                            <span className="text-xl font-black">
                                Salida Sur, Estelí, Nicaragua
                            </span>
                        </div>
                    </div>

                    <div className="flex flex-col gap-6">
                        <div>
                            <Badge
                                variant="outline"
                                className="mb-3 border-bugambilia-500/30 bg-bugambilia-500/10 text-xs font-extrabold text-bugambilia-600 dark:text-bugambilia-400"
                            >
                                <Building2 className="mr-1.5 size-3.5" />{' '}
                                Trayectoria Boutique
                            </Badge>
                            <h2 className="text-3xl font-black text-foreground sm:text-4xl">
                                Más de 3 décadas ofreciendo excelencia en el
                                Norte de Nicaragua
                            </h2>
                        </div>
                        <p className="text-xs leading-relaxed text-muted-foreground sm:text-sm">
                            Desde nuestra fundación en {fundado}, {hotelName} se
                            ha posicionado como el refugio predilecto para
                            ejecutivos, familias y turistas internacionales que
                            buscan comodidad de primera clase con atención
                            cálida e impecable.
                        </p>
                        <div className="flex flex-col gap-3">
                            <div className="flex items-center gap-3">
                                <CheckCircle2 className="size-5 shrink-0 text-emerald-500" />
                                <span className="text-xs font-extrabold text-foreground sm:text-sm">
                                    Ambientes climatizados con conectividad
                                    Wi-Fi de alta velocidad
                                </span>
                            </div>
                            <div className="flex items-center gap-3">
                                <CheckCircle2 className="size-5 shrink-0 text-emerald-500" />
                                <span className="text-xs font-extrabold text-foreground sm:text-sm">
                                    Ubicación estratégica con parqueo privado y
                                    seguridad 24 horas
                                </span>
                            </div>
                            <div className="flex items-center gap-3">
                                <CheckCircle2 className="size-5 shrink-0 text-emerald-500" />
                                <span className="text-xs font-extrabold text-foreground sm:text-sm">
                                    Compromiso con el turismo sostenible y
                                    desarrollo local en Estelí
                                </span>
                            </div>
                        </div>
                        <div>
                            <Button
                                asChild
                                size="lg"
                                className="rounded-full bg-bugambilia-600 font-extrabold text-white hover:bg-bugambilia-700 dark:bg-bugambilia-500 dark:hover:bg-bugambilia-600"
                            >
                                <Link href="/habitaciones" prefetch>
                                    Explorar Catálogo de Habitaciones{' '}
                                    <ArrowRight className="ml-2 size-4" />
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>

                {/* Grilla de Estadísticas */}
                <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    {estadisticas.map((stat, idx) => (
                        <TarjetaEstadisticaItem
                            key={idx}
                            valor={stat.valor}
                            etiqueta={stat.etiqueta}
                            Icono={stat.icono}
                        />
                    ))}
                </div>
            </div>
        </section>
    );
};

export default SeccionAcercaDe;
