import { zodResolver } from '@hookform/resolvers/zod';
import { router } from '@inertiajs/react';
import { useForm } from 'react-hook-form';
import type { HomeBusquedaFormValues } from '../schemas/homeBusquedaSchema';
import { homeBusquedaSchema } from '../schemas/homeBusquedaSchema';

export const useHomeBusquedaForm = () => {
    const form = useForm<HomeBusquedaFormValues>({
        resolver: zodResolver(homeBusquedaSchema),
        defaultValues: {
            categoria: '',
            check_in: '',
            check_out: '',
            personas: '2',
        },
    });

    const {
        register,
        handleSubmit,
        setValue,
        watch,
        formState: { isSubmitting },
    } = form;

    const onSubmit = (data: HomeBusquedaFormValues) => {
        const queryParams: Record<string, string> = {
            personas: data.personas,
        };

        if (data.categoria && data.categoria.trim() !== '') {
            queryParams.categoria = data.categoria;
        }

        if (data.check_in && data.check_in.trim() !== '') {
            queryParams.check_in = data.check_in;
        }

        if (data.check_out && data.check_out.trim() !== '') {
            queryParams.check_out = data.check_out;
        }

        router.get('/habitaciones', queryParams, {
            preserveState: true,
        });
    };

    return {
        register,
        handleSubmit: handleSubmit(onSubmit),
        setValue,
        watch,
        isSubmitting,
    };
};

export default useHomeBusquedaForm;
