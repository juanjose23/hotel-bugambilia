import { z } from 'zod';

export const cambiarContrasenaSchema = z
    .object({
        current_password: z
            .string()
            .min(1, { message: 'Ingresa tu contraseña actual' }),
        password: z.string().min(8, {
            message: 'La nueva contraseña debe tener al menos 8 caracteres',
        }),
        password_confirmation: z
            .string()
            .min(1, { message: 'Confirma tu nueva contraseña' }),
    })
    .refine((data) => data.password === data.password_confirmation, {
        message: 'Las contraseñas no coinciden',
        path: ['password_confirmation'],
    });

export type CambiarContrasenaFormValues = z.infer<
    typeof cambiarContrasenaSchema
>;
