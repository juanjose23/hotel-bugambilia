import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import type { PaginacionData } from '@/modules/shared/types';

interface PaginadorPublicoProps {
    pagination: PaginacionData;
    onlyProps?: string[];
}

export default function PaginadorPublico({
    pagination,
    onlyProps = [
        'services',
        'rooms',
        'pagination',
        'categorias',
        'selectedCategory',
    ],
}: PaginadorPublicoProps) {
    if (!pagination || pagination.last_page <= 1) {
        return null;
    }

    return (
        <div className="mb-16 flex items-center justify-center gap-2 font-sans">
            {/* Botón Anterior */}
            {pagination.prev_page_url ? (
                <Link
                    href={pagination.prev_page_url}
                    preserveScroll
                    only={onlyProps}
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

            {/* Píldoras de Números de Página */}
            <div className="mx-2 hidden items-center gap-1.5 sm:flex">
                {pagination.links
                    .filter((l) => {
                        const label = l.label.replace(/<[^>]*>/g, '').trim();

                        return label && !isNaN(Number(label));
                    })
                    .map((link, idx) => (
                        <Link
                            key={idx}
                            href={link.url!}
                            preserveScroll
                            only={onlyProps}
                            className={`flex h-9 w-9 cursor-pointer items-center justify-center rounded-full text-xs font-bold transition-all ${
                                link.active
                                    ? 'shadow-airbnb bg-bugambilia-600 text-white'
                                    : 'border border-border bg-card text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            {link.label.replace(/<[^>]*>/g, '')}
                        </Link>
                    ))}
            </div>

            {/* Botón Siguiente */}
            {pagination.next_page_url ? (
                <Link
                    href={pagination.next_page_url}
                    preserveScroll
                    only={onlyProps}
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
}
