import { Plus, Minus, Check, Loader2 } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/modules/shared/components/ui/button';
import { Textarea } from '@/modules/shared/components/ui/textarea';
import type { CatalogoServicioItemData } from '../../types';

interface CatalogoServicioItemProps {
    servicio: CatalogoServicioItemData;
    onSolicitar: (servicioId: number, cantidad: number, notas?: string) => void;
    isSubmitting?: boolean;
}

export const CatalogoServicioItem = ({
    servicio,
    onSolicitar,
    isSubmitting = false,
}: CatalogoServicioItemProps) => {
    const [cantidad, setCantidad] = useState(1);
    const [notas, setNotas] = useState('');
    const [mostrarNotas, setMostrarNotas] = useState(false);

    const incrementar = () => setCantidad((c) => Math.min(50, c + 1));
    const decrementar = () => setCantidad((c) => Math.max(1, c - 1));

    const handleSolicitar = () => {
        onSolicitar(servicio.id, cantidad, notas);
    };

    return (
        <div className="flex flex-col justify-between overflow-hidden rounded-3xl border border-border/70 bg-card p-5 shadow-xs transition-all hover:border-primary/40 sm:p-6">
            <div className="space-y-4">
                {/* Cabecera del servicio */}
                <div className="flex items-start justify-between gap-3">
                    <div>
                        <span className="rounded-full bg-primary/10 px-2.5 py-0.5 text-[11px] font-bold text-primary">
                            {servicio.categoria}
                        </span>
                        <h4 className="mt-1.5 text-base font-bold text-foreground">
                            {servicio.nombre}
                        </h4>
                    </div>

                    <div className="text-right">
                        <span className="text-base font-black text-foreground">
                            {servicio.moneda_simbolo}
                            {servicio.precio.toFixed(2)}
                        </span>
                    </div>
                </div>

                {servicio.descripcion && (
                    <p className="line-clamp-2 text-xs leading-relaxed text-muted-foreground">
                        {servicio.descripcion}
                    </p>
                )}

                {/* Campo opcional de notas / horario */}
                {mostrarNotas ? (
                    <div className="space-y-1.5 pt-1">
                        <Textarea
                            placeholder="Instrucciones especiales o horario deseado..."
                            value={notas}
                            onChange={(e) => setNotas(e.target.value)}
                            className="min-h-[70px] resize-none rounded-xl text-xs"
                        />
                    </div>
                ) : (
                    <button
                        type="button"
                        onClick={() => setMostrarNotas(true)}
                        className="text-[11px] font-semibold text-primary hover:underline"
                    >
                        + Agregar instrucciones especiales
                    </button>
                )}
            </div>

            {/* Selector de cantidad y botón */}
            <div className="mt-4 flex items-center justify-between gap-4 border-t border-border/40 pt-5">
                <div className="flex items-center rounded-xl border border-border/70 bg-secondary/40 p-1">
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        onClick={decrementar}
                        disabled={cantidad <= 1 || isSubmitting}
                        className="size-7 rounded-lg"
                    >
                        <Minus className="size-3.5" />
                    </Button>
                    <span className="w-8 text-center text-xs font-bold text-foreground">
                        {cantidad}
                    </span>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        onClick={incrementar}
                        disabled={cantidad >= 50 || isSubmitting}
                        className="size-7 rounded-lg"
                    >
                        <Plus className="size-3.5" />
                    </Button>
                </div>

                <Button
                    type="button"
                    size="sm"
                    onClick={handleSolicitar}
                    disabled={isSubmitting}
                    className="gap-1.5 rounded-xl font-bold"
                >
                    {isSubmitting ? (
                        <Loader2 className="size-3.5 animate-spin" />
                    ) : (
                        <Check className="size-3.5" />
                    )}
                    <span>
                        Pedir ({servicio.moneda_simbolo}
                        {(servicio.precio * cantidad).toFixed(2)})
                    </span>
                </Button>
            </div>
        </div>
    );
};
