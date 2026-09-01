import { z } from 'zod';

export const espacioReservaSchema = z.object({
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
    tipo_evento: z
        .string()
        .min(
            1,
            'Por favor especifica el tipo de evento (corporativo, boda, cumpleaños, etc.).',
        ),
    fecha: z.string().min(1, 'Por favor selecciona la fecha programada.'),
    hora_inicio: z.string().min(1, 'Por favor selecciona la hora de inicio.'),
    hora_fin: z
        .string()
        .min(1, 'Por favor selecciona la hora estimada de finalización.'),
    asistentes: z.string().min(1, 'Indica el número estimado de asistentes.'),
    requiere_catering: z.boolean(),
    notas: z
        .string()
        .max(500, 'Las notas no pueden exceder 500 caracteres.')
        .optional(),
});

export type EspacioReservaFormData = z.infer<typeof espacioReservaSchema>;
