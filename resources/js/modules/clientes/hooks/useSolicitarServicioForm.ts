import { zodResolver } from '@hookform/resolvers/zod';
import { router } from '@inertiajs/react';
import { useForm } from 'react-hook-form';
import { solicitarServicioSchema } from '../schemas/solicitarServicioSchema';
import type { SolicitarServicioFormData } from '../schemas/solicitarServicioSchema';

interface UseSolicitarServicioFormProps {
    reservaId: number;
    servicioIdInicial?: number;
    onSuccessCallback?: () => void;
}

export const useSolicitarServicioForm = ({
    reservaId,
    servicioIdInicial = 0,
    onSuccessCallback,
}: UseSolicitarServicioFormProps) => {
    const form = useForm<SolicitarServicioFormData>({
        resolver: zodResolver(solicitarServicioSchema),
        defaultValues: {
            servicio_id: servicioIdInicial,
            cantidad: 1,
            notas: '',
        },
    });

    const onSubmit = (data: SolicitarServicioFormData) => {
        router.post(`/portal/reservas/${reservaId}/servicios`, data, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                onSuccessCallback?.();
            },
        });
    };

    return {
        form,
        register: form.register,
        setValue: form.setValue,
        watch: form.watch,
        handleSubmit: form.handleSubmit(onSubmit),
        isSubmitting: form.formState.isSubmitting,
        errors: form.formState.errors,
    };
};
