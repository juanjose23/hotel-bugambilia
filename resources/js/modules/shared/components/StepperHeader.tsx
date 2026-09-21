import { CheckCircle2 } from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';

export interface PasoStepperItem {
    num: number;
    titulo: string;
    subtitulo?: string;
}

interface StepperHeaderProps {
    pasos: PasoStepperItem[];
    pasoActual: number;
    onCambiarPaso: (paso: number) => void;
    minWidthClass?: string;
    className?: string;
}

export const StepperHeader = ({
    pasos,
    pasoActual,
    onCambiarPaso,
    minWidthClass = 'min-w-[480px]',
    className = '',
}: StepperHeaderProps) => {
    return (
        <div className={`mb-8 overflow-x-auto pb-2 ${className}`}>
            <div
                className={`flex ${minWidthClass} items-center justify-between gap-2 rounded-3xl border border-border bg-card p-3 shadow-xs`}
            >
                {pasos.map((p) => {
                    const esActivo = pasoActual === p.num;
                    const esCompletado = pasoActual > p.num;

                    return (
                        <Button
                            key={p.num}
                            type="button"
                            variant="ghost"
                            onClick={() => onCambiarPaso(p.num)}
                            className={`flex h-auto flex-1 items-center justify-start gap-3 rounded-2xl p-2.5 text-left transition-all ${
                                esActivo
                                    ? 'bg-primary text-primary-foreground shadow-md hover:bg-primary hover:text-primary-foreground'
                                    : esCompletado
                                      ? 'bg-primary/10 text-primary hover:bg-primary/20 dark:bg-rose-950/40 dark:text-rose-200'
                                      : 'text-muted-foreground hover:bg-muted'
                            }`}
                        >
                            <div
                                className={`flex size-7 shrink-0 items-center justify-center rounded-xl text-xs font-black ${
                                    esActivo
                                        ? 'bg-primary-foreground text-primary'
                                        : esCompletado
                                          ? 'bg-primary text-primary-foreground'
                                          : 'bg-muted text-muted-foreground'
                                }`}
                            >
                                {esCompletado ? (
                                    <CheckCircle2 className="size-4" />
                                ) : (
                                    p.num
                                )}
                            </div>
                            <div className="min-w-0 text-left">
                                <div className="truncate text-xs font-black">
                                    {p.titulo}
                                </div>
                                {p.subtitulo && (
                                    <div
                                        className={`truncate text-[10px] ${
                                            esActivo
                                                ? 'text-primary-foreground/80'
                                                : 'text-muted-foreground'
                                        }`}
                                    >
                                        {p.subtitulo}
                                    </div>
                                )}
                            </div>
                        </Button>
                    );
                })}
            </div>
        </div>
    );
};

export default StepperHeader;
