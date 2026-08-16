import {
    Building2,
    MapPin,
    Users,
    Maximize,
    Star,
    ShieldCheck,
    Sparkles,
} from 'lucide-react';
import { Badge } from '@/modulos/compartido/ui/insignia';

interface PropiedadesCabeceraDetalleEspacio {
    nombre: string;
    descripcion?: string;
    tipoLabel?: string;
    tipo?: string;
    ubicacion?: string;
    capacidad?: number;
    metrosCuadrados?: number | string;
}

export const CabeceraDetalleEspacio = ({
    nombre,
    descripcion,
    tipoLabel,
    tipo,
    ubicacion,
    capacidad = 10,
    metrosCuadrados,
}: PropiedadesCabeceraDetalleEspacio) => {
    return (
        <div className="flex flex-col gap-4 font-sans">
            {/* Badges Principales */}
            <div className="flex flex-wrap items-center gap-2">
                <Badge
                    variant="outline"
                    className="border-bugambilia-500/30 bg-bugambilia-500/10 px-3.5 py-1 text-xs font-extrabold text-bugambilia-600 dark:text-bugambilia-400"
                >
                    <Building2 className="mr-1.5 size-3.5" />
                    {tipoLabel || tipo}
                </Badge>

                {ubicacion && (
                    <Badge
                        variant="outline"
                        className="border-border bg-muted/40 px-3.5 py-1 text-xs font-bold text-muted-foreground"
                    >
                        <MapPin className="mr-1.5 size-3.5 text-bugambilia-500" />
                        {ubicacion}
                    </Badge>
                )}

                <Badge
                    variant="outline"
                    className="border-emerald-500/30 bg-emerald-500/10 px-3.5 py-1 text-xs font-extrabold text-emerald-600 dark:text-emerald-400"
                >
                    <Users className="mr-1.5 size-3.5" />
                    Hasta {capacidad} personas
                </Badge>

                {metrosCuadrados && (
                    <Badge
                        variant="outline"
                        className="border-border bg-muted/50 px-3.5 py-1 text-xs font-extrabold text-foreground"
                    >
                        <Maximize className="mr-1.5 size-3.5 text-bugambilia-500" />
                        {metrosCuadrados} m² de área
                    </Badge>
                )}

                <div className="ml-auto flex items-center gap-1.5 rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-bold text-amber-600 dark:text-amber-400">
                    <Star className="size-3.5 fill-amber-500 text-amber-500" />
                    <span>5.0 Excelente</span>
                </div>
            </div>

            {/* Título Principal Estilo Luxury Boutique */}
            <div className="space-y-2">
                <h1 className="text-3xl font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">
                    {nombre}
                </h1>

                {descripcion && (
                    <p className="max-w-4xl text-sm leading-relaxed font-medium text-muted-foreground md:text-base">
                        {descripcion}
                    </p>
                )}
            </div>

            {/* Promesas de Servicio Rápidas */}
            <div className="flex flex-wrap items-center gap-4 border-y border-border/50 py-3 text-xs font-extrabold text-muted-foreground">
                <div className="flex items-center gap-1.5 text-foreground">
                    <Sparkles className="size-4 text-bugambilia-500" />
                    <span>Infraestructura Boutique</span>
                </div>
                <div className="flex items-center gap-1.5 text-foreground">
                    <ShieldCheck className="size-4 text-emerald-500" />
                    <span>Confirmación Directa sin Comisiones</span>
                </div>
            </div>
        </div>
    );
};

export default CabeceraDetalleEspacio;
