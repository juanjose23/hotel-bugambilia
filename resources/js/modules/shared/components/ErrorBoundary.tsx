import { ArrowLeft, Home, RefreshCw, ServerCrash } from 'lucide-react';
import { Component } from 'react';
import type { ReactNode } from 'react';
import { Button } from './ui/button';

interface PropsErrorBoundary {
    children: ReactNode;
    fallback?: ReactNode;
}

interface StateErrorBoundary {
    hasError: boolean;
}

const FallbackDefault = () => (
    <div className="relative flex min-h-[70vh] w-full items-center justify-center bg-background px-4 py-12 text-foreground sm:px-6 lg:px-8">
        <div className="relative z-10 w-full max-w-xl text-center">
            <div className="overflow-hidden rounded-3xl border border-border bg-card p-8 shadow-xl backdrop-blur-xl transition-all sm:p-12">
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
                    <Button
                        type="button"
                        onClick={() => window.location.reload()}
                        className="w-full rounded-full px-6 py-3.5 text-xs font-bold tracking-wider uppercase shadow-lg active:scale-95 sm:w-auto"
                    >
                        <RefreshCw className="h-4 w-4" />
                        Recargar página
                    </Button>

                    <a
                        href="/"
                        className="inline-flex w-full items-center justify-center gap-2 rounded-full border border-border bg-card px-6 py-3.5 text-xs font-bold tracking-wider text-card-foreground uppercase shadow-xs transition-all hover:bg-accent hover:text-accent-foreground active:scale-95 sm:w-auto"
                    >
                        <Home className="h-4 w-4" />
                        Ir al inicio
                    </a>

                    <Button
                        type="button"
                        variant="ghost"
                        onClick={() => window.history.back()}
                        className="w-full rounded-full px-6 py-3.5 text-xs font-bold tracking-wider text-muted-foreground uppercase shadow-xs active:scale-95 sm:w-auto"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        Volver atrás
                    </Button>
                </div>
            </div>

            <div className="mt-8 text-center text-xs font-medium text-muted-foreground">
                Hotel Bugambilias — Servicio y Confort
            </div>
        </div>
    </div>
);

export class ErrorBoundary extends Component<
    PropsErrorBoundary,
    StateErrorBoundary
> {
    constructor(props: PropsErrorBoundary) {
        super(props);
        this.state = { hasError: false };
    }

    static getDerivedStateFromError(): StateErrorBoundary {
        return { hasError: true };
    }

    render() {
        if (this.state.hasError) {
            return this.props.fallback || <FallbackDefault />;
        }

        return this.props.children;
    }
}

export const LimitadorErrores = ErrorBoundary;
export default ErrorBoundary;
