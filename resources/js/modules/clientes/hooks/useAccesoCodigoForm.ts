import { zodResolver } from '@hookform/resolvers/zod';
import { router } from '@inertiajs/react';
import { useForm } from 'react-hook-form';
import { accesoCodigoSchema } from '../schemas/accesoCodigoSchema';
import type { AccesoCodigoFormData } from '../schemas/accesoCodigoSchema';

interface UseAccesoCodigoFormProps {
    codigoInicial?: string;
    onSuccessCallback?: () => void;
}

export const useAccesoCodigoForm = ({
    codigoInicial = '',
    onSuccessCallback,
}: UseAccesoCodigoFormProps = {}) => {
    const form = useForm<AccesoCodigoFormData>({
        resolver: zodResolver(accesoCodigoSchema),
        defaultValues: {
            codigo: codigoInicial,
        },
    });

    const onSubmit = (data: AccesoCodigoFormData) => {
        router.post(
            '/portal/acceso-codigo',
            { codigo: data.codigo },
            {
                preserveScroll: true,
                onSuccess: () => {
                    onSuccessCallback?.();
                },
                onError: (errors) => {
                    if (errors.codigo) {
                        form.setError('codigo', {
                            type: 'manual',
                            message: errors.codigo,
                        });
                    }
                },
            },
        );
    };

    return {
        form,
        register: form.register,
        setValue: form.setValue,
        handleSubmit: form.handleSubmit(onSubmit),
        isSubmitting: form.formState.isSubmitting,
        errors: form.formState.errors,
    };
};
