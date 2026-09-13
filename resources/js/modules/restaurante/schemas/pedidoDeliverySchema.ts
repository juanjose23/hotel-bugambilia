import { z } from 'zod';

export const pedidoDeliverySchema = z.object({
    nombre: z
        .string()
        .min(3, 'El nombre debe tener al menos 3 caracteres')
        .max(100, 'El nombre no puede exceder 100 caracteres'),
    email: z
        .string()
        .min(1, 'El correo electrónico es requerido para la confirmación')
        .email('Ingresa un correo electrónico válido')
        .max(120, 'El correo no puede exceder 120 caracteres'),
    telefono: z
        .string()
        .min(8, 'Ingresa un número de teléfono o WhatsApp válido'),
    departamento: z.string().min(2, 'Selecciona un departamento de entrega'),
    municipio: z.string().min(2, 'Selecciona un municipio o zona de entrega'),
    direccion: z
        .string()
        .min(
            5,
            'Ingresa tu dirección exacta de entrega (calle, número, puntos de referencia)',
        ),
    metodoPago: z.enum(['efectivo', 'stripe']),
    montoPagaCon: z.string().optional(),
    notas: z
        .string()
        .max(250, 'Las notas no pueden exceder 250 caracteres')
        .optional(),
});

export type FormPedidoDelivery = z.infer<typeof pedidoDeliverySchema>;
