import { zodResolver } from '@hookform/resolvers/zod';
import { router } from '@inertiajs/react';
import { useForm, useWatch } from 'react-hook-form';
import type { RegistroFormValues } from '../schemas/registroSchema';
import { registroSchema } from '../schemas/registroSchema';

export const useRegistroForm = () => {
    const form = useForm<RegistroFormValues>({
        resolver: zodResolver(registroSchema),
        defaultValues: {
            tipo_persona: 'natural',
            primer_nombre: '',
            primer_apellido: '',
            razon_social: '',
            email: '',
            phone: '',
            tipo_identificacion: 'cedula',
            numero_identificacion: '',
            password: '',
            password_confirmation: '',
        },
    });

    const {
        register,
        handleSubmit,
        setError,
        setValue,
        control,
        formState: { errors, isSubmitting },
    } = form;

    const tipoPersona = useWatch({ control, name: 'tipo_persona' });
    const tipoIdentificacion = useWatch({
        control,
        name: 'tipo_identificacion',
    });

    const onSubmit = (data: RegistroFormValues) => {
        router.post('/auth/registro', data, {
            onError: (erroresServidor) => {
                for (const [campo, mensaje] of Object.entries(
                    erroresServidor,
                )) {
                    setError(campo as keyof RegistroFormValues, {
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
        tipoPersona,
        tipoIdentificacion,
        errors,
        isSubmitting,
    };
};

export default useRegistroForm;
