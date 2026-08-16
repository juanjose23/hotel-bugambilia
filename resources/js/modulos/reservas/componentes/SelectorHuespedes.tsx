import { Minus, Plus } from 'lucide-react';
import React from 'react';

interface PropiedadesSelectorHuespedes {
    adultos: number;
    ninos: number;
    capacidadMaxima: number;
    onAdultosChange: (val: number) => void;
    onNinosChange: (val: number) => void;
}

export function SelectorHuespedes({
    adultos,
    ninos,
    capacidadMaxima,
    onAdultosChange,
    onNinosChange,
}: PropiedadesSelectorHuespedes) {
    return (
        <div className="animate-in fade-in-50 space-y-6 rounded-3xl border border-border bg-card p-5 shadow-sm duration-300 md:p-8">
            <div>
                <h2 className="text-lg font-black text-foreground md:text-xl">
                    Huéspedes &{' '}
                    <span className="font-serif font-normal text-bugambilia-600 italic">
                        Acompañantes
                    </span>
                </h2>
                <p className="mt-0.5 text-xs text-muted-foreground">
                    Capacidad máxima sugerida: {capacidadMaxima} personas
                </p>
            </div>

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                {/* Adultos */}
                <div className="flex items-center justify-between rounded-2xl border border-border bg-background p-4">
                    <div>
                        <span className="block text-xs font-bold text-foreground">
                            Adultos
                        </span>
                        <span className="text-[10px] text-muted-foreground">
                            Mayores de 12 años
                        </span>
                    </div>
                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            onClick={() =>
                                onAdultosChange(Math.max(1, adultos - 1))
                            }
                            className="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-muted text-foreground transition hover:bg-muted/80 active:scale-95"
                        >
                            <Minus className="h-4 w-4" />
                        </button>
                        <span className="w-6 text-center text-sm font-black text-foreground">
                            {adultos}
                        </span>
                        <button
                            type="button"
                            onClick={() =>
                                onAdultosChange(
                                    Math.min(capacidadMaxima, adultos + 1),
                                )
                            }
                            className="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-bugambilia-600 text-white transition hover:bg-bugambilia-700 active:scale-95"
                        >
                            <Plus className="h-4 w-4" />
                        </button>
                    </div>
                </div>

                {/* Niños */}
                <div className="flex items-center justify-between rounded-2xl border border-border bg-background p-4">
                    <div>
                        <span className="block text-xs font-bold text-foreground">
                            Niños
                        </span>
                        <span className="text-[10px] text-muted-foreground">
                            Menores de 12 años
                        </span>
                    </div>
                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            onClick={() =>
                                onNinosChange(Math.max(0, ninos - 1))
                            }
                            className="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-muted text-foreground transition hover:bg-muted/80 active:scale-95"
                        >
                            <Minus className="h-4 w-4" />
                        </button>
                        <span className="w-6 text-center text-sm font-black text-foreground">
                            {ninos}
                        </span>
                        <button
                            type="button"
                            onClick={() => onNinosChange(ninos + 1)}
                            className="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-bugambilia-600 text-white transition hover:bg-bugambilia-700 active:scale-95"
                        >
                            <Plus className="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
