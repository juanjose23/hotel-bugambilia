import { z } from 'zod';

export const valoracionSchema = z.object({
    estrellas: z.number().int().min(1, 'Selecciona al menos 1 estrella').max(5),
    comentario: z
        .string()
        .max(1000, 'El comentario no puede exceder 1000 caracteres')
        .optional(),
});

export type ValoracionFormValues = z.infer<typeof valoracionSchema>;
