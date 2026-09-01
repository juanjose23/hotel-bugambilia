import type {
    UseFormRegister,
    FieldErrors,
    UseFormSetValue,
} from 'react-hook-form';
import {
    Field,
    FieldLabel,
    FieldError,
} from '@/modules/shared/components/ui/field';
import { Input } from '@/modules/shared/components/ui/input';
import type { CrearReservaFormValues } from '../schemas/crearReservaSchema';
import { CalendarioRangoReserva } from './CalendarioRangoReserva';

interface ReservaPasoFechasProps {
    checkIn: string;
    checkOut: string;
    diasAgotados?: string[];
    capacidadMaxima?: number;
    register: UseFormRegister<CrearReservaFormValues>;
    setValue: UseFormSetValue<CrearReservaFormValues>;
    errors: FieldErrors<CrearReservaFormValues>;
}

export const ReservaPasoFechas = ({
    checkIn,
    checkOut,
    diasAgotados = [],
    capacidadMaxima = 4,
    register,
    setValue,
    errors,
}: ReservaPasoFechasProps) => {
    return (
        <div className="animate-in fade-in space-y-6 duration-200">
            <div className="space-y-4 rounded-3xl border border-border bg-card p-6 shadow-sm">
                <div>
                    <h2 className="text-lg font-black tracking-tight text-foreground">
                        Selecciona las fechas de tu estancia
                    </h2>
                    <p className="mt-0.5 text-xs text-muted-foreground">
                        Visualiza los dos meses consecutivos. Los días tachados
                        están agotados para esta categoría.
                    </p>
                </div>

                {/* Calendario Doble */}
                <CalendarioRangoReserva
                    checkIn={checkIn}
                    checkOut={checkOut}
                    diasAgotados={diasAgotados}
                    onSelectRango={(cin, cout) => {
                        setValue('fecha_check_in', cin, {
                            shouldValidate: true,
                        });
                        setValue('fecha_check_out', cout, {
                            shouldValidate: true,
                        });
                    }}
                />

                {/* Selector de Huéspedes */}
                <div className="grid grid-cols-1 gap-4 pt-2 sm:grid-cols-2">
                    <Field>
                        <FieldLabel className="text-xs font-bold">
                            Adultos
                        </FieldLabel>
                        <Input
                            type="number"
                            min={1}
                            max={capacidadMaxima}
                            {...register('adultos', { valueAsNumber: true })}
                            className="h-11 rounded-2xl bg-background text-xs font-bold"
                        />
                        {errors.adultos && (
                            <FieldError>{errors.adultos.message}</FieldError>
                        )}
                    </Field>

                    <Field>
                        <FieldLabel className="text-xs font-bold">
                            Niños (0 a 12 años)
                        </FieldLabel>
                        <Input
                            type="number"
                            min={0}
                            max={4}
                            {...register('ninos', { valueAsNumber: true })}
                            className="h-11 rounded-2xl bg-background text-xs font-bold"
                        />
                        {errors.ninos && (
                            <FieldError>{errors.ninos.message}</FieldError>
                        )}
                    </Field>
                </div>
            </div>
        </div>
    );
};

export default ReservaPasoFechas;
