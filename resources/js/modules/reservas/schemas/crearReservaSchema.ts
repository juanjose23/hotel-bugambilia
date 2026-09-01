import { z } from 'zod';

export const acompananteSchema = z.object({
    nombre: z.string().min(2, 'El nombre debe tener al menos 2 caracteres'),
    identificacion: z.string().optional(),
    tipo: z.string().optional(),
});

export const servicioAdicionalSchema = z.object({
    servicio_id: z.number().int().positive(),
    cantidad: z.number().int().min(1),
});

export const crearReservaSchema = z
    .object({
        nombre_cliente: z
            .string()
            .min(
                3,
                'El nombre completo es requerido y debe tener al menos 3 caracteres',
            )
            .max(150, 'El nombre no puede exceder 150 caracteres'),
        email_cliente: z
            .string()
            .email('Ingrese un correo electrónico válido')
            .max(150, 'El correo no puede exceder 150 caracteres'),
        telefono_cliente: z
            .string()
            .min(6, 'Ingrese un teléfono de contacto válido')
            .max(50, 'El teléfono es demasiado largo'),
        tipo_reserva: z.string(),
        habitacion_id: z
            .number()
            .int()
            .positive('Debe seleccionar una habitación válida'),
        fecha_check_in: z.string().min(1, 'La fecha de llegada es obligatoria'),
        fecha_check_out: z.string().min(1, 'La fecha de salida es obligatoria'),
        adultos: z.number().int().min(1, 'Al menos 1 adulto').max(20),
        ninos: z.number().int().min(0).max(20),
        canal_pago_reserva: z.string(),
        tipo_pago_reserva: z.string(),
        notas: z
            .string()
            .max(2000, 'Las notas no pueden exceder 2000 caracteres')
            .optional(),
        acompanantes: z.array(acompananteSchema).optional(),
        servicios_adicionales: z.array(servicioAdicionalSchema).optional(),
        beneficio_id: z.number().optional().nullable(),
    })
    .refine(
        (data) => {
            if (!data.fecha_check_in || !data.fecha_check_out) {
                return true;
            }

            return (
                new Date(data.fecha_check_out) > new Date(data.fecha_check_in)
            );
        },
        {
            message:
                'La fecha de salida debe ser posterior a la fecha de llegada',
            path: ['fecha_check_out'],
        },
    );

export type CrearReservaFormInput = z.input<typeof crearReservaSchema>;
export type CrearReservaFormValues = z.infer<typeof crearReservaSchema>;
