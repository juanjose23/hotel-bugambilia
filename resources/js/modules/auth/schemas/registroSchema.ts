import { z } from 'zod';

export const registroSchema = z
    .object({
        tipo_persona: z.enum(['natural', 'juridica']),
        primer_nombre: z.string().optional(),
        primer_apellido: z.string().optional(),
        razon_social: z.string().optional(),
        email: z
            .string()
            .min(1, { message: 'El correo electrónico es obligatorio' })
            .email({ message: 'Ingresa un correo electrónico válido' }),
        phone: z.string().optional(),
        tipo_identificacion: z.string().optional(),
        numero_identificacion: z.string().optional(),
        password: z.string().min(8, {
            message: 'La contraseña debe tener al menos 8 caracteres',
        }),
        password_confirmation: z
            .string()
            .min(1, { message: 'Confirma tu contraseña' }),
    })
    .superRefine((data, ctx) => {
        if (data.tipo_persona === 'natural') {
            if (!data.primer_nombre || data.primer_nombre.trim().length < 2) {
                ctx.addIssue({
                    code: z.ZodIssueCode.custom,
                    message: 'El nombre es obligatorio',
                    path: ['primer_nombre'],
                });
            }

            if (
                !data.primer_apellido ||
                data.primer_apellido.trim().length < 2
            ) {
                ctx.addIssue({
                    code: z.ZodIssueCode.custom,
                    message: 'El apellido es obligatorio',
                    path: ['primer_apellido'],
                });
            }
        } else {
            if (!data.razon_social || data.razon_social.trim().length < 2) {
                ctx.addIssue({
                    code: z.ZodIssueCode.custom,
                    message: 'La razón social de la empresa es obligatoria',
                    path: ['razon_social'],
                });
            }

            if (!data.phone || data.phone.trim().length < 7) {
                ctx.addIssue({
                    code: z.ZodIssueCode.custom,
                    message: 'El teléfono es obligatorio para empresas',
                    path: ['phone'],
                });
            }
        }

        if (data.password !== data.password_confirmation) {
            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                message: 'Las contraseñas no coinciden',
                path: ['password_confirmation'],
            });
        }
    });

export type RegistroFormValues = z.infer<typeof registroSchema>;
