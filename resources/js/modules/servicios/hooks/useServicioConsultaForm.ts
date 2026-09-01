import { zodResolver } from '@hookform/resolvers/zod';
import { useForm } from 'react-hook-form';
import { servicioConsultaSchema } from '../schemas/servicioConsultaSchema';
import type { ServicioConsultaFormData } from '../schemas/servicioConsultaSchema';
import type { ServicioItem } from '../types';

interface UseServicioConsultaFormProps {
    servicio: ServicioItem;
}

export const useServicioConsultaForm = ({
    servicio,
}: UseServicioConsultaFormProps) => {
    const {
        register,
        handleSubmit,
        reset,
        formState: { errors, isSubmitting, isSubmitSuccessful },
    } = useForm<ServicioConsultaFormData>({
        resolver: zodResolver(servicioConsultaSchema),
        defaultValues: {
            nombre: '',
            email: '',
            telefono: '',
            fecha: '',
            personas: '2',
            notas: '',
        },
    });

    const onSubmit = (data: ServicioConsultaFormData): Promise<void> => {
        const mensaje = encodeURIComponent(
            `*Consulta sobre Servicio - Hotel Bugambilias*\n\n` +
                `*Servicio de interés:* ${servicio.nombre}\n` +
                (servicio.categoria
                    ? `*Categoría:* ${servicio.categoria}\n`
                    : '') +
                `*Nombre del Cliente:* ${data.nombre}\n` +
                `*Correo:* ${data.email}\n` +
                `*Teléfono:* ${data.telefono}\n` +
                `*Fecha tentativa:* ${data.fecha}\n` +
                `*Personas:* ${data.personas}\n` +
                (data.notas ? `*Notas:* ${data.notas}\n` : ''),
        );

        return new Promise<void>((resolve) => {
            setTimeout(() => {
                window.open(
                    `https://wa.me/50584842323?text=${mensaje}`,
                    '_blank',
                );
                resolve();
            }, 600);
        });
    };

    return {
        register,
        manejarSubmit: handleSubmit(onSubmit),
        reset,
        errors,
        isSubmitting,
        isSubmitSuccessful,
    };
};
