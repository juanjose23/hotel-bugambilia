import { zodResolver } from '@hookform/resolvers/zod';
import { useState } from 'react';
import { useForm, useWatch } from 'react-hook-form';
import { usePropiedadesPagina } from '@/modules/shared/hooks/usePropiedadesPagina';
import { reservaMesaSchema } from '../schemas/reservaMesaSchema';
import type { FormReservaMesa } from '../schemas/reservaMesaSchema';
import restaurantService from '../services/restauranteService';
import type {
    DisponibilidadMesasResponse,
    MesaSugerida,
    RespuestaReservaMesaBackend,
} from '../services/restauranteService';

interface PropsUseReservaMesaForm {
    alCerrar?: () => void;
}

export const useReservaMesaForm = ({
    alCerrar,
}: PropsUseReservaMesaForm = {}) => {
    const { auth } = usePropiedadesPagina();
    const usuario = auth?.user;
    const hoy = new Date().toISOString().split('T')[0];

    const [disponibilidad, setDisponibilidad] =
        useState<DisponibilidadMesasResponse | null>(null);
    const [cargandoDisponibilidad, setCargandoDisponibilidad] = useState(false);
    const [reservaCreada, setReservaCreada] =
        useState<RespuestaReservaMesaBackend | null>(null);
    const [errorEnvio, setErrorEnvio] = useState<string | null>(null);

    const form = useForm<FormReservaMesa>({
        resolver: zodResolver(reservaMesaSchema),
        defaultValues: {
            nombre_cliente:
                usuario?.persona?.nombre_completo || usuario?.name || '',
            telefono_cliente: usuario?.persona?.telefono || '',
            email_cliente: usuario?.email || '',
            fecha: hoy,
            hora: '13:00',
            duracion_horas: 1,
            adultos: 2,
            espacio_id: null,
            notas: '',
        },
    });

    const { setValue } = form;
    const fechaSeleccionada = useWatch({
        control: form.control,
        name: 'fecha',
    });
    const horaSeleccionada = useWatch({ control: form.control, name: 'hora' });
    const adultosEnFormulario = useWatch({
        control: form.control,
        name: 'adultos',
    });
    const adultosSeleccionados = Number(adultosEnFormulario || 2);
    const espacioIdSeleccionado = useWatch({
        control: form.control,
        name: 'espacio_id',
    });

    const consultarDisponibilidad = async () => {
        if (!fechaSeleccionada) {
            return;
        }

        setCargandoDisponibilidad(true);

        try {
            const data = await restaurantService.consultarMesasDisponibles({
                fecha: fechaSeleccionada,
                hora: horaSeleccionada || '13:00',
                duracion_horas: Number(form.getValues('duracion_horas') || 1),
                comensales: adultosSeleccionados,
            });
            setDisponibilidad(data);
        } catch (err) {
            console.error('Error al consultar mesas disponibles:', err);
        } finally {
            setCargandoDisponibilidad(false);
        }
    };

    const cambiarFecha = (fecha: string) => {
        setValue('fecha', fecha, { shouldValidate: true, shouldDirty: true });
        void consultarDisponibilidad();
    };

    const seleccionarHora = (hora: string) => {
        setValue('hora', hora, { shouldValidate: true, shouldDirty: true });
        void consultarDisponibilidad();
    };

    const ajustarComensales = (delta: number) => {
        const nuevoValor = Math.min(
            30,
            Math.max(1, adultosSeleccionados + delta),
        );
        setValue('adultos', nuevoValor, {
            shouldValidate: true,
            shouldDirty: true,
        });
        void consultarDisponibilidad();
    };

    const seleccionarComensales = (numero: number) => {
        setValue('adultos', numero, {
            shouldValidate: true,
            shouldDirty: true,
        });
        void consultarDisponibilidad();
    };

    const seleccionarMesaEnPlano = (mesa: MesaSugerida) => {
        setValue('espacio_id', mesa.id, { shouldValidate: true });

        if (mesa.capacidad && mesa.capacidad > adultosSeleccionados) {
            setValue('adultos', mesa.capacidad, { shouldValidate: true });
        }

        void consultarDisponibilidad();
    };

    const agregarNotaRapida = (tag: string) => {
        const notasActuales = form.getValues('notas') || '';

        if (!notasActuales.includes(tag)) {
            const nuevaNota = notasActuales ? `${notasActuales} · ${tag}` : tag;
            setValue('notas', nuevaNota, { shouldValidate: true });
        }
    };

    const onSubmit = async (data: FormReservaMesa) => {
        setErrorEnvio(null);

        try {
            const reservaDatos = await restaurantService.crearReservaMesa(data);
            setReservaCreada(reservaDatos);

            if (reservaDatos.whatsapp_url) {
                window.open(reservaDatos.whatsapp_url, '_blank');
            }
        } catch (err: unknown) {
            const msg =
                err instanceof Error
                    ? err.message
                    : 'No se pudo completar la reservación de mesa. Intenta con otro horario.';
            setErrorEnvio(msg);
        }
    };

    const reiniciar = () => {
        setReservaCreada(null);
        setErrorEnvio(null);
        setDisponibilidad(null);
        form.reset();

        if (alCerrar) {
            alCerrar();
        }
    };

    return {
        form,
        register: form.register,
        onSubmit: form.handleSubmit(onSubmit),
        errors: form.formState.errors,
        isSubmitting: form.formState.isSubmitting,
        disponibilidad,
        cargandoDisponibilidad,
        reservaCreada,
        errorEnvio,
        fechaSeleccionada,
        horaSeleccionada,
        adultosSeleccionados,
        espacioIdSeleccionado,
        cambiarFecha,
        seleccionarHora,
        ajustarComensales,
        seleccionarComensales,
        seleccionarMesaEnPlano,
        agregarNotaRapida,
        consultarDisponibilidad,
        reiniciar,
        setValue,
        watch: form.watch,
    };
};
