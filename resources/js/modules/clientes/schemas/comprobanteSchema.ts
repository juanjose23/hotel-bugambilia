import { z } from 'zod';

export const comprobanteSchema = z.object({
    banco_id: z.string().min(1, 'Selecciona el banco de destino.'),
    importe: z
        .string()
        .regex(/^\d+(\.\d{1,2})?$/, 'Usa un importe con hasta dos decimales.')
        .refine((v) => Number(v) > 0, 'Indica un importe positivo.'),
    fecha: z
        .string()
        .regex(/^\d{4}-\d{2}-\d{2}$/, 'Indica la fecha del depósito.'),
    referencia: z
        .string()
        .trim()
        .min(3, 'Escribe la referencia bancaria.')
        .max(150),
    archivo: z
        .custom<File>(
            (v) => typeof File !== 'undefined' && v instanceof File,
            'Adjunta una imagen o PDF.',
        )
        .refine(
            (v) => v instanceof File && v.size <= 5 * 1024 * 1024,
            'El archivo debe pesar hasta 5 MB.',
        )
        .refine(
            (v) =>
                v instanceof File &&
                [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                    'application/pdf',
                ].includes(v.type),
            'Adjunta una imagen JPG, PNG, WebP o un PDF.',
        ),
    clave: z.string().uuid(),
    aclaracion_id: z.number().nullable(),
});
export type ComprobanteForm = z.infer<typeof comprobanteSchema>;
