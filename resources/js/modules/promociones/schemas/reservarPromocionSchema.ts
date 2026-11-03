import { z } from 'zod';

export const reservarPromocionSchema = z.object({
    opcion: z.string().min(1, 'Selecciona dónde utilizar la promoción'),
    nombre_cliente: z.string().min(3, 'Escribe tu nombre completo').max(150),
    telefono_cliente: z
        .string()
        .min(8, 'Escribe un teléfono de contacto')
        .max(25),
    email_cliente: z.email('Escribe un correo válido'),
    fecha_check_in: z.string().min(1, 'Selecciona la fecha'),
    fecha_check_out: z.string(),
    hora_reserva: z
        .string()
        .regex(/^([01]\d|2[0-3]):[0-5]\d$/, 'Selecciona la hora'),
    duracion_horas: z.number().int().min(1).max(5),
    adultos: z.number().int().min(1).max(30),
    tipo_pago_reserva: z.enum(['sin_pago', 'abono_50', 'pago_total']),
    notas: z.string().max(255),
});
export type ReservarPromocionValues = z.infer<typeof reservarPromocionSchema>;
