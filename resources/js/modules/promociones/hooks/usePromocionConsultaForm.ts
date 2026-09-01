import { zodResolver } from '@hookform/resolvers/zod';
import { useForm } from 'react-hook-form';
import type { PromocionConsultaFormValues } from '../schemas/promocionConsultaSchema';
import { promocionConsultaSchema } from '../schemas/promocionConsultaSchema';
import type { PromocionItem } from '../types';

interface UsePromocionConsultaFormProps {
    promocion: PromocionItem;
    telefonoWhatsApp?: string;
    alCompletar?: () => void;
}

export const usePromocionConsultaForm = ({
    promocion,
    telefonoWhatsApp = '50587136805',
    alCompletar,
}: UsePromocionConsultaFormProps) => {
    const form = useForm<PromocionConsultaFormValues>({
        resolver: zodResolver(promocionConsultaSchema),
        defaultValues: {
            nombre: '',
            email: '',
            telefono: '',
            fecha_tentativa: '',
            huespedes: '2',
            mensaje: '',
        },
    });

    const {
        register,
        handleSubmit,
        setValue,
        watch,
        reset,
        formState: { errors, isSubmitting, isSubmitSuccessful },
    } = form;

    const onSubmit = (data: PromocionConsultaFormValues) => {
        const telefonoLimpio = telefonoWhatsApp.replace(/\D/g, '');
        const precioTxt = `${promocion.moneda}${Number(promocion.precio_final).toFixed(0)}`;

        const texto =
            `¡Hola Hotel Bugambilias! 👋\n\n` +
            `Deseo aprovechar la promoción:\n` +
            `🎁 *${promocion.nombre}* (${promocion.codigo})\n` +
            `💵 *Tarifa Promocional:* ${precioTxt}\n\n` +
            `👤 *Nombre:* ${data.nombre}\n` +
            `📧 *Email:* ${data.email}\n` +
            `📱 *Teléfono:* ${data.telefono}\n` +
            (data.fecha_tentativa
                ? `📅 *Fecha Tentativa:* ${data.fecha_tentativa}\n`
                : '') +
            `👥 *Huéspedes:* ${data.huespedes} personas\n` +
            (data.mensaje ? `📝 *Detalles:* ${data.mensaje}\n\n` : `\n`) +
            `¿Podrían confirmarme disponibilidad y cómo procesar mi reserva?`;

        const url = `https://wa.me/${telefonoLimpio}?text=${encodeURIComponent(texto)}`;

        window.open(url, '_blank', 'noopener,noreferrer');

        if (alCompletar) {
            alCompletar();
        }
    };

    return {
        register,
        handleSubmit: handleSubmit(onSubmit),
        setValue,
        watch,
        reset,
        errors,
        isSubmitting,
        isSubmitSuccessful,
    };
};

export default usePromocionConsultaForm;
