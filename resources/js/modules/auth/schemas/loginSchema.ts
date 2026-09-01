import { z } from 'zod';

export const loginSchema = z.object({
    email: z
        .string()
        .min(1, { message: 'El correo electrónico es requerido' })
        .email({ message: 'Ingresa un correo electrónico válido' }),
    password: z.string().min(1, { message: 'La contraseña es requerida' }),
    remember: z.boolean(),
});

export type LoginFormValues = z.infer<typeof loginSchema>;
