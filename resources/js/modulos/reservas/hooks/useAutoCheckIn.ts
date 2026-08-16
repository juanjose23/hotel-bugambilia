import { useForm } from '@inertiajs/react';
import { useState } from 'react';
import type { SyntheticEvent } from 'react';
import type {
    ReservaAutoCheckInProps,
    HuespedInput,
} from '../interfaces/autoCheckInInterfaces';

export const useAutoCheckIn = (reserva?: ReservaAutoCheckInProps) => {
    const reservaDatos: ReservaAutoCheckInProps = reserva || {
        codigoReserva: 'RES-2026-8849',
        clienteNombre: 'Juan José Ríos',
        clienteEmail: 'juan@ejemplo.com',
        clienteTelefono: '+505 8888 9999',
        habitacionNombre: 'Suite Nupcial 204',
        categoriaHabitacion: 'Suite Premium Vista Jardín',
        capacidadAdultos: 2,
        capacidadNinos: 2,
        fechaEntrada: '2026-07-23',
        fechaSalida: '2026-07-26',
        solicitaCuentaInicial: true,
        limiteCuentaInicial: 500,
    };

    const [step, setStep] = useState<number>(1);
    const [completado, setCompletado] = useState<boolean>(false);

    const form = useForm({
        codigoReserva: reservaDatos.codigoReserva,
        titularNombre: reservaDatos.clienteNombre,
        titularIdentificacion: '',
        titularTelefono: reservaDatos.clienteTelefono,
        titularEmail: reservaDatos.clienteEmail,
        huespedes: [
            {
                nombre: reservaDatos.clienteNombre,
                identificacion: '',
                tipo: 'adulto',
                esTitular: true,
            },
        ] as HuespedInput[],
        solicitaCuenta: reservaDatos.solicitaCuentaInicial ?? true,
        limiteCuenta: reservaDatos.limiteCuentaInicial ?? 500,
        personasAutorizadas: '',
        formaPagoPrevista: 'tarjeta',
        aceptaPoliticas: false,
        firmaDigital: '',
    });

    const agregarHuesped = () => {
        form.setData('huespedes', [
            ...form.data.huespedes,
            {
                nombre: '',
                identificacion: '',
                tipo: 'adulto',
                esTitular: false,
            },
        ]);
    };

    const eliminarHuesped = (index: number) => {
        if (form.data.huespedes[index]?.esTitular) {
            return;
        }

        form.setData(
            'huespedes',
            form.data.huespedes.filter((_, idx) => idx !== index),
        );
    };

    const actualizarHuesped = (
        index: number,
        campo: keyof HuespedInput,
        valor: string | boolean,
    ) => {
        const nuevos = [...form.data.huespedes];

        if (nuevos[index]) {
            nuevos[index] = { ...nuevos[index], [campo]: valor };
            form.setData('huespedes', nuevos);
        }
    };

    const siguientePaso = () => {
        if (step < 4) {
            setStep(step + 1);
        }
    };

    const anteriorPaso = () => {
        if (step > 1) {
            setStep(step - 1);
        }
    };

    const finalizarCheckIn = (e?: SyntheticEvent) => {
        if (e && e.preventDefault) {
            e.preventDefault();
        }

        setCompletado(true);
    };

    return {
        reservaDatos,
        step,
        setStep,
        completado,
        form,
        agregarHuesped,
        eliminarHuesped,
        actualizarHuesped,
        siguientePaso,
        anteriorPaso,
        finalizarCheckIn,
    };
};
