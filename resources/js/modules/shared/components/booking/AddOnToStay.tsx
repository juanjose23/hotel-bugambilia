import { Link } from '@inertiajs/react';
import { UtensilsCrossed, Sparkles, Car, Plus } from 'lucide-react';

export interface AddOnItem {
    id: string;
    category: 'restaurante' | 'spa' | 'transporte' | 'tour';
    title: string;
    subtitle: string;
    price?: string;
    details?: string;
    href: string;
    buttonText?: string;
}

export interface AddOnToStayProps {
    checkInDate?: string;
    checkOutDate?: string;
    guestCount?: number;
    title?: string;
    subtitle?: string;
    customItems?: AddOnItem[];
    className?: string;
}

export const AddOnToStay = ({
    checkInDate,
    checkOutDate,
    guestCount = 2,
    title = 'Completa tu estancia en Hotel Bugambilias',
    subtitle = 'Eleva tu experiencia reservando gastronomía, bienestar o traslados antes de tu llegada.',
    customItems,
    className = '',
}: AddOnToStayProps) => {
    const defaultItems: AddOnItem[] = [
        {
            id: 'restaurante-reserva',
            category: 'restaurante',
            title: 'Cena Gourmet en Restaurante',
            subtitle: `Mesa para ${guestCount} ${guestCount === 1 ? 'persona' : 'personas'} · Cocina nicaragüense e internacional`,
            details: checkInDate
                ? `Recomendado para tu noche de llegada (${checkInDate})`
                : 'Mesa preferencial con vista al jardín',
            href: `/restaurante/reservar?fecha=${checkInDate || ''}&comensales=${guestCount}`,
            buttonText: 'Reservar mesa',
        },
        {
            id: 'spa-masaje',
            category: 'spa',
            title: 'Sesión de Masaje Relajante & Spa',
            subtitle:
                'Tratamiento corporal antiestrés de 60 minutos con aceites botánicos',
            details: checkOutDate
                ? `Disfrútalo durante tu estancia antes del check-out (${checkOutDate})`
                : 'Cabina climatizada y aromaterapia incluida',
            href: '/servicios',
            buttonText: 'Ver tratamientos',
        },
        {
            id: 'transporte-hotel',
            category: 'transporte',
            title: 'Traslado Privado (Aeropuerto / Terminal)',
            subtitle:
                'Conductor privado, vehículo ejecutivo y bienvenida personalizada',
            details: 'Coordinación directa según tu horario de arribo',
            href: '/servicios',
            buttonText: 'Coordinar traslado',
        },
    ];

    const items = customItems || defaultItems;

    const getCategoryIcon = (category: AddOnItem['category']) => {
        switch (category) {
            case 'restaurante':
                return <UtensilsCrossed className="size-4 text-amber-500" />;
            case 'spa':
                return <Sparkles className="size-4 text-purple-400" />;
            case 'transporte':
                return <Car className="size-4 text-sky-400" />;
            default:
                return <Sparkles className="size-4 text-primary" />;
        }
    };

    return (
        <div
            className={`rounded-3xl border border-border/80 bg-card/60 p-6 shadow-sm backdrop-blur-md ${className}`}
        >
            <div className="mb-5 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div className="flex items-center gap-1.5 text-xs font-black tracking-wider text-primary uppercase dark:text-rose-400">
                        <Sparkles className="size-3.5" />
                        <span>Añadir a mi estancia</span>
                    </div>
                    <h3 className="mt-1 text-base font-black text-foreground sm:text-lg">
                        {title}
                    </h3>
                    <p className="text-xs text-muted-foreground">{subtitle}</p>
                </div>
            </div>

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {items.map((item) => (
                    <div
                        key={item.id}
                        className="group flex flex-col justify-between rounded-2xl border border-border/70 bg-background/80 p-4 transition-all hover:border-primary/40 hover:shadow-md"
                    >
                        <div>
                            <div className="flex items-center justify-between">
                                <span className="flex size-8 items-center justify-center rounded-xl bg-muted">
                                    {getCategoryIcon(item.category)}
                                </span>
                                {item.price && (
                                    <span className="text-xs font-black text-foreground">
                                        {item.price}
                                    </span>
                                )}
                            </div>

                            <h4 className="mt-3 text-xs font-black text-foreground group-hover:text-primary">
                                {item.title}
                            </h4>
                            <p className="mt-1 text-[11px] leading-relaxed text-muted-foreground">
                                {item.subtitle}
                            </p>
                            {item.details && (
                                <p className="mt-2 text-[10px] font-medium text-muted-foreground/80">
                                    {item.details}
                                </p>
                            )}
                        </div>

                        <div className="mt-4 border-t border-border/50 pt-3">
                            <Link
                                href={item.href}
                                className="inline-flex w-full items-center justify-center gap-1.5 rounded-xl border border-border bg-card py-2 text-xs font-bold text-foreground transition-colors group-hover:border-primary/50 hover:bg-primary hover:text-primary-foreground"
                            >
                                <Plus className="size-3.5" />
                                <span>{item.buttonText || 'Agregar'}</span>
                            </Link>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
};

export default AddOnToStay;
