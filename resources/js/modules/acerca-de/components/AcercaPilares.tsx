import {
    BedDouble,
    Utensils,
    ShieldCheck,
    Accessibility,
    HeartHandshake,
    Flower2,
} from 'lucide-react';
import type { PilarValor } from '../types';

const PILARES: PilarValor[] = [
    {
        id: '1',
        titulo: 'Atención Familiar: Como en Casa',
        descripcion:
            'Te recibimos con una sonrisa genuina y la calidez norteña. Conocemos tus preferencias para que cada momento sea especial.',
        icono: 'heart-handshake',
    },
    {
        id: '2',
        titulo: 'Silencio & Descanso Reparador',
        descripcion:
            'Habitaciones con camas de alto confort, aire acondicionado silencioso y cortinas blackout para un descanso revitalizante.',
        icono: 'bed',
    },
    {
        id: '3',
        titulo: 'Jardines & Bugambilias Vivas',
        descripcion:
            'Patios coloniales llenos de bugambilias y flora tropical de Estelí. Un espacio sereno para disfrutar un café o una lectura.',
        icono: 'flower',
    },
    {
        id: '4',
        titulo: 'Sabor Local & Café de Altura',
        descripcion:
            'Desayunos con cuajada fresca, tortillas palmeadas y café cosechado en las alturas segovianas de Tisey y Miraflor.',
        icono: 'utensils',
    },
    {
        id: '5',
        titulo: 'Accesibilidad para Todos',
        descripcion:
            'Instalaciones en planta baja, rampas suaves y apoyo dedicado para adultos mayores y personas con movilidad reducida.',
        icono: 'accessibility',
    },
    {
        id: '6',
        titulo: 'Seguridad & Parqueo Privado 24/7',
        descripcion:
            'Estacionamiento cerrado y seguro las 24 horas dentro del hotel, monitoreo constante y total tranquilidad para tu familia.',
        icono: 'shield',
    },
];

const renderIcono = (icono: string) => {
    const className =
        'size-4 text-bugambilia-600 dark:text-bugambilia-400 shrink-0';

    switch (icono) {
        case 'heart-handshake':
            return <HeartHandshake className={className} aria-hidden="true" />;
        case 'bed':
            return <BedDouble className={className} aria-hidden="true" />;
        case 'flower':
            return <Flower2 className={className} aria-hidden="true" />;
        case 'utensils':
            return <Utensils className={className} aria-hidden="true" />;
        case 'accessibility':
            return <Accessibility className={className} aria-hidden="true" />;
        case 'shield':
            return <ShieldCheck className={className} aria-hidden="true" />;
        default:
            return <HeartHandshake className={className} aria-hidden="true" />;
    }
};

export const AcercaPilares = () => {
    return (
        <section
            aria-labelledby="titulo-pilares"
            className="bg-background py-10 md:py-16"
        >
            <div className="container mx-auto px-4 sm:px-6">
                <div className="mx-auto max-w-2xl text-center">
                    <span className="text-xs font-black tracking-widest text-bugambilia-600 uppercase dark:text-bugambilia-400">
                        Nuestra Promesa
                    </span>
                    <h2
                        id="titulo-pilares"
                        className="mt-1 text-2xl font-black tracking-tight text-foreground sm:text-3xl"
                    >
                        Hospitalidad que Marca la Diferencia
                    </h2>
                    <p className="mt-2 text-xs text-muted-foreground sm:text-sm">
                        Diseñado para tu comodidad, descanso y una estancia sin
                        preocupaciones.
                    </p>
                </div>

                {/* En Mobile: Deslizador Horizontal Compacto (Swipe Snap-x). En Tablet/Desktop: Grid */}
                <div className="-mx-4 mt-8 flex snap-x snap-mandatory gap-3 overflow-x-auto px-4 pb-3 sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 sm:pb-0 lg:grid-cols-3">
                    {PILARES.map((pilar) => (
                        <article
                            key={pilar.id}
                            className="group flex w-[240px] shrink-0 snap-center flex-col justify-between rounded-2xl border border-border bg-card p-4.5 shadow-xs transition-all hover:border-bugambilia-500/50 sm:w-auto"
                        >
                            <div>
                                <div className="dark:bg-bugambilia-950/70 inline-flex size-9 items-center justify-center rounded-xl bg-bugambilia-50">
                                    {renderIcono(pilar.icono)}
                                </div>
                                <h3 className="mt-3 text-xs font-black text-foreground sm:text-sm">
                                    {pilar.titulo}
                                </h3>
                                <p className="mt-1.5 line-clamp-3 text-[11px] leading-relaxed text-muted-foreground sm:text-xs">
                                    {pilar.descripcion}
                                </p>
                            </div>
                        </article>
                    ))}
                </div>
            </div>
        </section>
    );
};

export default AcercaPilares;
