import { zodResolver } from '@hookform/resolvers/zod';
import { format } from 'date-fns';
import { useCallback, useEffect, useRef, useState } from 'react';
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
    promocionId?: number | null;
}

export const useReservaMesaForm = ({
    alCerrar,
    promocionId,
}: PropsUseReservaMesaForm = {}) => {
    const { auth } = usePropiedadesPagina();
    const usuario = auth?.user;
    const hoy = format(new Date(), 'yyyy-MM-dd');

    const [disponibilidad, setDisponibilidad] =
        useState<DisponibilidadMesasResponse | null>(null);
    const [cargandoDisponibilidad, setCargandoDisponibilidad] = useState(false);
    const [reservaCreada, setReservaCreada] =
        useState<RespuestaReservaMesaBackend | null>(null);

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
            promocion_id: promocionId ?? null,
            items_preorden: [],
        },
    });

    const { setValue, getValues, setError, clearErrors } = form;
    const ultimaConsulta = useRef(0);
    const duracionSeleccionada = useWatch({
        control: form.control,
        name: 'duracion_horas',
    });
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

    const consultarDisponibilidad = useCallback(
        async (signal?: AbortSignal) => {
            const values = getValues();

            if (!values.fecha || !values.hora) {
                return;
            }

            const consulta = ++ultimaConsulta.current;
            setCargandoDisponibilidad(true);
            clearErrors('root.disponibilidad');

            try {
                const data = await restaurantService.consultarMesasDisponibles({
                    fecha: values.fecha,
                    hora: values.hora,
                    duracion_horas: Number(values.duracion_horas || 1),
                    comensales: Number(values.adultos || 2),
                });

                if (consulta === ultimaConsulta.current && !signal?.aborted) {
                    setDisponibilidad(data);
                }
            } catch (err) {
                if (consulta === ultimaConsulta.current && !signal?.aborted) {
                    setDisponibilidad(null);
                    setError('root.disponibilidad', {
                        message:
                            err instanceof Error
                                ? err.message
                                : 'No se pudo consultar la disponibilidad. Intenta nuevamente.',
                    });
                }
            } finally {
                if (consulta === ultimaConsulta.current && !signal?.aborted) {
                    setCargandoDisponibilidad(false);
                }
            }
        },
        [getValues, clearErrors, setError],
    );

    useEffect(() => {
        setValue('espacio_id', null);
        const controller = new AbortController();
        const timer = window.setTimeout(
            () => void consultarDisponibilidad(controller.signal),
            150,
        );

        return () => {
            window.clearTimeout(timer);
            controller.abort();
        };
    }, [
        fechaSeleccionada,
        horaSeleccionada,
        adultosSeleccionados,
        duracionSeleccionada,
        consultarDisponibilidad,
        setValue,
    ]);

    const cambiarFecha = (fecha: string) => {
        setValue('fecha', fecha, { shouldValidate: true, shouldDirty: true });
    };

    const seleccionarHora = (hora: string) => {
        setValue('hora', hora, { shouldValidate: true, shouldDirty: true });
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
    };

    const seleccionarComensales = (numero: number) => {
        setValue('adultos', numero, {
            shouldValidate: true,
            shouldDirty: true,
        });
    };

    const seleccionarMesaEnPlano = (mesa: MesaSugerida) => {
        setValue('espacio_id', mesa.id, { shouldValidate: true });
    };

    const agregarNotaRapida = (tag: string) => {
        const notasActuales = form.getValues('notas') || '';

        if (!notasActuales.includes(tag)) {
            const nuevaNota = notasActuales ? `${notasActuales} · ${tag}` : tag;
            setValue('notas', nuevaNota, { shouldValidate: true });
        }
    };

    const onSubmit = async (data: FormReservaMesa) => {
        clearErrors('root');

        try {
            const reservaDatos = await restaurantService.crearReservaMesa(data);
            setReservaCreada(reservaDatos);
        } catch (err: unknown) {
            const msg =
                err instanceof Error
                    ? err.message
                    : 'No se pudo completar la reservación de mesa. Intenta con otro horario.';
            setError('root.envio', { message: msg });
        }
    };

    const reiniciar = () => {
        setReservaCreada(null);
        clearErrors('root');
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
        errorEnvio:
            form.formState.errors.root?.envio?.message ??
            form.formState.errors.root?.disponibilidad?.message,
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
