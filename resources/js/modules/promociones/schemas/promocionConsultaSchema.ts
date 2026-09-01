import { z } from 'zod';

export const promocionConsultaSchema = z.object({
    nombre: z.string().min(2, { message: 'Ingresa tu nombre completo' }),
    email: z
        .string()
        .email({ message: 'Ingresa un correo electrónico válido' }),
    telefono: z.string().min(8, { message: 'Ingresa un número de contacto' }),
    fecha_tentativa: z.string().optional(),
    huespedes: z.string(),
    mensaje: z.string().optional(),
});

export type PromocionConsultaFormValues = z.infer<
    typeof promocionConsultaSchema
>;
