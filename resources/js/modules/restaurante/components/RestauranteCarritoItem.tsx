import { MessageSquareQuote, Minus, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { Input } from '@/modules/shared/components/ui/input';
import type { ItemCarritoRestaurante, MenuItemData } from '../types';

interface PropsRestauranteCarritoItem {
    item: ItemCarritoRestaurante;
    moneda: string;
    onAgregar: (plato: MenuItemData) => void;
    onDisminuir: (platoId: number) => void;
    onEliminar: (platoId: number) => void;
    onGuardarNotas: (platoId: number, notas: string) => void;
}

export const RestauranteCarritoItem = ({
    item,
    moneda,
    onAgregar,
    onDisminuir,
    onEliminar,
    onGuardarNotas,
}: PropsRestauranteCarritoItem) => {
    const { plato, cantidad, notas } = item;
    const [editandoNota, setEditandoNota] = useState(false);

    return (
        <div className="space-y-2 rounded-2xl border border-border/70 bg-card p-3 text-xs shadow-xs">
            <div className="flex items-center justify-between gap-3">
                <div className="min-w-0 flex-1">
                    <p className="truncate font-bold text-foreground">
                        {plato.nombre}
                    </p>
                    <p className="text-[11px] text-muted-foreground">
                        {moneda}{' '}
                        {(Number(plato.precio || 0) * cantidad).toFixed(2)}
                    </p>
                </div>

                <div className="flex items-center gap-1 rounded-full border border-border bg-muted/30 p-0.5">
                    <button
                        type="button"
                        onClick={() => onDisminuir(plato.id)}
                        className="flex size-6 cursor-pointer items-center justify-center rounded-full text-foreground hover:bg-muted"
                    >
                        <Minus className="size-3" />
                    </button>
                    <span className="w-5 text-center text-xs font-bold">
                        {cantidad}
                    </span>
                    <button
                        type="button"
                        onClick={() => onAgregar(plato)}
                        className="flex size-6 cursor-pointer items-center justify-center rounded-full text-foreground hover:bg-muted"
                    >
                        <Plus className="size-3" />
                    </button>
                </div>

                <button
                    type="button"
                    onClick={() => onEliminar(plato.id)}
                    className="cursor-pointer p-1 text-muted-foreground/60 hover:text-rose-500"
                >
                    <Trash2 className="size-3.5" />
                </button>
            </div>

            {notas && !editandoNota && (
                <p className="flex items-center justify-between rounded-lg bg-muted/50 px-2.5 py-1 text-[11px] text-muted-foreground italic">
                    <span>💬 {notas}</span>
                    <button
                        type="button"
                        onClick={() => setEditandoNota(true)}
                        className="ml-2 cursor-pointer text-[10px] font-bold text-primary not-italic hover:underline"
                    >
                        Editar
                    </button>
                </p>
            )}

            {editandoNota ? (
                <div className="flex gap-1.5 pt-1">
                    <Input
                        type="text"
                        defaultValue={notas || ''}
                        placeholder="Ej: Término medio, sin aderezo..."
                        className="h-7 rounded-lg text-[11px]"
                        onKeyDown={(e) => {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                onGuardarNotas(plato.id, e.currentTarget.value);
                                setEditandoNota(false);
                            }
                        }}
                        onBlur={(e) => {
                            onGuardarNotas(plato.id, e.target.value);
                            setEditandoNota(false);
                        }}
                        autoFocus
                    />
                </div>
            ) : (
                !notas && (
                    <button
                        type="button"
                        onClick={() => setEditandoNota(true)}
                        className="inline-flex cursor-pointer items-center gap-1 text-[10px] font-medium text-muted-foreground hover:text-primary"
                    >
                        <MessageSquareQuote className="size-3" />
                        <span>+ Observación para cocina</span>
                    </button>
                )
            )}
        </div>
    );
};

export default RestauranteCarritoItem;
