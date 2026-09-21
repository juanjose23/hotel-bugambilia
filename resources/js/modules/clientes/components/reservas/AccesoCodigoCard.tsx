import { Search, Loader2, KeyRound, ArrowRight } from 'lucide-react';
import { useAccesoCodigoForm } from '@/modules/clientes/hooks/useAccesoCodigoForm';
import { Button } from '@/modules/shared/components/ui/button';
import { Input } from '@/modules/shared/components/ui/input';

interface AccesoCodigoCardProps {
    codigoInicial?: string;
    titulo?: string;
    descripcion?: string;
    className?: string;
}

export const AccesoCodigoCard = ({
    codigoInicial = '',
    titulo = 'Consultar o Acceder con Código de Reserva',
    descripcion = 'Ingresa tu código de confirmación (ej. RES-2026-XXXX) para acceder a tu reservación o activar tu sesión de huésped.',
    className = '',
}: AccesoCodigoCardProps) => {
    const { register, handleSubmit, isSubmitting, errors } =
        useAccesoCodigoForm({
            codigoInicial,
        });

    return (
        <div
            className={`rounded-3xl border border-border/80 bg-card p-6 shadow-sm transition-all sm:p-8 ${className}`}
        >
            <div className="flex items-center gap-3">
                <div className="flex size-10 items-center justify-center rounded-2xl bg-primary/10 text-primary dark:bg-rose-950/60 dark:text-rose-400">
                    <KeyRound className="size-5" />
                </div>
                <div>
                    <h3 className="text-base font-black text-foreground sm:text-lg">
                        {titulo}
                    </h3>
                    {descripcion && (
                        <p className="text-xs text-muted-foreground">
                            {descripcion}
                        </p>
                    )}
                </div>
            </div>

            <form onSubmit={handleSubmit} noValidate className="mt-5 space-y-2">
                <div className="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <div className="relative flex-1">
                        <Search className="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            type="text"
                            placeholder="Ej. RES-2026-ABCD"
                            {...register('codigo')}
                            className="h-11 rounded-2xl border border-border/80 bg-background pr-4 pl-11 font-mono text-xs font-bold tracking-wider uppercase placeholder:font-sans placeholder:tracking-normal focus-visible:ring-2 focus-visible:ring-primary/30"
                        />
                    </div>
                    <Button
                        type="submit"
                        disabled={isSubmitting}
                        className="h-11 cursor-pointer rounded-2xl bg-primary px-6 text-xs font-black text-white shadow-md shadow-primary/20 transition-all hover:bg-primary/90"
                    >
                        {isSubmitting ? (
                            <>
                                <Loader2 className="mr-2 size-4 animate-spin" />
                                <span>Verificando...</span>
                            </>
                        ) : (
                            <>
                                <span>Acceder a Reserva</span>
                                <ArrowRight className="ml-1.5 size-4" />
                            </>
                        )}
                    </Button>
                </div>

                {errors.codigo && (
                    <p className="px-2 text-xs font-bold text-destructive">
                        {errors.codigo.message}
                    </p>
                )}
            </form>
        </div>
    );
};
