import { z } from 'zod';

export const perfilClienteSchema = z
    .object({
        nombre: z.string().min(2, 'El nombre es obligatorio').max(255),
        email: z.string().email('Ingresa un correo electrónico válido'),
        telefono: z.string().max(30).optional().or(z.literal('')),
        identificacion: z.string().max(50).optional().or(z.literal('')),
        tipo_identificacion: z.string().max(50).optional().or(z.literal('')),
    })
    .superRefine((data, ctx) => {
        if (
            data.tipo_identificacion === 'cedula' &&
            data.identificacion &&
            data.identificacion.trim().length > 0
        ) {
            const regexCedula = /^\d{3}-?\d{6}-?\d{4}[A-Za-z]$/;
            if (!regexCedula.test(data.identificacion.trim())) {
                ctx.addIssue({
                    code: z.ZodIssueCode.custom,
                    message:
                        'El formato de la cédula no es válido (ej. 001-010100-1234A)',
                    path: ['identificacion'],
                });
            }
        }
    });

export type PerfilClienteFormData = z.infer<typeof perfilClienteSchema>;
