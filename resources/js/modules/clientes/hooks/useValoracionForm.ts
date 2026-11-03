import { zodResolver } from '@hookform/resolvers/zod';
import { router } from '@inertiajs/react';
import { useForm } from 'react-hook-form';
import { valoracionSchema } from '@/modules/clientes/schemas/valoracionSchema';
import type { ValoracionFormValues } from '@/modules/clientes/schemas/valoracionSchema';

interface UseValoracionFormProps {
    reservaId: number;
    codigoReserva: string;
}

export const useValoracionForm = ({ reservaId, codigoReserva }: UseValoracionFormProps) => {
    const form = useForm<ValoracionFormValues>({
        resolver: zodResolver(valoracionSchema),
        defaultValues: {
            estrellas: 0,
            comentario: '',
        },
    });

    const onSubmit = (data: ValoracionFormValues) => {
        router.post(
            `/portal/reservas/${reservaId}/valoracion`,
            { ...data, codigo: codigoReserva },
            {
                preserveScroll: true,
                onSuccess: () => {
                    form.reset();
                },
            },
        );
    };

    return {
        form,
        enviando: form.formState.isSubmitting,
        isSubmitSuccessful: form.formState.isSubmitSuccessful,
        onSubmit,
    };
};
