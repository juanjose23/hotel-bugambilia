import { Link } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';

interface AccesoRapidoCardProps {
    titulo: string;
    descripcion: string;
    href: string;
    icono: LucideIcon;
    badge?: string;
    external?: boolean;
}

export const AccesoRapidoCard = ({
    titulo,
    descripcion,
    href,
    icono: Icono,
    badge,
    external = false,
}: AccesoRapidoCardProps) => {
    const Contenido = (
        <div className="group flex flex-col justify-between rounded-3xl border border-border/70 bg-card p-5 transition-all duration-300 hover:-translate-y-1 hover:border-primary/40 hover:shadow-md hover:shadow-primary/5 sm:p-6">
            <div className="space-y-3">
                <div className="flex items-center justify-between">
                    <div className="flex size-12 items-center justify-center rounded-2xl bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-white">
                        <Icono className="size-6" />
                    </div>
                    {badge && (
                        <span className="rounded-full bg-secondary px-2.5 py-0.5 text-[11px] font-bold text-muted-foreground">
                            {badge}
                        </span>
                    )}
                </div>
                <div>
                    <h4 className="font-bold text-foreground transition-colors group-hover:text-primary">
                        {titulo}
                    </h4>
                    <p className="mt-1 text-xs leading-relaxed text-muted-foreground">
                        {descripcion}
                    </p>
                </div>
            </div>
        </div>
    );

    if (external) {
        return (
            <a href={href} target="_blank" rel="noreferrer">
                {Contenido}
            </a>
        );
    }

    return <Link href={href}>{Contenido}</Link>;
};
