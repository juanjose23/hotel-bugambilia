import { useState } from 'react';
import { esquemaFormularioContacto } from '../esquemas/esquemaContacto';
import type { TipoFormularioContacto } from '../esquemas/esquemaContacto';

const datosIniciales: TipoFormularioContacto = {
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    subject: 'reserva',
    message: '',
};

export function useFormularioContacto() {
    const [formData, setFormData] =
        useState<TipoFormularioContacto>(datosIniciales);
    const [errors, setErrors] = useState<
        Partial<Record<keyof TipoFormularioContacto, string>>
    >({});
    const [isLoading, setIsLoading] = useState<boolean>(false);
    const [isSubmitted, setIsSubmitted] = useState<boolean>(false);

    const setFieldValue = (
        campo: keyof TipoFormularioContacto,
        valor: string,
    ) => {
        setFormData((prev) => ({ ...prev, [campo]: valor }));

        if (errors[campo]) {
            setErrors((prev) => ({ ...prev, [campo]: undefined }));
        }
    };

    const enviarFormulario = async (e: React.SubmitEvent) => {
        e.preventDefault();
        setErrors({});

        const resultado = esquemaFormularioContacto.safeParse(formData);

        if (!resultado.success) {
            const erroresFormateados: Partial<
                Record<keyof TipoFormularioContacto, string>
            > = {};

            for (const issue of resultado.error.issues) {
                const campo = issue.path[0] as keyof TipoFormularioContacto;

                if (!erroresFormateados[campo]) {
                    erroresFormateados[campo] = issue.message;
                }
            }

            setErrors(erroresFormateados);

            return;
        }

        setIsLoading(true);
        // Simular envío de consulta (Inertia router.post puede conectarse cuando esté el endpoint listo)
        await new Promise((resolve) => setTimeout(resolve, 1000));
        setIsLoading(false);
        setIsSubmitted(true);
    };

    const reiniciarFormulario = () => {
        setFormData(datosIniciales);
        setErrors({});
        setIsSubmitted(false);
    };

    return {
        formData,
        errors,
        isLoading,
        isSubmitted,
        setFieldValue,
        enviarFormulario,
        reiniciarFormulario,
    };
}
