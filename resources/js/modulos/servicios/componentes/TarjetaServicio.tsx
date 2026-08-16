import { Sparkles, Check, Plus, Minus } from 'lucide-react';
import React from 'react';
import {
    Tarjeta,
    TarjetaCabecera,
    TarjetaTitulo,
    TarjetaPie,
} from '@/modulos/compartido/ui/tarjeta';
import { formatearNumero } from '@/modulos/compartido/utilidades/formato';

export interface ServicioAdicionalItem {
    id: number;
    nombre: string;
    descripcion?: string;
    precio: number;
    moneda?: string;
    categoria?: string;
    imagen?: string;
}

interface PropiedadesTarjetaServicio {
    servicio: ServicioAdicionalItem;
    seleccionado: boolean;
    cantidad?: number;
    onToggle: (servicio: ServicioAdicionalItem) => void;
    onCambiarCantidad?: (servicioId: number, nuevaCantidad: number) => void;
}

export function TarjetaServicio({
    servicio,
    seleccionado,
    cantidad = 1,
    onToggle,
    onCambiarCantidad,
}: PropiedadesTarjetaServicio) {
    return (
        <Tarjeta
            className={`group relative flex flex-col justify-between transition-all duration-300 ${
                seleccionado
                    ? 'border-primary bg-primary/5 ring-2 ring-primary/20'
                    : 'border-border/60 bg-card hover:border-border'
            }`}
        >
            <TarjetaCabecera className="flex flex-row items-start justify-between gap-3">
                <div className="flex flex-col gap-1">
                    <div className="flex items-center gap-2">
                        <span className="flex size-7 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <Sparkles className="size-3.5" />
                        </span>
                        <TarjetaTitulo className="text-base font-bold text-foreground">
                            {servicio.nombre}
                        </TarjetaTitulo>
                    </div>
                    {servicio.descripcion && (
                        <p className="line-clamp-2 text-xs text-muted-foreground">
                            {servicio.descripcion}
                        </p>
                    )}
                </div>

                <button
                    type="button"
                    onClick={() => onToggle(servicio)}
                    className={`flex size-7 shrink-0 items-center justify-center rounded-full transition-colors ${
                        seleccionado
                            ? 'bg-primary text-primary-foreground'
                            : 'border border-border bg-background text-muted-foreground hover:border-primary'
                    }`}
                >
                    <Check
                        className={`size-4 ${seleccionado ? 'opacity-100' : 'opacity-0'}`}
                    />
                </button>
            </TarjetaCabecera>

            <TarjetaPie className="flex items-center justify-between border-t border-border/40 pt-3">
                <span className="text-sm font-extrabold text-foreground">
                    {servicio.moneda || '$'} {formatearNumero(servicio.precio)}
                </span>

                {seleccionado && onCambiarCantidad && (
                    <div className="flex items-center gap-2 rounded-full border border-border bg-background px-2 py-1">
                        <button
                            type="button"
                            onClick={() =>
                                onCambiarCantidad(
                                    servicio.id,
                                    Math.max(1, cantidad - 1),
                                )
                            }
                            className="text-muted-foreground hover:text-foreground"
                        >
                            <Minus className="size-3.5" />
                        </button>
                        <span className="w-4 text-center text-xs font-bold">
                            {cantidad}
                        </span>
                        <button
                            type="button"
                            onClick={() =>
                                onCambiarCantidad(servicio.id, cantidad + 1)
                            }
                            className="text-muted-foreground hover:text-foreground"
                        >
                            <Plus className="size-3.5" />
                        </button>
                    </div>
                )}
            </TarjetaPie>
        </Tarjeta>
    );
}

export default TarjetaServicio;
