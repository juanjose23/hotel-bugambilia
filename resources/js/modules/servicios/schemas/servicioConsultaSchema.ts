import { z } from 'zod';

export const servicioConsultaSchema = z.object({
    nombre: z
        .string()
        .min(2, 'El nombre debe tener al menos 2 caracteres.')
        .max(100, 'El nombre no puede exceder 100 caracteres.'),
    email: z
        .string()
        .email('Por favor ingresa un correo electrónico válido.')
        .max(120, 'El correo no puede exceder 120 caracteres.'),
    telefono: z
        .string()
        .min(8, 'Ingresa un número telefónico válido de al menos 8 dígitos.')
        .max(20, 'El teléfono es demasiado largo.'),
    fecha: z
        .string()
        .min(1, 'Por favor selecciona una fecha estimada para el servicio.'),
    personas: z.string().min(1, 'Indica la cantidad de personas.'),
    notas: z
        .string()
        .max(500, 'Las notas no pueden exceder 500 caracteres.')
        .optional(),
});

export type ServicioConsultaFormData = z.infer<typeof servicioConsultaSchema>;
