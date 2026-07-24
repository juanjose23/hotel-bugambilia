import { Head, useForm } from '@inertiajs/react';
import {
    CheckCircle2,
    ChevronRight,
    ChevronLeft,
    UserCheck,
    Users,
    CreditCard,
    FileCheck2,
    QrCode,
    ShieldCheck,
    Sparkles,
    AlertCircle,
} from 'lucide-react';
import React, { useState } from 'react';

interface HuespedInput {
    nombre: string;
    identificacion: string;
    tipo: 'adulto' | 'nino' | 'infante';
    esTitular: boolean;
}

interface ReservaProps {
    codigoReserva: string;
    clienteNombre: string;
    clienteEmail: string;
    clienteTelefono: string;
    habitacionNombre?: string;
    categoriaHabitacion?: string;
    capacidadAdultos: number;
    capacidadNinos: number;
    fechaEntrada: string;
    fechaSalida: string;
    solicitaCuentaInicial?: boolean;
    limiteCuentaInicial?: number;
}

export default function AutoCheckIn({
    reserva,
    politicas = [],
}: {
    reserva?: ReservaProps;
    politicas?: Array<{ id: number; nombre: string; descripcion: string }>;
}) {
    const reservaDemo: ReservaProps = reserva || {
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

    const { data, setData, processing } = useForm({
        codigoReserva: reservaDemo.codigoReserva,
        titularNombre: reservaDemo.clienteNombre,
        titularIdentificacion: '',
        titularTelefono: reservaDemo.clienteTelefono,
        titularEmail: reservaDemo.clienteEmail,
        huespedes: [
            {
                nombre: reservaDemo.clienteNombre,
                identificacion: '',
                tipo: 'adulto',
                esTitular: true,
            },
        ] as HuespedInput[],
        solicitaCuenta: reservaDemo.solicitaCuentaInicial ?? true,
        limiteCuenta: reservaDemo.limiteCuentaInicial ?? 500,
        personasAutorizadas: '',
        formaPagoPrevista: 'tarjeta',
        aceptaPoliticas: false,
        firmaDigital: '',
    });

    const agregarHuesped = () => {
        setData('huespedes', [
            ...data.huespedes,
            {
                nombre: '',
                identificacion: '',
                tipo: 'adulto',
                esTitular: false,
            },
        ]);
    };

    const eliminarHuesped = (index: number) => {
        if (data.huespedes[index]?.esTitular) {
            return;
        }

        const filtrados = data.huespedes.filter((_, i) => i !== index);
        setData('huespedes', filtrados);
    };

    const actualizarHuesped = <K extends keyof HuespedInput>(
        index: number,
        field: K,
        value: HuespedInput[K],
    ) => {
        const copia = [...data.huespedes];
        copia[index] = { ...copia[index], [field]: value };
        setData('huespedes', copia);
    };

    const totalAdultos = data.huespedes.filter(
        (h) => h.tipo === 'adulto',
    ).length;
    const totalNinos = data.huespedes.filter((h) => h.tipo === 'nino').length;

    const excesoAdultos = totalAdultos > reservaDemo.capacidadAdultos;
    const excesoNinos = totalNinos > reservaDemo.capacidadNinos;

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setCompletado(true);
    };

    return (
        <div className="flex min-h-screen flex-col bg-slate-950 font-sans text-slate-100 antialiased selection:bg-emerald-500 selection:text-slate-900">
            <Head title={`Auto Check-in | ${reservaDemo.codigoReserva}`} />

            {/* Header */}
            <header className="sticky top-0 z-50 border-b border-slate-800 bg-slate-900/80 backdrop-blur-md">
                <div className="mx-auto flex max-w-4xl items-center justify-between px-6 py-4">
                    <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 shadow-lg shadow-emerald-500/20">
                            <Sparkles className="h-5 w-5 text-slate-950" />
                        </div>
                        <div>
                            <h1 className="text-lg font-bold tracking-tight text-white">
                                Hotel Bugambilias
                            </h1>
                            <p className="text-xs text-slate-400">
                                Portal Express Auto Check-in
                            </p>
                        </div>
                    </div>
                    <div className="text-right">
                        <span className="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-400">
                            <ShieldCheck className="h-3.5 w-3.5" /> Reserva
                            Confirmada
                        </span>
                    </div>
                </div>
            </header>

            {/* Main Container */}
            <main className="mx-auto w-full max-w-4xl flex-1 px-6 py-8">
                {!completado ? (
                    <div>
                        {/* Step Wizard Indicator */}
                        <div className="mb-8">
                            <div className="mb-3 flex items-center justify-between text-xs font-semibold text-slate-400">
                                <span
                                    className={
                                        step === 1 ? 'text-emerald-400' : ''
                                    }
                                >
                                    1. Identidad & Reserva
                                </span>
                                <span
                                    className={
                                        step === 2 ? 'text-emerald-400' : ''
                                    }
                                >
                                    2. Acompañantes
                                </span>
                                <span
                                    className={
                                        step === 3 ? 'text-emerald-400' : ''
                                    }
                                >
                                    3. Cuenta de Consumo
                                </span>
                                <span
                                    className={
                                        step === 4 ? 'text-emerald-400' : ''
                                    }
                                >
                                    4. Políticas & Firma
                                </span>
                            </div>
                            <div className="h-2 w-full overflow-hidden rounded-full bg-slate-800">
                                <div
                                    className="h-full bg-gradient-to-r from-emerald-500 to-teal-400 transition-all duration-500"
                                    style={{ width: `${(step / 4) * 100}%` }}
                                />
                            </div>
                        </div>

                        {/* Step Content */}
                        <form onSubmit={handleSubmit} className="space-y-6">
                            {step === 1 && (
                                <div className="space-y-6 rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl">
                                    <div className="flex items-center gap-3 border-b border-slate-800 pb-4">
                                        <UserCheck className="h-6 w-6 text-emerald-400" />
                                        <div>
                                            <h2 className="text-lg font-semibold text-white">
                                                Confirmar Titular de la Reserva
                                            </h2>
                                            <p className="text-xs text-slate-400">
                                                Verifique sus datos personales y
                                                la información del alojamiento
                                            </p>
                                        </div>
                                    </div>

                                    {/* Summary Card */}
                                    <div className="grid grid-cols-1 gap-4 rounded-xl border border-slate-800 bg-slate-950/60 p-4 md:grid-cols-2">
                                        <div>
                                            <span className="block text-xs text-slate-400">
                                                Código de Reserva
                                            </span>
                                            <strong className="font-mono text-base text-emerald-400">
                                                {reservaDemo.codigoReserva}
                                            </strong>
                                        </div>
                                        <div>
                                            <span className="block text-xs text-slate-400">
                                                Habitación Asignada
                                            </span>
                                            <strong className="text-slate-200">
                                                {reservaDemo.habitacionNombre ||
                                                    'Por asignar'}
                                            </strong>
                                        </div>
                                        <div>
                                            <span className="block text-xs text-slate-400">
                                                Fecha de Entrada
                                            </span>
                                            <strong className="text-slate-300">
                                                {reservaDemo.fechaEntrada}
                                            </strong>
                                        </div>
                                        <div>
                                            <span className="block text-xs text-slate-400">
                                                Fecha de Salida
                                            </span>
                                            <strong className="text-slate-300">
                                                {reservaDemo.fechaSalida}
                                            </strong>
                                        </div>
                                    </div>

                                    <div className="space-y-4">
                                        <div>
                                            <label className="mb-1 block text-xs font-semibold text-slate-300">
                                                Nombre Completo del Titular *
                                            </label>
                                            <input
                                                type="text"
                                                className="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-sm text-white outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                                value={data.titularNombre}
                                                onChange={(e) =>
                                                    setData(
                                                        'titularNombre',
                                                        e.target.value,
                                                    )
                                                }
                                                required
                                            />
                                        </div>

                                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            <div>
                                                <label className="mb-1 block text-xs font-semibold text-slate-300">
                                                    Documento de Identificación
                                                    (DNI / Cédula / Pasaporte) *
                                                </label>
                                                <input
                                                    type="text"
                                                    placeholder="Ej. 001-010190-0001A"
                                                    className="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-sm text-white outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                                    value={
                                                        data.titularIdentificacion
                                                    }
                                                    onChange={(e) => {
                                                        setData(
                                                            'titularIdentificacion',
                                                            e.target.value,
                                                        );
                                                        actualizarHuesped(
                                                            0,
                                                            'identificacion',
                                                            e.target.value,
                                                        );
                                                    }}
                                                    required
                                                />
                                            </div>

                                            <div>
                                                <label className="mb-1 block text-xs font-semibold text-slate-300">
                                                    Teléfono de Contacto *
                                                </label>
                                                <input
                                                    type="text"
                                                    className="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-sm text-white outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                                    value={data.titularTelefono}
                                                    onChange={(e) =>
                                                        setData(
                                                            'titularTelefono',
                                                            e.target.value,
                                                        )
                                                    }
                                                    required
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            )}

                            {step === 2 && (
                                <div className="space-y-6 rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl">
                                    <div className="flex items-center justify-between border-b border-slate-800 pb-4">
                                        <div className="flex items-center gap-3">
                                            <Users className="h-6 w-6 text-emerald-400" />
                                            <div>
                                                <h2 className="text-lg font-semibold text-white">
                                                    Registro de Acompañantes y
                                                    Huéspedes
                                                </h2>
                                                <p className="text-xs text-slate-400">
                                                    Registre todos los huéspedes
                                                    que se hospedarán en la
                                                    habitación
                                                </p>
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            onClick={agregarHuesped}
                                            className="rounded-lg bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-400 transition hover:bg-emerald-500/20"
                                        >
                                            + Agregar Huésped
                                        </button>
                                    </div>

                                    {/* Capacidad Banner */}
                                    <div className="flex items-center justify-between rounded-xl border border-slate-800 bg-slate-950 p-3.5 text-xs">
                                        <span className="text-slate-300">
                                            Capacidad Máxima:{' '}
                                            <strong className="text-emerald-400">
                                                {reservaDemo.capacidadAdultos}{' '}
                                                Adultos
                                            </strong>{' '}
                                            |{' '}
                                            <strong className="text-teal-400">
                                                {reservaDemo.capacidadNinos}{' '}
                                                Niños
                                            </strong>
                                        </span>
                                        <span className="text-slate-400">
                                            Registrados: {totalAdultos} Adultos,{' '}
                                            {totalNinos} Niños
                                        </span>
                                    </div>

                                    {(excesoAdultos || excesoNinos) && (
                                        <div className="flex items-center gap-3 rounded-xl border border-rose-500/20 bg-rose-500/10 p-3.5 text-xs text-rose-300">
                                            <AlertCircle className="h-5 w-5 shrink-0" />
                                            <span>
                                                La cantidad de huéspedes
                                                registrados supera la capacidad
                                                máxima de la habitación
                                                seleccionada.
                                            </span>
                                        </div>
                                    )}

                                    <div className="space-y-4">
                                        {data.huespedes.map((huesped, idx) => (
                                            <div
                                                key={idx}
                                                className="relative space-y-3 rounded-xl border border-slate-800 bg-slate-950/80 p-4"
                                            >
                                                <div className="flex items-center justify-between">
                                                    <span className="text-xs font-semibold text-emerald-400">
                                                        {huesped.esTitular
                                                            ? 'Huésped Titular'
                                                            : `Acompañante #${idx}`}
                                                    </span>
                                                    {!huesped.esTitular && (
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                eliminarHuesped(
                                                                    idx,
                                                                )
                                                            }
                                                            className="text-xs font-semibold text-rose-400 hover:text-rose-300"
                                                        >
                                                            Eliminar
                                                        </button>
                                                    )}
                                                </div>

                                                <div className="grid grid-cols-1 gap-3 md:grid-cols-3">
                                                    <div>
                                                        <label className="mb-1 block text-xs text-slate-400">
                                                            Nombre Completo
                                                        </label>
                                                        <input
                                                            type="text"
                                                            className="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-1.5 text-xs text-white outline-none focus:border-emerald-500"
                                                            value={
                                                                huesped.nombre
                                                            }
                                                            onChange={(e) =>
                                                                actualizarHuesped(
                                                                    idx,
                                                                    'nombre',
                                                                    e.target
                                                                        .value,
                                                                )
                                                            }
                                                            required
                                                        />
                                                    </div>

                                                    <div>
                                                        <label className="mb-1 block text-xs text-slate-400">
                                                            Identificación
                                                        </label>
                                                        <input
                                                            type="text"
                                                            className="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-1.5 text-xs text-white outline-none focus:border-emerald-500"
                                                            value={
                                                                huesped.identificacion
                                                            }
                                                            onChange={(e) =>
                                                                actualizarHuesped(
                                                                    idx,
                                                                    'identificacion',
                                                                    e.target
                                                                        .value,
                                                                )
                                                            }
                                                            placeholder="Opcional para menores"
                                                        />
                                                    </div>

                                                    <div>
                                                        <label className="mb-1 block text-xs text-slate-400">
                                                            Categoría / Edad
                                                        </label>
                                                        <select
                                                            className="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-1.5 text-xs text-white outline-none focus:border-emerald-500"
                                                            value={huesped.tipo}
                                                            onChange={(e) =>
                                                                actualizarHuesped(
                                                                    idx,
                                                                    'tipo',
                                                                    e.target
                                                                        .value as any,
                                                                )
                                                            }
                                                        >
                                                            <option value="adulto">
                                                                Adulto
                                                            </option>
                                                            <option value="nino">
                                                                Niño
                                                            </option>
                                                            <option value="infante">
                                                                Infante
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {step === 3 && (
                                <div className="space-y-6 rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl">
                                    <div className="flex items-center gap-3 border-b border-slate-800 pb-4">
                                        <CreditCard className="h-6 w-6 text-emerald-400" />
                                        <div>
                                            <h2 className="text-lg font-semibold text-white">
                                                Solicitud de Cuenta de Consumo
                                                en Estancia
                                            </h2>
                                            <p className="text-xs text-slate-400">
                                                Active su folio para consumir en
                                                restaurante, minibar y servicios
                                                durante su estadía
                                            </p>
                                        </div>
                                    </div>

                                    <div className="flex items-center justify-between rounded-xl border border-slate-800 bg-slate-950 p-4">
                                        <div>
                                            <span className="block text-sm font-semibold text-white">
                                                ¿Desea solicitar una cuenta de
                                                consumo?
                                            </span>
                                            <span className="text-xs text-slate-400">
                                                La cuenta será verificada y
                                                activada por Recepción al
                                                momento de la entrega de llaves.
                                            </span>
                                        </div>
                                        <input
                                            type="checkbox"
                                            className="h-5 w-5 cursor-pointer rounded accent-emerald-500"
                                            checked={data.solicitaCuenta}
                                            onChange={(e) =>
                                                setData(
                                                    'solicitaCuenta',
                                                    e.target.checked,
                                                )
                                            }
                                        />
                                    </div>

                                    {data.solicitaCuenta && (
                                        <div className="space-y-4 pt-2">
                                            <div>
                                                <label className="mb-1 block text-xs font-semibold text-slate-300">
                                                    Límite de Consumo Estimado
                                                    (C$)
                                                </label>
                                                <input
                                                    type="number"
                                                    min="0"
                                                    step="50"
                                                    className="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-sm text-white outline-none focus:border-emerald-500"
                                                    value={data.limiteCuenta}
                                                    onChange={(e) =>
                                                        setData(
                                                            'limiteCuenta',
                                                            Number(
                                                                e.target.value,
                                                            ),
                                                        )
                                                    }
                                                />
                                            </div>

                                            <div>
                                                <label className="mb-1 block text-xs font-semibold text-slate-300">
                                                    Personas Autorizadas para
                                                    Cargar Consumos
                                                </label>
                                                <input
                                                    type="text"
                                                    placeholder="Ej. Titular y acompañante principal"
                                                    className="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-sm text-white outline-none focus:border-emerald-500"
                                                    value={
                                                        data.personasAutorizadas
                                                    }
                                                    onChange={(e) =>
                                                        setData(
                                                            'personasAutorizadas',
                                                            e.target.value,
                                                        )
                                                    }
                                                />
                                            </div>

                                            <div>
                                                <label className="mb-1 block text-xs font-semibold text-slate-300">
                                                    Forma Prevista de Pago en
                                                    Check-out
                                                </label>
                                                <select
                                                    className="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-sm text-white outline-none focus:border-emerald-500"
                                                    value={
                                                        data.formaPagoPrevista
                                                    }
                                                    onChange={(e) =>
                                                        setData(
                                                            'formaPagoPrevista',
                                                            e.target.value,
                                                        )
                                                    }
                                                >
                                                    <option value="tarjeta">
                                                        Tarjeta de Crédito /
                                                        Débito
                                                    </option>
                                                    <option value="efectivo">
                                                        Efectivo
                                                    </option>
                                                    <option value="transferencia">
                                                        Transferencia Bancaria
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            )}

                            {step === 4 && (
                                <div className="space-y-6 rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl">
                                    <div className="flex items-center gap-3 border-b border-slate-800 pb-4">
                                        <FileCheck2 className="h-6 w-6 text-emerald-400" />
                                        <div>
                                            <h2 className="text-lg font-semibold text-white">
                                                Aceptación de Políticas y Firma
                                                Digital
                                            </h2>
                                            <p className="text-xs text-slate-400">
                                                Finalice el proceso aceptando
                                                los reglamentos del hotel
                                            </p>
                                        </div>
                                    </div>

                                    <div className="max-h-36 space-y-2 overflow-y-auto rounded-xl border border-slate-800 bg-slate-950 p-4 text-xs text-slate-300">
                                        <p className="font-semibold text-white">
                                            Reglamento General de Hospedaje -
                                            Hotel Bugambilias:
                                        </p>
                                        {politicas && politicas.length > 0 ? (
                                            politicas.map((pol, idx) => (
                                                <p key={pol.id || idx}>
                                                    {idx + 1}.{' '}
                                                    <strong className="text-slate-200">
                                                        {pol.nombre}:
                                                    </strong>{' '}
                                                    {pol.descripcion}
                                                </p>
                                            ))
                                        ) : (
                                            <>
                                                <p>
                                                    1. El horario de check-in es
                                                    a partir de las 14:00 hrs y
                                                    el check-out debe realizarse
                                                    antes de las 11:00 hrs.
                                                </p>
                                                <p>
                                                    2. Toda cuenta de consumo
                                                    abierta debe saldarse al
                                                    momento del check-out
                                                    definitivo.
                                                </p>
                                                <p>
                                                    3. Se requiere conservar el
                                                    código QR de pre-check-in
                                                    para presentar en la
                                                    recepción al reclamar sus
                                                    llaves.
                                                </p>
                                            </>
                                        )}
                                    </div>

                                    <div className="flex items-center gap-3">
                                        <input
                                            type="checkbox"
                                            id="politicas"
                                            className="h-5 w-5 cursor-pointer rounded accent-emerald-500"
                                            checked={data.aceptaPoliticas}
                                            onChange={(e) =>
                                                setData(
                                                    'aceptaPoliticas',
                                                    e.target.checked,
                                                )
                                            }
                                            required
                                        />
                                        <label
                                            htmlFor="politicas"
                                            className="cursor-pointer text-xs text-slate-300"
                                        >
                                            Acepto las políticas de hospedaje y
                                            declaro que la información brindada
                                            es fidedigna.
                                        </label>
                                    </div>

                                    <div>
                                        <label className="mb-1 block text-xs font-semibold text-slate-300">
                                            Firma Digital del Titular (Nombre
                                            Completo) *
                                        </label>
                                        <input
                                            type="text"
                                            placeholder="Escriba su nombre completo para firmar electrónicamente"
                                            className="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 font-serif text-sm text-emerald-400 italic outline-none focus:border-emerald-500"
                                            value={data.firmaDigital}
                                            onChange={(e) =>
                                                setData(
                                                    'firmaDigital',
                                                    e.target.value,
                                                )
                                            }
                                            required
                                        />
                                    </div>
                                </div>
                            )}

                            {/* Action Bar */}
                            <div className="flex items-center justify-between pt-4">
                                {step > 1 ? (
                                    <button
                                        type="button"
                                        onClick={() => setStep(step - 1)}
                                        className="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-5 py-2.5 text-sm font-semibold text-slate-200 transition hover:bg-slate-700"
                                    >
                                        <ChevronLeft className="h-4 w-4" />{' '}
                                        Anterior
                                    </button>
                                ) : (
                                    <div />
                                )}

                                {step < 4 ? (
                                    <button
                                        type="button"
                                        onClick={() => setStep(step + 1)}
                                        disabled={excesoAdultos || excesoNinos}
                                        className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-6 py-2.5 text-sm font-bold text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:from-emerald-400 hover:to-teal-400 disabled:opacity-50"
                                    >
                                        Siguiente{' '}
                                        <ChevronRight className="h-4 w-4" />
                                    </button>
                                ) : (
                                    <button
                                        type="submit"
                                        disabled={
                                            !data.aceptaPoliticas ||
                                            !data.firmaDigital ||
                                            processing
                                        }
                                        className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-400 via-teal-400 to-emerald-500 px-8 py-2.5 text-sm font-black text-slate-950 shadow-xl shadow-emerald-500/30 transition hover:from-emerald-300 hover:to-teal-300 disabled:opacity-50"
                                    >
                                        <CheckCircle2 className="h-5 w-5" />{' '}
                                        Finalizar Pre Check-in
                                    </button>
                                )}
                            </div>
                        </form>
                    </div>
                ) : (
                    /* Confirmation Screen with Pre Check-in QR Token */
                    <div className="mx-auto max-w-xl space-y-6 rounded-3xl border border-slate-800 bg-slate-900 p-8 text-center shadow-2xl">
                        <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-400">
                            <QrCode className="h-10 w-10" />
                        </div>

                        <div>
                            <h2 className="mb-2 text-2xl font-bold text-white">
                                ¡Pre Check-in Completado!
                            </h2>
                            <p className="text-xs text-slate-400">
                                Presente este código QR en la recepción del
                                Hotel Bugambilias para recibir sus llaves y
                                activar su estancia.
                            </p>
                        </div>

                        <div className="space-y-4 rounded-2xl border border-slate-800 bg-slate-950 p-6">
                            <div className="mx-auto flex h-44 w-44 items-center justify-center rounded-xl bg-white p-3">
                                {/* Visual QR representation */}
                                <div className="flex h-full w-full flex-col items-center justify-center border-4 border-slate-900 p-2 text-center font-mono text-[10px] leading-tight font-bold text-slate-900">
                                    QR TOKEN
                                    <br />
                                    {reservaDemo.codigoReserva}
                                    <br />
                                    PRE-CHECKIN
                                </div>
                            </div>

                            <div className="space-y-1 text-xs text-slate-300">
                                <p>
                                    Titular:{' '}
                                    <strong>{data.titularNombre}</strong>
                                </p>
                                <p>
                                    Habitación:{' '}
                                    <strong>
                                        {reservaDemo.habitacionNombre}
                                    </strong>
                                </p>
                                <p>
                                    Estado Cuenta:{' '}
                                    <strong className="text-emerald-400">
                                        {data.solicitaCuenta
                                            ? 'SOLICITADA'
                                            : 'NO SOLICITADA'}
                                    </strong>
                                </p>
                            </div>
                        </div>

                        <div className="pt-2">
                            <button
                                type="button"
                                onClick={() => window.print()}
                                className="rounded-xl bg-slate-800 px-6 py-2.5 text-xs font-semibold text-white transition hover:bg-slate-700"
                            >
                                Imprimir / Guardar QR
                            </button>
                        </div>
                    </div>
                )}
            </main>
        </div>
    );
}
