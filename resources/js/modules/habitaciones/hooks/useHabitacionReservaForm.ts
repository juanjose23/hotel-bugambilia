import { zodResolver } from '@hookform/resolvers/zod';
import { useForm, useWatch } from 'react-hook-form';
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

    // Calcular noches entre fechas
    const calcularNoches = (): number => {
        if (!checkIn || !checkOut) {
            return 1;
        }

        try {
            const d1 = new Date(checkIn);
            const d2 = new Date(checkOut);
            const diffTime = d2.getTime() - d1.getTime();
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            return diffDays > 0 ? diffDays : 1;
        } catch {
            return 1;
        }
    };

    const noches = calcularNoches();
    const precioNoche = Number(room.precio ?? room.precio_desde ?? 0);
    const totalEstimado = precioNoche * noches;

    const tieneConflictoDisponibilidad = (() => {
        if (!checkIn || !checkOut || diasAgotados.length === 0) {
            return false;
        }

        try {
            const d1 = new Date(checkIn);
            const d2 = new Date(checkOut);

            for (let d = new Date(d1); d < d2; d.setDate(d.getDate() + 1)) {
                const fStr = d.toISOString().split('T')[0];

                if (diasAgotados.includes(fStr)) {
                    return true;
                }
            }
        } catch {
            return false;
        }

        return false;
    })();

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
