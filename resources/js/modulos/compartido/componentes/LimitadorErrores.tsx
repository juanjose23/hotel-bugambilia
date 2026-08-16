import { ArrowLeft, Home, RefreshCw, ServerCrash } from 'lucide-react';
import { Component } from 'react';
import type { ReactNode } from 'react';

interface PropiedadesLimitadorErrores {
    children: ReactNode;
    respaldo?: ReactNode;
}

interface EstadoLimitadorErrores {
    hayError: boolean;
}

const FallbackPorDefecto = () => (
    <div className="relative flex min-h-[70vh] w-full items-center justify-center bg-background px-4 py-12 text-foreground sm:px-6 lg:px-8">
        {/* Glow decorativo del sistema */}
        <div className="pointer-events-none absolute inset-0 flex items-center justify-center overflow-hidden opacity-30">
            <div className="h-[400px] w-[400px] rounded-full bg-gradient-to-tr from-bugambilia-400/20 via-primary/20 to-purple-500/20 blur-3xl" />
        </div>

        <div className="relative z-10 w-full max-w-xl text-center">
            <div className="luxury-glass shadow-airbnb-hover overflow-hidden rounded-3xl border border-border p-8 backdrop-blur-xl transition-all sm:p-12">
                <div className="mb-6 flex justify-center">
                    <span className="inline-flex items-center gap-2 rounded-full border border-border bg-muted px-4 py-1.5 text-xs font-black tracking-widest text-muted-foreground uppercase shadow-xs">
                        <span className="h-2 w-2 rounded-full bg-gradient-to-r from-bugambilia-500 to-rose-500" />
                        Error de Renderizado
                    </span>
                </div>

                <div className="mb-6 flex justify-center">
                    <div className="flex h-20 w-20 items-center justify-center rounded-3xl bg-bugambilia-500/10 text-bugambilia-600 shadow-inner dark:text-bugambilia-300">
                        <ServerCrash className="h-10 w-10" />
                    </div>
                </div>

                <h2 className="mb-3 text-2xl font-black tracking-tight text-foreground sm:text-3xl">
                    Algo salió mal en esta sección
                </h2>

                <p className="mx-auto mb-8 max-w-md text-sm leading-relaxed text-muted-foreground sm:text-base">
                    Ha ocurrido un error inesperado durante el renderizado. Por
                    favor, intente recargar la página.
                </p>

                <div className="flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">
                    <button
                        type="button"
                        onClick={() => window.location.reload()}
                        className="bugambilia-gradient inline-flex w-full items-center justify-center gap-2 rounded-full px-6 py-3.5 text-xs font-bold tracking-wider text-white uppercase shadow-lg transition-all hover:opacity-90 active:scale-95 sm:w-auto"
                    >
                        <RefreshCw className="h-4 w-4" />
                        Recargar página
                    </button>

                    <a
                        href="/"
                        className="inline-flex w-full items-center justify-center gap-2 rounded-full border border-border bg-card px-6 py-3.5 text-xs font-bold tracking-wider text-card-foreground uppercase shadow-xs transition-all hover:bg-accent hover:text-accent-foreground active:scale-95 sm:w-auto"
                    >
                        <Home className="h-4 w-4" />
                        Ir al inicio
                    </a>

                    <button
                        type="button"
                        onClick={() => window.history.back()}
                        className="inline-flex w-full items-center justify-center gap-2 rounded-full border border-border bg-muted px-6 py-3.5 text-xs font-bold tracking-wider text-muted-foreground uppercase shadow-xs transition-all hover:bg-secondary hover:text-secondary-foreground active:scale-95 sm:w-auto"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        Volver atrás
                    </button>
                </div>
            </div>

            <div className="mt-8 text-center text-xs font-medium text-muted-foreground">
                Hotel Bugambilias — Servicio y Confort
            </div>
        </div>
    </div>
);

export class LimitadorErrores extends Component<
    PropiedadesLimitadorErrores,
    EstadoLimitadorErrores
> {
    constructor(propiedades: PropiedadesLimitadorErrores) {
        super(propiedades);
        this.state = { hayError: false };
    }

    static getDerivedStateFromError(): EstadoLimitadorErrores {
        return { hayError: true };
    }

    render() {
        if (this.state.hayError) {
            return this.props.respaldo || <FallbackPorDefecto />;
        }

        return this.props.children;
    }
}
