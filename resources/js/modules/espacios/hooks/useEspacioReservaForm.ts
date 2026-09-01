import { zodResolver } from '@hookform/resolvers/zod';
import { useForm } from 'react-hook-form';
import { espacioReservaSchema } from '../schemas/espacioReservaSchema';
import type { EspacioReservaFormData } from '../schemas/espacioReservaSchema';
import type { EspacioItem } from '../types';

interface UseEspacioReservaFormProps {
    space: Pick<EspacioItem, 'nombre' | 'capacidad'>;
}

export const useEspacioReservaForm = ({
    space,
}: UseEspacioReservaFormProps) => {
    const {
        register,
        handleSubmit,
        setValue,
        watch,
        reset,
        formState: { errors, isSubmitting, isSubmitSuccessful },
    } = useForm<EspacioReservaFormData>({
        resolver: zodResolver(espacioReservaSchema),
        defaultValues: {
            nombre: '',
            email: '',
            telefono: '',
            tipo_evento: 'Evento / Reunión',
            fecha: '',
            hora_inicio: '09:00',
            hora_fin: '18:00',
            asistentes: String(space.capacidad || 20),
            requiere_catering: false,
            notas: '',
        },
    });

    const onSubmit = (data: EspacioReservaFormData): Promise<void> => {
        const mensaje = encodeURIComponent(
            `*Solicitud de Reserva de Espacio - Hotel Bugambilias*\n\n` +
                `*Espacio:* ${space.nombre}\n` +
                `*Tipo de Evento:* ${data.tipo_evento}\n` +
                `*Nombre del Solicitante:* ${data.nombre}\n` +
                `*Correo:* ${data.email}\n` +
                `*Teléfono:* ${data.telefono}\n` +
                `*Fecha:* ${data.fecha}\n` +
                `*Horario:* ${data.hora_inicio} a ${data.hora_fin}\n` +
                `*Asistentes:* ${data.asistentes}\n` +
                `*Catering:* ${data.requiere_catering ? 'Sí' : 'No'}\n` +
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
        setValue,
        watch,
        reset,
        errors,
        isSubmitting,
        isSubmitSuccessful,
    };
};
