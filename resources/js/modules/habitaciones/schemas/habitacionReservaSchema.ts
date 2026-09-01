import { z } from 'zod';

export const habitacionReservaSchema = z.object({
    check_in: z.string().min(1, { message: 'Selecciona la fecha de llegada' }),
    check_out: z.string().min(1, { message: 'Selecciona la fecha de salida' }),
    huespedes: z
        .string()
        .min(1, { message: 'Indica la cantidad de huéspedes' }),
    notas: z.string().optional(),
});

export type HabitacionReservaFormValues = z.infer<
    typeof habitacionReservaSchema
>;

export const habitacionFiltrosSchema = z.object({
    categoria: z.string().optional(),
    buscar: z.string().optional(),
    huespedes: z.string().optional(),
    orden: z.enum(['precio_asc', 'precio_desc', 'popular']).optional(),
});

export type HabitacionFiltrosFormValues = z.infer<
    typeof habitacionFiltrosSchema
>;
