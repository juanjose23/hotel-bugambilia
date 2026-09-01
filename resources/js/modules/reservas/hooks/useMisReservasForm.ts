import { zodResolver } from '@hookform/resolvers/zod';
import { router } from '@inertiajs/react';
import { useForm } from 'react-hook-form';
import type { MisReservasFormValues } from '../schemas/misReservasSchema';
import { misReservasSchema } from '../schemas/misReservasSchema';

interface UseMisReservasFormProps {
    codigoInicial?: string;
}

export const useMisReservasForm = ({
    codigoInicial = '',
}: UseMisReservasFormProps = {}) => {
    const form = useForm<MisReservasFormValues>({
        resolver: zodResolver(misReservasSchema),
        defaultValues: {
            codigo: codigoInicial,
        },
    });

    const {
        register,
        handleSubmit,
        formState: { errors, isSubmitting },
    } = form;

    const onSubmit = (data: MisReservasFormValues) => {
        router.get(
            '/mis-reservas',
            { codigo: data.codigo.trim() },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    return {
        form,
        register,
        handleSubmit: handleSubmit(onSubmit),
        errors,
        isSubmitting,
    };
};

export default useMisReservasForm;
