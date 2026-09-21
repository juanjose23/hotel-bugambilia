import { zodResolver } from '@hookform/resolvers/zod';
import { useForm, useWatch } from 'react-hook-form';
import {
    calcularNoches,
    verificarConflictoFechas,
} from '@/modules/reservas/utils/reservaCalculos';
import type { HabitacionReservaFormValues } from '../schemas/habitacionReservaSchema';
import { habitacionReservaSchema } from '../schemas/habitacionReservaSchema';
import type { HabitacionDetalleData } from '../types';

interface UseHabitacionReservaFormProps {
    room: HabitacionDetalleData;
    telefonoWhatsApp?: string;
    diasAgotados?: string[];
}

export const useHabitacionReservaForm = ({
    room,
    telefonoWhatsApp = '50587136805',
    diasAgotados = [],
}: UseHabitacionReservaFormProps) => {
    const form = useForm<HabitacionReservaFormValues>({
        resolver: zodResolver(habitacionReservaSchema),
        defaultValues: {
            check_in: '',
            check_out: '',
            huespedes: String(room.capacidad || 2),
            notas: '',
        },
    });

    const {
        register,
        handleSubmit,
        setValue,
        control,
        formState: { errors, isSubmitting },
    } = form;

    const checkIn = useWatch({ control, name: 'check_in' });
    const checkOut = useWatch({ control, name: 'check_out' });
    const huespedes = useWatch({ control, name: 'huespedes' });

    const totalNochesCalculadas = calcularNoches(checkIn, checkOut);
    const noches = totalNochesCalculadas > 0 ? totalNochesCalculadas : 1;
    const precioNoche = Number(room.precio ?? room.precio_desde ?? 0);
    const totalEstimado = precioNoche * noches;

    const tieneConflictoDisponibilidad = verificarConflictoFechas(
        checkIn,
        checkOut,
        diasAgotados,
    );

    const onSubmit = (data: HabitacionReservaFormValues) => {
        const moneda = room.moneda || '$';
        const telefonoLimpio = telefonoWhatsApp.replace(/\D/g, '');
        const mensaje =
            `¡Hola Hotel Bugambilias! 👋\n\n` +
            `Deseo reservar la suite:\n` +
            `🏨 *${room.nombre}* (${room.categoria || 'Habitación'})\n` +
            `📅 *Llegada:* ${data.check_in}\n` +
            `📅 *Salida:* ${data.check_out}\n` +
            `🌙 *Noches:* ${noches}\n` +
            `👥 *Huéspedes:* ${data.huespedes} personas\n` +
            `💵 *Total estimado:* ${moneda}${totalEstimado}\n` +
            (data.notas ? `📝 *Notas:* ${data.notas}\n\n` : `\n`) +
            `¿Tienen disponibilidad para estas fechas?`;

        const url = `https://wa.me/${telefonoLimpio}?text=${encodeURIComponent(mensaje)}`;

        window.open(url, '_blank', 'noopener,noreferrer');
    };

    return {
        register,
        handleSubmit: handleSubmit(onSubmit),
        setValue,
        errors,
        isSubmitting,
        noches,
        precioNoche,
        totalEstimado,
        checkIn,
        checkOut,
        huespedes,
        tieneConflictoDisponibilidad,
    };
};

export default useHabitacionReservaForm;
