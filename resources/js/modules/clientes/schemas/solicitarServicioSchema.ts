import { z } from 'zod';

export const solicitarServicioSchema = z.object({
    servicio_id: z.number().min(1, 'Debes seleccionar un servicio.'),
    cantidad: z
        .number()
        .min(1, 'La cantidad mínima es 1')
        .max(50, 'Máximo 50 unidades'),
    notas: z
        .string()
        .max(500, 'Las notas no pueden superar 500 caracteres')
        .optional(),
});

export type SolicitarServicioFormData = z.infer<typeof solicitarServicioSchema>;
