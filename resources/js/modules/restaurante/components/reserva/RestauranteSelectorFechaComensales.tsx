import { Calendar, Minus, Plus, Users } from 'lucide-react';
import type { FieldErrors, UseFormRegister } from 'react-hook-form';
import { Button } from '@/modules/shared/components/ui/button';
import {
    Field,
    FieldError,
    FieldLabel,
} from '@/modules/shared/components/ui/field';
import { Input } from '@/modules/shared/components/ui/input';
import { OPCIONES_COMENSALES } from '../../constants';
import type { FormReservaMesa } from '../../schemas/reservaMesaSchema';

interface RestauranteSelectorFechaComensalesProps {
    register: UseFormRegister<FormReservaMesa>;
    errors: FieldErrors<FormReservaMesa>;
    adultosSeleccionados: number;
    alCambiarFecha?: (fecha: string) => void;
    alAjustarComensales: (delta: number) => void;
    alSeleccionarComensales?: (numero: number) => void;
    variante?: 'pagina' | 'modal';
}

export const RestauranteSelectorFechaComensales = ({
    register,
    errors,
    adultosSeleccionados,
    alCambiarFecha,
    alAjustarComensales,
    alSeleccionarComensales,
    variante = 'pagina',
}: RestauranteSelectorFechaComensalesProps) => {
    const esModal = variante === 'modal';
    const hoy = new Date().toISOString().split('T')[0];
    const campo = register('fecha');
    const tamanoInput = esModal ? 'h-10' : 'h-11';
    const etiquetaBase = esModal ? 'text-[11px]' : 'text-xs';

    return (
        <div className="space-y-3">
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <Field>
                    <FieldLabel
                        className={`${etiquetaBase} flex items-center gap-1.5 font-bold`}
                    >
                        {esModal && (
                            <Calendar className="size-3.5 text-primary" />
                        )}
                        Fecha de Reserva *
                    </FieldLabel>
                    <Input
                        type="date"
                        min={hoy}
                        className={`${tamanoInput} rounded-xl text-xs font-medium`}
                        {...campo}
                        onChange={(e) => {
                            campo.onChange(e);
                            alCambiarFecha?.(e.target.value);
                        }}
                    />
                    {errors.fecha && (
                        <FieldError className="text-[10px]">
                            {errors.fecha.message}
                        </FieldError>
                    )}
                </Field>

                <Field>
                    <FieldLabel
                        className={`${etiquetaBase} flex items-center justify-between font-bold`}
                    >
                        <span className="flex items-center gap-1.5">
                            {esModal && (
                                <Users className="size-3.5 text-primary" />
                            )}
                            Comensales *
                        </span>
                        <span className="text-[10px] font-normal text-muted-foreground">
                            {adultosSeleccionados === 1
                                ? '1 persona'
                                : `${adultosSeleccionados} personas`}
                        </span>
                    </FieldLabel>
                    <div className="flex items-center gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            size="icon"
                            onClick={() => alAjustarComensales(-1)}
                            disabled={adultosSeleccionados <= 1}
                            className={`${tamanoInput} w-11 shrink-0 cursor-pointer rounded-xl`}
                        >
                            <Minus className="size-4" />
                        </Button>
                        <Input
                            type="number"
                            min={1}
                            max={30}
                            className={`${tamanoInput} rounded-xl text-center text-xs font-black`}
                            {...register('adultos', { valueAsNumber: true })}
                        />
                        <Button
                            type="button"
                            variant="outline"
                            size="icon"
                            onClick={() => alAjustarComensales(1)}
                            disabled={adultosSeleccionados >= 30}
                            className={`${tamanoInput} w-11 shrink-0 cursor-pointer rounded-xl`}
                        >
                            <Plus className="size-4" />
                        </Button>
                    </div>
                    {errors.adultos && (
                        <FieldError className="text-[10px]">
                            {errors.adultos.message}
                        </FieldError>
                    )}
                </Field>
            </div>

            {alSeleccionarComensales && (
                <div className="flex scrollbar-none items-center gap-1.5 overflow-x-auto pb-1">
                    <span className="mr-1 shrink-0 text-[10px] font-medium text-muted-foreground">
                        Grupo:
                    </span>
                    {OPCIONES_COMENSALES.map((num) => (
                        <button
                            key={num}
                            type="button"
                            onClick={() => alSeleccionarComensales(num)}
                            className={`shrink-0 cursor-pointer rounded-lg px-2.5 py-1 text-[11px] font-bold transition-all ${
                                adultosSeleccionados === num
                                    ? 'bg-primary text-primary-foreground shadow-xs'
                                    : 'border border-border/50 bg-muted/50 text-muted-foreground hover:bg-muted hover:text-foreground'
                            }`}
                        >
                            {num} {num === 1 ? 'persona' : 'pers.'}
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
};

export default RestauranteSelectorFechaComensales;
