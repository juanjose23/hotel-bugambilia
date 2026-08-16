import { z } from 'zod';

export const esquemaFormularioContacto = z.object({
    firstName: z.string().min(2, 'El nombre debe tener al menos 2 caracteres'),
    lastName: z.string().min(2, 'El apellido debe tener al menos 2 caracteres'),
    email: z.email('Ingrese un correo electrónico válido'),
    phone: z.string().optional(),
    subject: z.string().min(1, 'Seleccione un asunto de la consulta'),
    message: z.string().min(10, 'El mensaje debe tener al menos 10 caracteres'),
});

export type TipoFormularioContacto = z.infer<typeof esquemaFormularioContacto>;
