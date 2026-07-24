import { Link } from '@inertiajs/react';
import { ChevronLeft } from 'lucide-react';
import React from 'react';

export interface ItemMigaPan {
    label: string;
    href?: string;
}

interface PropiedadesNavegacionMigasPan {
    migas: ItemMigaPan[];
    badge?: string;
    className?: string;
}

export const NavegacionMigasPan: React.FC<PropiedadesNavegacionMigasPan> = ({
    migas = [],
    badge,
    className = '',
}) => {
    if (!migas || migas.length === 0) {
        return null;
    }

    const migaRetorno = migas[0];

    return (
        <div
            className={`border-b border-border/40 bg-card/60 py-3.5 font-sans backdrop-blur-xs ${className}`}
        >
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div className="flex items-center gap-2 text-xs font-semibold text-muted-foreground">
                        {migaRetorno?.href && (
                            <Link
                                href={migaRetorno.href}
                                className="inline-flex items-center gap-1 font-bold text-muted-foreground transition-colors hover:text-foreground"
                            >
                                <ChevronLeft className="h-3.5 w-3.5" />
                                {migaRetorno.label}
                            </Link>
                        )}

                        {migas.slice(1).map((miga, idx) => (
                            <React.Fragment key={idx}>
                                <span className="text-muted-foreground/60">
                                    /
                                </span>
                                {miga.href ? (
                                    <Link
                                        href={miga.href}
                                        className="transition-colors hover:text-foreground"
                                    >
                                        {miga.label}
                                    </Link>
                                ) : (
                                    <span className="max-w-[200px] truncate font-bold text-bugambilia-600 sm:max-w-[300px] dark:text-bugambilia-400">
                                        {miga.label}
                                    </span>
                                )}
                            </React.Fragment>
                        ))}
                    </div>

                    {badge && (
                        <span className="rounded-full bg-bugambilia-500/10 px-3 py-1 text-[11px] font-extrabold text-bugambilia-600 dark:text-bugambilia-400">
                            {badge}
                        </span>
                    )}
                </div>
            </div>
        </div>
    );
};
