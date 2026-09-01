import { zodResolver } from '@hookform/resolvers/zod';
import { router } from '@inertiajs/react';
import { useForm } from 'react-hook-form';
import { perfilClienteSchema } from '../schemas/perfilClienteSchema';
import type { PerfilClienteFormData } from '../schemas/perfilClienteSchema';
import type { ClienteProfile } from '../types';

interface UsePerfilClienteFormProps {
    cliente: ClienteProfile;
    onSuccessCallback?: () => void;
}

export const usePerfilClienteForm = ({
    cliente,
    onSuccessCallback,
}: UsePerfilClienteFormProps) => {
    const form = useForm<PerfilClienteFormData>({
        resolver: zodResolver(perfilClienteSchema),
        defaultValues: {
            nombre: cliente.nombre || '',
            email: cliente.email || '',
            telefono: cliente.telefono || '',
            identificacion: cliente.identificacion || '',
            tipo_identificacion: cliente.tipo_identificacion || '',
        },
    });

    const onSubmit = (data: PerfilClienteFormData) => {
        router.post('/portal/perfil', data, {
            preserveScroll: true,
            onSuccess: () => {
                onSuccessCallback?.();
            },
        });
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
