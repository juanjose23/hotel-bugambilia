import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import type { DatosPaginacion } from '@/modules/shared/types';
interface PropiedadesPaginador {
    paginacion?: DatosPaginacion;
    propiedadesSolo?: string[];
}
export const PaginadorPublico = ({
    paginacion,
    propiedadesSolo = [
        'services',
        'rooms',
        'pagination',
        'categorias',
        'selectedCategory',
    ],
}: PropiedadesPaginador) => {
    if (!paginacion || paginacion.last_page <= 1) {
        return null;
    }

    return (
        <div className="mb-16 flex items-center justify-center gap-2 font-sans">
            {paginacion.prev_page_url ? (
                <Link
                    href={paginacion.prev_page_url}
                    preserveScroll
                    only={propiedadesSolo}
                    className="inline-flex cursor-pointer items-center gap-1.5 rounded-full border border-border bg-card px-4 py-2.5 text-xs font-extrabold text-foreground transition-colors hover:border-bugambilia-500"
                >
                    <ChevronLeft className="h-4 w-4" />
                    <span>Anterior</span>
                </Link>
            ) : (
                <span className="inline-flex cursor-not-allowed items-center gap-1.5 rounded-full px-4 py-2.5 text-xs font-semibold text-muted-foreground/40">
                    <ChevronLeft className="h-4 w-4" />
                    <span>Anterior</span>
                </span>
            )}

            <div className="mx-2 hidden items-center gap-1.5 sm:flex">
                {paginacion.links
                    .filter((l) => {
                        const etiqueta = l.label.replace(/<[^>]*>/g, '').trim();

                        return etiqueta && !isNaN(Number(etiqueta));
                    })
                    .map((enlace, idx) => (
                        <Link
                            key={idx}
                            href={enlace.url!}
                            preserveScroll
                            only={propiedadesSolo}
                            className={`flex h-9 w-9 cursor-pointer items-center justify-center rounded-full text-xs font-bold transition-all ${
                                enlace.active
                                    ? 'shadow-airbnb bg-bugambilia-600 text-white'
                                    : 'border border-border bg-card text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            {enlace.label.replace(/<[^>]*>/g, '')}
                        </Link>
                    ))}
            </div>

            {paginacion.next_page_url ? (
                <Link
                    href={paginacion.next_page_url}
                    preserveScroll
                    only={propiedadesSolo}
                    className="inline-flex cursor-pointer items-center gap-1.5 rounded-full border border-border bg-card px-4 py-2.5 text-xs font-extrabold text-foreground transition-colors hover:border-bugambilia-500"
                >
                    <span>Siguiente</span>
                    <ChevronRight className="h-4 w-4" />
                </Link>
            ) : (
                <span className="inline-flex cursor-not-allowed items-center gap-1.5 rounded-full px-4 py-2.5 text-xs font-semibold text-muted-foreground/40">
                    <span>Siguiente</span>
                    <ChevronRight className="h-4 w-4" />
                </span>
            )}
        </div>
    );
};
