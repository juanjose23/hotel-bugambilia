import { z } from 'zod';

export const reservaMesaSchema = z.object({
    nombre_cliente: z
        .string()
        .min(3, 'El nombre debe tener al menos 3 caracteres')
        .max(100, 'El nombre no puede exceder 100 caracteres'),
    telefono_cliente: z
        .string()
        .min(8, 'Ingresa un número de teléfono o WhatsApp válido'),
    email_cliente: z
        .string()
        .email('Ingresa un correo electrónico válido')
        .optional()
        .or(z.literal('')),
    fecha: z.string().min(1, 'Selecciona la fecha de la reservación'),
    hora: z.string().min(1, 'Selecciona el horario de tu preferencia'),
    duracion_horas: z.number().min(1).max(5),
    adultos: z
        .number()
        .min(1, 'Mínimo 1 comensal')
        .max(30, 'Máximo 30 personas'),
    espacio_id: z.number().optional().nullable(),
    notas: z
        .string()
        .max(250, 'Las notas no pueden exceder 250 caracteres')
        .optional()
        .or(z.literal('')),
});

export type FormReservaMesa = z.infer<typeof reservaMesaSchema>;
