import { zodResolver } from '@hookform/resolvers/zod';
import { router } from '@inertiajs/react';
import { useForm } from 'react-hook-form';
import type { CambiarContrasenaFormValues } from '../schemas/cambiarContrasenaSchema';
import { cambiarContrasenaSchema } from '../schemas/cambiarContrasenaSchema';

export const useCambiarContrasenaForm = () => {
    const form = useForm<CambiarContrasenaFormValues>({
        resolver: zodResolver(cambiarContrasenaSchema),
        defaultValues: {
            current_password: '',
            password: '',
            password_confirmation: '',
        },
    });

    const {
        register,
        handleSubmit,
        setError,
        setValue,
        watch,
        formState: { errors, isSubmitting },
    } = form;

    const onSubmit = (data: CambiarContrasenaFormValues) => {
        router.post('/auth/cambiar-contrasena', data, {
            onError: (erroresServidor) => {
                for (const [campo, mensaje] of Object.entries(
                    erroresServidor,
                )) {
                    setError(campo as keyof CambiarContrasenaFormValues, {
                        type: 'server',
                        message: Array.isArray(mensaje)
                            ? mensaje[0]
                            : String(mensaje),
                    });
                }
            },
        });
    };

    return {
        register,
        handleSubmit: handleSubmit(onSubmit),
        setValue,
        watch,
        errors,
        isSubmitting,
    };
};

export default useCambiarContrasenaForm;
