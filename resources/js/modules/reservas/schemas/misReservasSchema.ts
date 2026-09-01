import { z } from 'zod';

export const misReservasSchema = z.object({
    codigo: z.string().trim().min(1, 'Ingresa un código de reserva válido'),
});

export type MisReservasFormValues = z.infer<typeof misReservasSchema>;
