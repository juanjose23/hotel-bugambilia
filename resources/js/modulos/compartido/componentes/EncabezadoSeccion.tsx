import { Award } from 'lucide-react';
import React from 'react';

export interface PropiedadesEncabezadoSeccion {
    titulo: string;
    tituloEnfasis?: string;
    subtitulo?: string;
    badgeIcon?: React.ElementType;
    badgeLabel?: string;
    badgeStyle?: string;
    centrado?: boolean;
    className?: string;
}

export const EncabezadoSeccion: React.FC<PropiedadesEncabezadoSeccion> = ({
    titulo,
    tituloEnfasis,
    subtitulo,
    badgeIcon: BadgeIcon = Award,
    badgeLabel,
    centrado = false,
    className = '',
}) => {
    return (
        <header
            className={`mb-10 font-sans ${centrado ? 'text-center' : ''} ${className}`}
        >
            {badgeLabel && (
                <div
                    className={`inline-flex items-center gap-2 rounded-full border border-bugambilia-500/30 bg-bugambilia-500/10 px-3.5 py-1 text-xs font-bold text-bugambilia-600 dark:text-bugambilia-400`}
                >
                    <BadgeIcon className="h-3.5 w-3.5" />
                    <span>{badgeLabel}</span>
                </div>
            )}

            <h2 className="mt-3 text-2xl font-black tracking-tight text-foreground md:text-3xl">
                {titulo}{' '}
                {tituloEnfasis && (
                    <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                        {tituloEnfasis}
                    </span>
                )}
            </h2>

            {subtitulo && (
                <p className="mt-1.5 text-sm font-medium text-muted-foreground">
                    {subtitulo}
                </p>
            )}
        </header>
    );
};
