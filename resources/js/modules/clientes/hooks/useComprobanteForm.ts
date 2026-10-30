import { zodResolver } from '@hookform/resolvers/zod';
import { router } from '@inertiajs/react';
import { useForm } from 'react-hook-form';
import { comprobanteSchema } from '../schemas/comprobanteSchema';
import type { ComprobanteForm } from '../schemas/comprobanteSchema';

export function useComprobanteForm(cuentaId: number) {
    const form = useForm<ComprobanteForm>({
        resolver: zodResolver(comprobanteSchema),
        defaultValues: {
            banco_id: '',
            importe: '',
            fecha: new Date().toLocaleDateString('en-CA'),
            referencia: '',
            clave: crypto.randomUUID(),
            aclaracion_id: null,
        },
    });
    const enviar = form.handleSubmit(async (values) => {
        const data = new FormData();
        data.set('banco_id', values.banco_id);
        data.set('importe', values.importe);
        data.set('fecha', values.fecha);
        data.set('referencia', values.referencia);
        data.set('archivo', values.archivo);
        data.set('clave', values.clave);

        if (values.aclaracion_id !== null) {
            data.set('aclaracion_id', String(values.aclaracion_id));
        }

        await new Promise<void>((resolve) =>
            router.post(`/portal/cuentas/${cuentaId}/comprobantes`, data, {
                preserveScroll: true,
                onError: (errors) => {
                    for (const [key, message] of Object.entries(errors)) {
                        const campo =
                            key in values
                                ? (key as keyof ComprobanteForm)
                                : 'archivo';
                        form.setError(campo, { message });
                    }
                },
                onSuccess: () => {
                    form.reset({
                        banco_id: '',
                        importe: '',
                        fecha: new Date().toLocaleDateString('en-CA'),
                        referencia: '',
                        clave: crypto.randomUUID(),
                        aclaracion_id: null,
                    });
                },
                onFinish: () => resolve(),
            }),
        );
    });

    return { form, enviar };
}
