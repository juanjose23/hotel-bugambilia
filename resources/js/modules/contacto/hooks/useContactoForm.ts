import { zodResolver } from '@hookform/resolvers/zod';
import { useForm } from 'react-hook-form';
import { toast } from 'sonner';
import type { ContactoFormValues } from '../schemas/contactoSchema';
import { contactoSchema } from '../schemas/contactoSchema';

interface UseContactoFormProps {
    telefonoWhatsApp?: string;
}

export const useContactoForm = ({
    telefonoWhatsApp = '+50587136805',
}: UseContactoFormProps = {}) => {
    const form = useForm<ContactoFormValues>({
        resolver: zodResolver(contactoSchema),
        defaultValues: {
            nombre_completo: '',
            email: '',
            telefono: '',
            asunto: 'Consulta General',
            mensaje: '',
        },
        mode: 'onTouched',
    });

    const {
        register,
        handleSubmit,
        setValue,
        watch,
        reset,
        formState: { errors, isSubmitting, isSubmitSuccessful },
    } = form;

    const onSubmit = async (data: ContactoFormValues) => {
        try {
            // Simulamos guardado/procesamiento con latencia visual de feedback
            await new Promise((resolve) => setTimeout(resolve, 800));

            toast.success('¡Mensaje enviado con éxito!', {
                description:
                    'Nuestro equipo de recepción en Estelí te responderá a la brevedad.',
            });

            // Opción de enviar también a WhatsApp
            const numLimpio = telefonoWhatsApp.replace(/\D/g, '');
            const textoWhatsApp = encodeURIComponent(
                `*Nuevo Mensaje de Contacto - Hotel Bugambilias*\n` +
                    `👤 *Nombre:* ${data.nombre_completo}\n` +
                    `📧 *Email:* ${data.email}\n` +
                    `📞 *Teléfono:* ${data.telefono}\n` +
                    `📌 *Motivo:* ${data.asunto}\n` +
                    `💬 *Mensaje:* ${data.mensaje}`,
            );

            // Guardamos en estado y permitimos abrir WhatsApp si el usuario lo desea
            window.sessionStorage.setItem(
                'ultimo_mensaje_contacto',
                `https://wa.me/${numLimpio}?text=${textoWhatsApp}`,
            );

            reset();
        } catch {
            toast.error('Error al enviar el mensaje', {
                description:
                    'Por favor intenta de nuevo o comunícate vía WhatsApp.',
            });
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

export default useContactoForm;
