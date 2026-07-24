import { AlertTriangle, RefreshCw } from 'lucide-react';
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
    <div className="flex min-h-[400px] flex-col items-center justify-center px-4 text-center">
        <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-red-500/10">
            <AlertTriangle className="h-8 w-8 text-red-500" />
        </div>
        <h2 className="mb-2 text-xl font-black text-gray-900 dark:text-white">
            Algo salió mal
        </h2>
        <p className="mb-6 max-w-md text-sm text-gray-500">
            Ha ocurrido un error inesperado. Por favor, intente recargar la
            página.
        </p>
        <button
            onClick={() => window.location.reload()}
            className="inline-flex items-center gap-2 rounded-full bg-bugambilia-600 px-6 py-3 text-xs font-bold text-white uppercase transition-colors hover:bg-bugambilia-700"
        >
            <RefreshCw className="h-4 w-4" />
            Recargar página
        </button>
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
