import { Link } from '@inertiajs/react';
import {
    Gift,
    CheckCircle2,
    X,
    Calendar,
    ArrowRight,
    ShieldCheck,
    Tag,
} from 'lucide-react';
import React, { useState } from 'react';
import { TarjetaPromocionEspecial } from '@/modules/shared/components/TarjetaPromocionEspecial';
import { Button } from '@/modules/shared/ui/boton';

export interface PromocionItem {
    id: number;
    codigo: string;
    nombre: string;
    descripcion: string;
    badge?: string;
    precio_paquete?: number | null;
    precio_final?: number | null;
    descuento_porcentaje?: number | null;
    descuento_monto?: number | null;
    moneda?: string;
    imagen?: string | null;
    itemsIncluidos?: string[];
    habitacion_slug?: string | null;
    url_reserva?: string | null;
    valido_hasta?: string | null;
}

interface SeccionPromocionesProps {
    promociones?: PromocionItem[];
}

const PROMOCIONES_DEFAULT: PromocionItem[] = [
    {
        id: 101,
        codigo: 'ROMANTICO20',
        nombre: 'Escapada Romántica & Spa',
        descripcion:
            'Disfrute de una estancia inolvidable en nuestra Junior Suite con decoración floral, botella de vino de bienvenida y desayuno artesanal en la habitación.',
        badge: '20% OFF Combo',
        precio_paquete: 450,
        precio_final: 360,
        descuento_porcentaje: 20,
        moneda: '$',
        imagen: '/images/hero-secondary.webp',
        valido_hasta: 'Oferta de Temporada',
        url_reserva: '/habitaciones?promo=ROMANTICO20',
        itemsIncluidos: [
            'Noche en Junior Suite Deluxe',
            'Botella de vino espumoso de bienvenida',
            'Desayuno gourmet servido en la habitación',
            'Late Check-out garantizado hasta las 2:00 PM',
        ],
    },
    {
        id: 102,
        codigo: 'FINDE15',
        nombre: 'Paquete Relax Fin de Semana',
        descripcion:
            'Ideal para desconectarse en familia o pareja. Incluye acceso libre a la piscina, cocteles artesanales y descuento en el restaurante.',
        badge: 'Paquete Especial',
        precio_paquete: 380,
        precio_final: 323,
        descuento_porcentaje: 15,
        moneda: '$',
        imagen: '/images/pool-front-view.webp',
        valido_hasta: 'Cupos Limitados',
        url_reserva: '/habitaciones?promo=FINDE15',
        itemsIncluidos: [
            'Estancia de 2 noches en Habitación Doble',
            'Coctel de bienvenida en la terraza',
            'Desayuno buffet incluido todas las mañanas',
            'Acceso a piscina y áreas verdes',
        ],
    },
    {
        id: 103,
        codigo: 'EJECUTIVO50',
        nombre: 'Plan Ejecutivo & Business',
        descripcion:
            'Diseñado para viajeros de negocios con máxima conectividad de alta velocidad, espacio de trabajo silencioso y menú ejecutivo.',
        badge: 'Cupón Especial',
        precio_paquete: 290,
        precio_final: 240,
        descuento_monto: 50,
        moneda: '$',
        imagen: '/images/main-room.webp',
        valido_hasta: 'Válido Todo el Año',
        url_reserva: '/habitaciones?promo=EJECUTIVO50',
        itemsIncluidos: [
            'Habitación Individual Ejecutiva',
            'Internet Fibra Óptica 300 Mbps Dedicado',
            'Almuerzo o cena ejecutiva en Restaurante Bugambilias',
            'Estacionamiento privado con vigilancia 24h',
        ],
    },
];

const SeccionPromociones: React.FC<SeccionPromocionesProps> = ({
    promociones = [],
}) => {
    const [promoSeleccionada, setPromoSeleccionada] =
        useState<PromocionItem | null>(null);

    const listaPromociones =
        promociones && promociones.length > 0
            ? promociones
            : PROMOCIONES_DEFAULT;

    return (
        <section className="border-b border-border/40 bg-card py-20 font-sans">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                {/* Encabezado */}
                <div className="mb-12 flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
                    <div className="max-w-2xl">
                        <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-3.5 py-1 text-xs font-extrabold tracking-widest text-amber-600 uppercase dark:text-amber-400">
                            <Tag className="h-3.5 w-3.5" />
                            Ofertas, Paquetes & Cupones
                        </div>
                        <h2 className="text-3xl leading-tight font-black tracking-tight text-foreground sm:text-4xl md:text-5xl">
                            Promociones{' '}
                            <span className="font-serif font-normal text-amber-500 italic">
                                Especiales
                            </span>
                        </h2>
                        <p className="mt-2 text-base font-medium text-muted-foreground sm:text-lg">
                            Aproveche nuestros paquetes todo incluido o aplique
                            códigos de descuento exclusivos en sus próximas
                            reservas.
                        </p>
                    </div>
                </div>

                {/* Grilla de Tarjetas Promocionales Profesionales */}
                <div className="grid grid-cols-1 gap-8 md:grid-cols-3">
                    {listaPromociones.map((promo) => (
                        <TarjetaPromocionEspecial
                            key={promo.id}
                            promocion={promo}
                            alVerDetalles={(item) => setPromoSeleccionada(item)}
                        />
                    ))}
                </div>
            </div>

            {/* MODAL DE DETALLES DE PROMOCIÓN */}
            {promoSeleccionada && (
                <div
                    onClick={() => setPromoSeleccionada(null)}
                    className="animate-in fade-in fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-md duration-200"
                >
                    <div
                        onClick={(e) => e.stopPropagation()}
                        className="relative w-full max-w-lg overflow-hidden rounded-3xl border border-border bg-card shadow-2xl"
                    >
                        {/* Cabecera Modal con Imagen */}
                        <div className="relative h-48 bg-muted">
                            <img
                                src={
                                    promoSeleccionada.imagen ||
                                    '/images/hero-main.webp'
                                }
                                alt={promoSeleccionada.nombre}
                                className="h-full w-full object-cover"
                            />
                            <div className="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent" />

                            <button
                                type="button"
                                onClick={() => setPromoSeleccionada(null)}
                                className="absolute top-4 right-4 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-black/60 text-white backdrop-blur-md transition-all hover:bg-black"
                            >
                                <X className="h-4 w-4" />
                            </button>

                            <div className="absolute right-6 bottom-4 left-6 text-white">
                                <span className="mb-1 inline-flex items-center gap-1.5 rounded-full bg-amber-400 px-3 py-1 text-[10px] font-black text-black uppercase">
                                    <Gift className="h-3 w-3" />
                                    {promoSeleccionada.badge ||
                                        'Oferta Exclusiva'}
                                </span>
                                <h3 className="text-xl font-black text-white">
                                    {promoSeleccionada.nombre}
                                </h3>
                            </div>
                        </div>

                        {/* Cuerpo Modal */}
                        <div className="space-y-5 p-6 font-sans">
                            {/* Código Promocional Pill */}
                            <div className="flex items-center justify-between rounded-2xl border border-bugambilia-500/30 bg-bugambilia-500/10 p-4">
                                <div>
                                    <span className="block text-[10px] font-extrabold tracking-wider text-muted-foreground uppercase">
                                        Código de Descuento
                                    </span>
                                    <span className="font-mono text-lg font-black tracking-widest text-bugambilia-600 uppercase dark:text-bugambilia-400">
                                        {promoSeleccionada.codigo}
                                    </span>
                                </div>
                                <span className="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                    <ShieldCheck className="h-3.5 w-3.5" />
                                    Activo
                                </span>
                            </div>

                            {/* Descripción */}
                            <div>
                                <h4 className="mb-1 text-xs font-bold text-muted-foreground uppercase">
                                    Detalles de la Oferta
                                </h4>
                                <p className="text-xs leading-relaxed font-medium text-foreground">
                                    {promoSeleccionada.descripcion}
                                </p>
                            </div>

                            {/* Ítems Incluidos en el Combo */}
                            {promoSeleccionada.itemsIncluidos &&
                                promoSeleccionada.itemsIncluidos.length > 0 && (
                                    <div className="space-y-2">
                                        <h4 className="text-xs font-bold text-muted-foreground uppercase">
                                            Servicios e Instalaciones Incluidas:
                                        </h4>
                                        <div className="grid gap-2 sm:grid-cols-2">
                                            {promoSeleccionada.itemsIncluidos.map(
                                                (item, idx) => (
                                                    <div
                                                        key={idx}
                                                        className="flex items-center gap-2 rounded-xl border border-border/80 bg-background p-2.5 text-xs font-semibold text-foreground"
                                                    >
                                                        <CheckCircle2 className="h-4 w-4 shrink-0 text-emerald-500" />
                                                        <span>{item}</span>
                                                    </div>
                                                ),
                                            )}
                                        </div>
                                    </div>
                                )}

                            {/* Botón de Acción Directa */}
                            <div className="pt-2">
                                <Button
                                    asChild
                                    className="w-full cursor-pointer rounded-2xl bg-bugambilia-600 py-6 text-sm font-black text-white shadow-lg transition-all hover:bg-bugambilia-700"
                                >
                                    <Link
                                        href={`/habitaciones?promo=${promoSeleccionada.codigo}`}
                                    >
                                        <Calendar className="mr-2 h-4 w-4" />
                                        Reservar Ahora con esta Promoción
                                        <ArrowRight className="ml-2 h-4 w-4" />
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </section>
    );
};

export default SeccionPromociones;
