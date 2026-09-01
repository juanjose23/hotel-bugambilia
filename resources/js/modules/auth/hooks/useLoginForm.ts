import { zodResolver } from '@hookform/resolvers/zod';
import { router } from '@inertiajs/react';
import { useForm } from 'react-hook-form';
import type { LoginFormValues } from '../schemas/loginSchema';
import { loginSchema } from '../schemas/loginSchema';

export const useLoginForm = () => {
    const form = useForm<LoginFormValues>({
        resolver: zodResolver(loginSchema),
        defaultValues: {
            email: '',
            password: '',
            remember: false,
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

    const onSubmit = (data: LoginFormValues) => {
        router.post('/auth/login', data, {
            onError: (erroresServidor) => {
                for (const [campo, mensaje] of Object.entries(
                    erroresServidor,
                )) {
                    if (campo === 'email' || campo === 'password') {
                        setError(campo, {
                            type: 'server',
                            message: Array.isArray(mensaje)
                                ? mensaje[0]
                                : String(mensaje),
                        });
                    }
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

export default useLoginForm;
