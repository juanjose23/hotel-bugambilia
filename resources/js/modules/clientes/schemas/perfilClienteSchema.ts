import { z } from 'zod';

export const perfilClienteSchema = z.object({
    nombre: z.string().min(2, 'El nombre es obligatorio').max(255),
    email: z.string().email('Ingresa un correo electrónico válido'),
    telefono: z.string().max(30).optional().or(z.literal('')),
    identificacion: z.string().max(50).optional().or(z.literal('')),
    tipo_identificacion: z.string().max(50).optional().or(z.literal('')),
});

export type PerfilClienteFormData = z.infer<typeof perfilClienteSchema>;
