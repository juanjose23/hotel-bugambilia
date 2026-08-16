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
            className={`w-full max-w-full overflow-hidden border-b border-border/40 bg-card/60 py-3 font-sans backdrop-blur-xs ${className}`}
        >
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <div className="flex max-w-full flex-wrap items-center gap-1.5 overflow-hidden text-xs font-semibold text-muted-foreground">
                        {migaRetorno?.href && (
                            <Link
                                href={migaRetorno.href}
                                className="inline-flex shrink-0 items-center gap-1 font-bold text-muted-foreground transition-colors hover:text-foreground"
                            >
                                <ChevronLeft className="size-3.5" />
                                {migaRetorno.label}
                            </Link>
                        )}

                        {migas.slice(1).map((miga, idx) => (
                            <React.Fragment key={idx}>
                                <span className="shrink-0 text-muted-foreground/60">
                                    /
                                </span>
                                {miga.href ? (
                                    <Link
                                        href={miga.href}
                                        className="shrink-0 transition-colors hover:text-foreground"
                                    >
                                        {miga.label}
                                    </Link>
                                ) : (
                                    <span className="max-w-[130px] truncate font-bold text-bugambilia-600 sm:max-w-[300px] dark:text-bugambilia-400">
                                        {miga.label}
                                    </span>
                                )}
                            </React.Fragment>
                        ))}
                    </div>

                    {badge && (
                        <span className="shrink-0 rounded-full bg-bugambilia-500/10 px-2.5 py-0.5 text-[10px] font-extrabold text-bugambilia-600 dark:text-bugambilia-400">
                            {badge}
                        </span>
                    )}
                </div>
            </div>
        </div>
    );
};

export default NavegacionMigasPan;
