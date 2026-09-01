import { z } from 'zod';

export const acompananteIndividualSchema = z.object({
    nombre: z
        .string()
        .min(2, 'El nombre debe tener al menos 2 caracteres')
        .max(150),
    identificacion: z.string().max(50).optional().or(z.literal('')),
    tipo: z.enum(['adulto', 'nino', 'bebe']),
});

export const acompanantesSchema = z.object({
    acompanantes: z.array(acompananteIndividualSchema),
});

export type AcompanantesFormData = z.infer<typeof acompanantesSchema>;
