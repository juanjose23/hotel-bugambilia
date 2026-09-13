import { CheckCircle2, Loader2, Users } from 'lucide-react';
import type { FieldErrors, UseFormRegister } from 'react-hook-form';
import { Button } from '@/modules/shared/components/ui/button';
import type { FormReservaMesa } from '../../schemas/reservaMesaSchema';
import { RestauranteCamposContactoReserva } from './RestauranteCamposContactoReserva';

interface RestaurantePasoDatosContactoProps {
    register: UseFormRegister<FormReservaMesa>;
    errors: FieldErrors<FormReservaMesa>;
    onSubmit: () => void;
    isSubmitting: boolean;
    errorEnvio: string | null;
    alAgregarNotaRapida: (tag: string) => void;
}

export const RestaurantePasoDatosContacto = ({
    register,
    errors,
    onSubmit,
    isSubmitting,
    errorEnvio,
    alAgregarNotaRapida,
}: RestaurantePasoDatosContactoProps) => {
    return (
        <div className="space-y-6 rounded-3xl border border-border/80 bg-card p-6 shadow-xs">
            <div className="flex items-center gap-3 border-b border-border/60 pb-4">
                <div className="flex size-9 items-center justify-center rounded-2xl border border-primary/20 bg-primary/10 text-primary dark:text-rose-400">
                    <Users className="size-4" />
                </div>
                <div>
                    <h3 className="text-base font-black text-foreground">
                        Datos del Titular de la Reserva
                    </h3>
                    <p className="text-xs text-muted-foreground">
                        Completa tus datos para confirmar tu mesa de inmediato.
                    </p>
                </div>
            </div>

            <form onSubmit={onSubmit} className="space-y-4">
                <RestauranteCamposContactoReserva
                    register={register}
                    errors={errors}
                    alAgregarNotaRapida={alAgregarNotaRapida}
                    variante="pagina"
                />

                {errorEnvio && (
                    <div className="rounded-2xl border border-destructive/40 bg-destructive/10 p-3 text-xs font-bold text-destructive">
                        {errorEnvio}
                    </div>
                )}

                <Button
                    type="submit"
                    disabled={isSubmitting}
                    className="mt-4 h-12 w-full cursor-pointer rounded-2xl text-sm font-black shadow-md"
                >
                    {isSubmitting ? (
                        <>
                            <Loader2 className="mr-2 size-4 animate-spin" />
                            Confirmando Reserva...
                        </>
                    ) : (
                        <>
                            <CheckCircle2 className="mr-2 size-4" />
                            Confirmar Reservación de Mesa
                        </>
                    )}
                </Button>
            </form>
        </div>
    );
};

export default RestaurantePasoDatosContacto;
