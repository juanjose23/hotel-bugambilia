import { Link } from '@inertiajs/react';
import React from 'react';

export interface ItemSimilarData {
    id: number;
    slug?: string;
    nombre: string;
    tipo?: string;
    precio?: number;
    moneda?: string;
    imagen?: string;
}

interface PropiedadesGrillaItemsSimilares {
    items: ItemSimilarData[];
    baseRoute: string;
    titulo?: string;
    tituloEnfasis?: string;
    className?: string;
}

const formatearPrecio = (val?: number) => {
    if (val === undefined || val === null) {
        return '0.00';
    }

    return Number(val).toLocaleString('es-NI', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

export const GrillaItemsSimilares: React.FC<
    PropiedadesGrillaItemsSimilares
> = ({
    items = [],
    baseRoute,
    titulo = 'Opciones',
    tituloEnfasis = 'Similares',
    className = '',
}) => {
    if (!items || items.length === 0) {
        return null;
    }

    return (
        <section
            className={`w-full max-w-full overflow-x-hidden border-t border-border/50 bg-card/60 py-16 font-sans ${className}`}
        >
            <div className="container mx-auto max-w-full px-4 sm:px-6 lg:px-8">
                <header className="mb-8">
                    <h2 className="text-2xl font-black tracking-tight text-foreground md:text-3xl">
                        {titulo}{' '}
                        <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                            {tituloEnfasis}
                        </span>
                    </h2>
                </header>

                <div className="w-full max-w-full overflow-hidden">
                    <div className="no-scrollbar flex w-full snap-x snap-mandatory gap-4 overflow-x-auto pb-4 sm:grid sm:grid-cols-2 sm:gap-6 sm:overflow-visible sm:pb-0 lg:grid-cols-3">
                        {items.map((item) => {
                            const targetSlug = item.slug || item.id;
                            const targetUrl = `${baseRoute}/${targetSlug}`;

                            return (
                                <article
                                    key={item.id}
                                    className="group relative flex w-[82vw] max-w-[300px] shrink-0 snap-center flex-col justify-between overflow-hidden rounded-3xl border border-border/80 bg-background transition-all duration-300 hover:-translate-y-1 hover:border-bugambilia-500/40 hover:shadow-xl sm:w-auto sm:max-w-none sm:shrink"
                                >
                                    <div>
                                        <Link
                                            href={targetUrl}
                                            className="relative block aspect-4/3 overflow-hidden bg-muted/40"
                                        >
                                            <img
                                                src={
                                                    item.imagen ||
                                                    '/images/main-room.webp'
                                                }
                                                alt={item.nombre}
                                                className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                            />
                                        </Link>

                                        <div className="p-5">
                                            {item.tipo && (
                                                <span className="mb-1 block text-[10px] font-extrabold tracking-wider text-bugambilia-600 uppercase dark:text-bugambilia-400">
                                                    {item.tipo}
                                                </span>
                                            )}

                                            <h3 className="mb-2 text-sm font-extrabold text-foreground transition-colors group-hover:text-bugambilia-600 dark:group-hover:text-bugambilia-400">
                                                {item.nombre}
                                            </h3>
                                        </div>
                                    </div>

                                    <div className="flex items-center justify-between border-t border-border/40 px-5 py-3.5">
                                        <span className="text-base font-black text-foreground">
                                            {item.precio && item.precio > 0
                                                ? `${item.moneda || '$'} ${formatearPrecio(item.precio)}`
                                                : 'Acceso Libre'}
                                        </span>

                                        <Link
                                            href={targetUrl}
                                            className="text-xs font-bold text-bugambilia-600 transition-colors hover:underline dark:text-bugambilia-400"
                                        >
                                            Ver Detalles →
                                        </Link>
                                    </div>
                                </article>
                            );
                        })}
                    </div>
                </div>
            </div>
        </section>
    );
};
