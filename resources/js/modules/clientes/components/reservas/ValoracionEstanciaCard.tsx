import { Star } from 'lucide-react';
import { useState } from 'react';
import { useValoracionForm } from '@/modules/clientes/hooks/useValoracionForm';
import { Button } from '@/modules/shared/components/ui/button';
import { Textarea } from '@/modules/shared/components/ui/textarea';

interface ValoracionEstanciaCardProps {
    reservaId: number;
    codigoReserva: string;
}

export const ValoracionEstanciaCard = ({
    reservaId,
    codigoReserva,
}: ValoracionEstanciaCardProps) => {
    const [hovered, setHovered] = useState(0);
    const { form, enviando, isSubmitSuccessful, onSubmit } = useValoracionForm({
        reservaId,
        codigoReserva,
    });
    const {
        register,
        setValue,
        watch,
        formState: { errors },
    } = form;

    const estrellas = watch('estrellas') ?? 0;

    if (isSubmitSuccessful) {
        return (
            <div className="rounded-3xl border border-emerald-500/30 bg-emerald-500/10 p-6 text-center">
                <p className="text-sm font-black text-emerald-700 dark:text-emerald-400">
                    ¡Gracias por tu valoración! 🌟
                </p>
                <p className="mt-1 text-xs text-muted-foreground">
                    Tu opinión nos ayuda a mejorar la experiencia en Hotel
                    Bugambilias.
                </p>
            </div>
        );
    }

    return (
        <div className="space-y-4 rounded-3xl border border-border/70 bg-card p-6 shadow-xs">
            <div>
                <h4 className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                    ¿Cómo fue tu Estancia?
                </h4>
                <p className="mt-0.5 text-xs text-muted-foreground">
                    Comparte tu experiencia con nosotros
                </p>
            </div>
            <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
                {/* Star Rating */}
                <div className="flex items-center gap-1">
                    {[1, 2, 3, 4, 5].map((star) => (
                        <button
                            key={star}
                            type="button"
                            onMouseEnter={() => setHovered(star)}
                            onMouseLeave={() => setHovered(0)}
                            onClick={() =>
                                setValue('estrellas', star, {
                                    shouldValidate: true,
                                })
                            }
                            className="transition-transform active:scale-110"
                        >
                            <Star
                                className={`size-7 transition-colors ${
                                    star <= (hovered || estrellas)
                                        ? 'fill-amber-400 text-amber-400'
                                        : 'fill-muted text-muted-foreground/30'
                                }`}
                            />
                        </button>
                    ))}
                    {estrellas > 0 && (
                        <span className="ml-2 text-xs font-bold text-amber-600 dark:text-amber-400">
                            {
                                (
                                    [
                                        '',
                                        'Muy malo',
                                        'Malo',
                                        'Regular',
                                        'Bueno',
                                        'Excelente',
                                    ] as const
                                )[estrellas]
                            }
                        </span>
                    )}
                </div>
                {errors.estrellas && (
                    <p className="text-xs font-bold text-destructive">
                        {errors.estrellas.message}
                    </p>
                )}

                {/* Comment */}
                <div>
                    <Textarea
                        {...register('comentario')}
                        placeholder="Cuéntanos qué te pareció tu estancia, qué podemos mejorar..."
                        className="min-h-20 resize-none rounded-xl text-xs"
                        rows={3}
                    />
                    {errors.comentario && (
                        <p className="mt-1 text-xs font-bold text-destructive">
                            {errors.comentario.message}
                        </p>
                    )}
                </div>

                <Button
                    type="submit"
                    size="sm"
                    disabled={enviando || estrellas === 0}
                    className="w-full rounded-xl text-xs font-bold"
                >
                    {enviando ? 'Enviando...' : 'Enviar Valoración'}
                </Button>
            </form>
        </div>
    );
};

export default ValoracionEstanciaCard;
