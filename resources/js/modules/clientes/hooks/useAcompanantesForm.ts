import { zodResolver } from '@hookform/resolvers/zod';
import { router } from '@inertiajs/react';
import { useForm, useFieldArray } from 'react-hook-form';
import { acompanantesSchema } from '../schemas/acompanantesSchema';
import type { AcompanantesFormData } from '../schemas/acompanantesSchema';
import type { AcompananteItem } from '../types';

interface UseAcompanantesFormProps {
    reservaId: number;
    acompanantesIniciales?: AcompananteItem[];
    onSuccessCallback?: () => void;
}

export const useAcompanantesForm = ({
    reservaId,
    acompanantesIniciales = [],
    onSuccessCallback,
}: UseAcompanantesFormProps) => {
    const form = useForm<AcompanantesFormData>({
        resolver: zodResolver(acompanantesSchema),
        defaultValues: {
            acompanantes:
                acompanantesIniciales.length > 0
                    ? acompanantesIniciales
                    : [{ nombre: '', identificacion: '', tipo: 'adulto' }],
        },
    });

    const { fields, append, remove } = useFieldArray({
        control: form.control,
        name: 'acompanantes',
    });

    const onSubmit = (data: AcompanantesFormData) => {
        router.post(`/portal/reservas/${reservaId}/acompanantes`, data, {
            preserveScroll: true,
            onSuccess: () => {
                onSuccessCallback?.();
            },
        });
    };

    return {
        form,
        fields,
        append,
        remove,
        register: form.register,
        setValue: form.setValue,
        handleSubmit: form.handleSubmit(onSubmit),
        isSubmitting: form.formState.isSubmitting,
        errors: form.formState.errors,
    };
};
