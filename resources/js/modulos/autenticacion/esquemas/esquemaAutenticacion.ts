import { z } from 'zod';

export const esquemaInicioSesion = z.object({
    email: z.string().email('Ingrese un correo electrónico válido'),
    password: z
        .string()
        .min(6, 'La contraseña debe tener al menos 6 caracteres'),
    remember: z.boolean().optional(),
});

export const esquemaRegistro = z
    .object({
        tipo_persona: z.enum(['natural', 'juridica']),
        primer_nombre: z.string().optional(),
        primer_apellido: z.string().optional(),
        razon_social: z.string().optional(),
        tipo_identificacion: z.enum(['cedula', 'ruc', 'pasaporte']),
        numero_identificacion: z
            .string()
            .min(4, 'Ingrese un número de identificación válido'),
        email: z.string().email('Ingrese un correo electrónico válido'),
        phone: z.string().min(8, 'Ingrese un número telefónico válido'),
        password: z
            .string()
            .min(8, 'La contraseña debe tener al menos 8 caracteres'),
        password_confirmation: z.string().min(8, 'Confirme su contraseña'),
        acceptTerms: z.boolean().refine((val) => val === true, {
            message: 'Debe aceptar los términos y condiciones',
        }),
    })
    .refine((data) => data.password === data.password_confirmation, {
        message: 'Las contraseñas no coinciden',
        path: ['password_confirmation'],
    });

export const esquemaCambiarContrasena = z
    .object({
        email: z.string().email('Ingrese un correo electrónico válido'),
        password: z
            .string()
            .min(8, 'La contraseña debe tener al menos 8 caracteres'),
        password_confirmation: z.string().min(8, 'Confirme su contraseña'),
    })
    .refine((data) => data.password === data.password_confirmation, {
        message: 'Las contraseñas no coinciden',
        path: ['password_confirmation'],
    });

export type TipoInicioSesion = z.infer<typeof esquemaInicioSesion>;
export type TipoRegistro = z.infer<typeof esquemaRegistro>;
export type TipoCambiarContrasena = z.infer<typeof esquemaCambiarContrasena>;
