import {
    ShieldCheck,
    Clock,
    FileText,
    Info,
    Ban,
    HeartHandshake,
    KeyRound,
    Scale,
} from 'lucide-react';
import React from 'react';

export interface ItemPoliticaData {
    id?: number;
    nombre: string;
    descripcion?: string | null;
    tipo?: string | null;
}

interface PropiedadesSeccionPoliticasCondiciones {
    politicas?: ItemPoliticaData[];
    titulo?: string;
    subtitulo?: string;
    className?: string;
}

const CATEGORIAS_POLITICAS: Array<{
    palabras: string[];
    icono: React.ElementType;
    badge: string;
    estilo: string;
}> = [
    {
        palabras: ['horario', 'check-in', 'check-out', 'registro', 'salida'],
        icono: Clock,
        badge: 'Horarios',
        estilo: 'text-amber-500 bg-amber-500/10 border-amber-500/20',
    },
    {
        palabras: ['cancela', 'reembolso', 'anulaci', 'garantía'],
        icono: ShieldCheck,
        badge: 'Garantía',
        estilo: 'text-emerald-500 bg-emerald-500/10 border-emerald-500/20',
    },
    {
        palabras: ['humo', 'mascota', 'prohibid', 'norma', 'regla'],
        icono: Ban,
        badge: 'Normativa',
        estilo: 'text-rose-500 bg-rose-500/10 border-rose-500/20',
    },
    {
        palabras: ['pago', 'cuenta', 'tarjeta', 'depósito'],
        icono: FileText,
        badge: 'Pagos',
        estilo: 'text-blue-500 bg-blue-500/10 border-blue-500/20',
    },
    {
        palabras: ['menor', 'niño', 'edad', 'supervis', 'familia'],
        icono: HeartHandshake,
        badge: 'Convivencia',
        estilo: 'text-purple-500 bg-purple-500/10 border-purple-500/20',
    },
    {
        palabras: ['llave', 'acceso', 'identificac', 'código'],
        icono: KeyRound,
        badge: 'Seguridad',
        estilo: 'text-violet-500 bg-violet-500/10 border-violet-500/20',
    },
];

function resolverDetallePolitica(nombre: string) {
    const lower = nombre.toLowerCase();

    for (const item of CATEGORIAS_POLITICAS) {
        if (item.palabras.some((p) => lower.includes(p))) {
            return {
                Icono: item.icono,
                badgeLabel: item.badge,
                estilo: item.estilo,
            };
        }
    }

    return {
        Icono: Scale,
        badgeLabel: 'Reglamento',
        estilo: 'text-bugambilia-600 bg-bugambilia-500/10 border-bugambilia-500/20 dark:text-bugambilia-400',
    };
}

export const SeccionPoliticasCondiciones: React.FC<
    PropiedadesSeccionPoliticasCondiciones
> = ({
    politicas = [],
    titulo = 'Políticas & Términos',
    subtitulo = 'Regulaciones oficiales del establecimiento obtenidas en tiempo real',
    className = '',
}) => {
    // Si no hay políticas asignadas en la BD, NO mostrar la sección
    if (!politicas || politicas.length === 0) {
        return null;
    }

    return (
        <section
            className={`border-t border-border/40 bg-background py-12 font-sans md:py-16 ${className}`}
        >
            <div className="container mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <header className="mb-10">
                    <div className="inline-flex items-center gap-2 rounded-full border border-bugambilia-500/30 bg-bugambilia-500/10 px-3.5 py-1 text-xs font-bold text-bugambilia-600 dark:text-bugambilia-400">
                        <Info className="h-3.5 w-3.5" />
                        <span>Reglamento Transparente</span>
                    </div>
                    <h2 className="mt-3 text-2xl font-black tracking-tight text-foreground md:text-3xl">
                        {titulo}{' '}
                        <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                            de Hospedaje
                        </span>
                    </h2>
                    {subtitulo && (
                        <p className="mt-1 text-sm font-medium text-muted-foreground">
                            {subtitulo}
                        </p>
                    )}
                </header>

                <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    {politicas.map((pol, index) => {
                        const { Icono, badgeLabel, estilo } =
                            resolverDetallePolitica(pol.nombre);

                        return (
                            <div
                                key={pol.id || index}
                                className="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-border/80 bg-card p-6 shadow-xs transition-all duration-500 hover:-translate-y-1.5 hover:border-bugambilia-500/50 hover:shadow-xl"
                            >
                                <div className="absolute top-0 left-0 h-1 w-full bg-gradient-to-r from-bugambilia-500 via-amber-500 to-emerald-500 opacity-80" />

                                <div>
                                    <div className="mb-4 flex items-center justify-between">
                                        <div
                                            className={`flex h-11 w-11 items-center justify-center rounded-2xl border transition-all duration-500 group-hover:scale-110 ${estilo}`}
                                        >
                                            <Icono className="h-5 w-5 stroke-[1.8]" />
                                        </div>
                                        <span className="rounded-full border border-border bg-muted/60 px-3 py-1 text-[10px] font-black tracking-wider text-muted-foreground uppercase">
                                            {badgeLabel}
                                        </span>
                                    </div>

                                    <h3 className="text-sm font-black tracking-tight text-foreground transition-colors group-hover:text-bugambilia-600 dark:group-hover:text-bugambilia-400">
                                        {pol.nombre}
                                    </h3>

                                    {pol.descripcion && (
                                        <p className="mt-2 text-xs leading-relaxed font-medium text-muted-foreground">
                                            {pol.descripcion}
                                        </p>
                                    )}
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </section>
    );
};
