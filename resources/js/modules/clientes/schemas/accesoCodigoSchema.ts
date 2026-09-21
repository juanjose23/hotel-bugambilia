import { z } from 'zod';

export const accesoCodigoSchema = z.object({
    codigo: z
        .string()
        .min(3, 'Ingresa un código de reserva válido')
        .max(50, 'El código es demasiado largo')
        .transform((val) => val.trim().toUpperCase()),
});

export type AccesoCodigoFormData = z.infer<typeof accesoCodigoSchema>;
