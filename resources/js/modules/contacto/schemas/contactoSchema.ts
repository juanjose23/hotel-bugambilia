import { z } from 'zod';

export const contactoSchema = z.object({
    nombre_completo: z
        .string()
        .min(2, 'El nombre debe tener al menos 2 caracteres')
        .max(100, 'El nombre no puede exceder 100 caracteres'),
    email: z
        .string()
        .email('Ingresa un correo electrónico válido')
        .max(150, 'El correo no puede exceder 150 caracteres'),
    telefono: z
        .string()
        .min(8, 'Ingresa un número telefónico válido')
        .max(25, 'El teléfono no puede exceder 25 caracteres'),
    asunto: z.string().min(1, 'Selecciona un motivo de contacto'),
    mensaje: z
        .string()
        .min(10, 'Tu mensaje debe tener al menos 10 caracteres')
        .max(1000, 'El mensaje no puede exceder 1000 caracteres'),
});

export type ContactoFormValues = z.infer<typeof contactoSchema>;
