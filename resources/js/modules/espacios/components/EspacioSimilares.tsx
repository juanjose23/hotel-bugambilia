import { Link } from '@inertiajs/react';
import { ArrowRight, Building2 } from 'lucide-react';
import type { EspacioSimilarItem } from '../types';

interface PropsEspacioSimilares {
    similares?: EspacioSimilarItem[];
}

export const EspacioSimilares = ({ similares = [] }: PropsEspacioSimilares) => {
    if (similares.length === 0) {
        return null;
    }

    return (
        <div className="mt-14 border-t border-border/60 pt-10 font-sans">
            <div className="mb-6 flex items-center justify-between">
                <div>
                    <div className="inline-flex items-center gap-1.5 text-xs font-black tracking-wider text-bugambilia-600 uppercase dark:text-bugambilia-400">
                        <Building2 className="size-3.5" />
                        <span>Otras Instalaciones</span>
                    </div>
                    <h3 className="mt-1 text-xl font-black text-foreground">
                        Espacios recomendados
                    </h3>
                </div>

                <Link
                    href="/espacios"
                    className="inline-flex items-center gap-1 text-xs font-black text-bugambilia-600 hover:underline dark:text-bugambilia-400"
                >
                    <span>Ver todos</span>
                    <ArrowRight className="size-3.5" />
                </Link>
            </div>

            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {similares.map((esp) => (
                    <Link
                        key={esp.id}
                        href={`/espacios/${esp.slug || esp.id}`}
                        className="group flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-xs transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
                    >
                        <div className="relative aspect-4/3 w-full overflow-hidden bg-muted">
                            <img
                                src={
                                    esp.imagen || '/images/service-events.webp'
                                }
                                alt={esp.nombre}
                                className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                            />
                            {esp.precio !== undefined && esp.precio > 0 && (
                                <span className="absolute right-2.5 bottom-2.5 rounded-full bg-foreground/90 px-2.5 py-0.5 text-[11px] font-black text-background backdrop-blur-md">
                                    {esp.moneda || 'C$'}
                                    {Number(esp.precio).toFixed(2)}
                                </span>
                            )}
                        </div>
                        <div className="p-4">
                            <h4 className="text-sm font-bold text-foreground transition-colors group-hover:text-bugambilia-600 dark:group-hover:text-bugambilia-400">
                                {esp.nombre}
                            </h4>
                        </div>
                    </Link>
                ))}
            </div>
        </div>
    );
};

export default EspacioSimilares;
