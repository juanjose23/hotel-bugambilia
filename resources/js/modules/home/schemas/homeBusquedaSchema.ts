import { z } from 'zod';

export const homeBusquedaSchema = z.object({
    categoria: z.string().optional(),
    check_in: z.string().optional(),
    check_out: z.string().optional(),
    personas: z.string(),
});

export type HomeBusquedaFormValues = z.infer<typeof homeBusquedaSchema>;
