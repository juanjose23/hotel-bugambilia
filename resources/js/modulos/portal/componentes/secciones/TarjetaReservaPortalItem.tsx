import { Link } from '@inertiajs/react';
import {
    Calendar,
    Users,
    CheckCircle2,
    Clock,
    X,
    FileText,
    UserPlus,
    Receipt,
    Tv,
    Utensils,
    Bell,
    CreditCard,
    Sparkles,
    Box,
    UserCheck,
} from 'lucide-react';
import { useState } from 'react';
import type { ReservaClienteDomain } from '@/modulos/clientes/interfaces/cliente';
import { Button } from '@/modulos/compartido/ui/boton';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { Card } from '@/modulos/compartido/ui/tarjeta';
import { formatearNumero } from '@/modulos/compartido/utilidades/formato';
import ModalConfirmarLlegadaReserva from '../modales/ModalConfirmarLlegadaReserva';
import ModalGestionHuespedesReserva from '../modales/ModalGestionHuespedesReserva';
import ModalPedidoRestauranteHabitacion from '../modales/ModalPedidoRestauranteHabitacion';
import ModalSolicitarServicioHabitacion from '../modales/ModalSolicitarServicioHabitacion';

interface PropiedadesTarjetaReservaPortalItem {
    reserva: ReservaClienteDomain;
    onSolicitarCancelacion: (reserva: ReservaClienteDomain) => void;
}

export const TarjetaReservaPortalItem = ({
    reserva,
    onSolicitarCancelacion,
}: PropiedadesTarjetaReservaPortalItem) => {
    const [pestanaActiva, setPestanaActiva] = useState<
        'cuenta' | 'huespedes' | 'activos' | 'servicios' | 'restaurante'
    >('cuenta');

    const [modalServicioAbierto, setModalServicioAbierto] = useState(false);
    const [modalRestauranteAbierto, setModalRestauranteAbierto] =
        useState(false);
    const [modalHuespedesAbierto, setModalHuespedesAbierto] = useState(false);
    const [modalLlegadaAbierto, setModalLlegadaAbierto] = useState(false);

    const esActiva = reserva.estado === 1 || reserva.estado === 2;
    const esCancelada = reserva.estado === 3;
    const montoTotal =
        typeof reserva.total === 'string'
            ? parseFloat(reserva.total) || 0
            : Number(reserva.total) || 0;

    const estadoCuenta = reserva.estado_cuenta || {
        cargos: [
            {
                id: 1,
                fecha: reserva.fecha_check_in || 'Hoy',
                descripcion: `Estancia Base — ${reserva.detalles}`,
                monto: montoTotal,
                categoria: 'Hospedaje',
            },
        ],
        subtotal: Math.round(montoTotal / 1.15),
        impuestos: Math.round(montoTotal - montoTotal / 1.15),
        total: montoTotal,
        total_pagado: montoTotal,
        saldo_pendiente: 0,
    };

    const activosHabitacion =
        reserva.activos_habitacion && reserva.activos_habitacion.length > 0
            ? reserva.activos_habitacion
            : [
                  {
                      id: 1,
                      codigo: 'ACT-TV-55',
                      nombre: 'Smart TV 55" UHD 4K',
                      descripcion: 'Televisor con streaming y cable digital',
                      categoria: 'Entretenimiento',
                      estado: 'Excelente',
                  },
                  {
                      id: 2,
                      codigo: 'ACT-AC-INV',
                      nombre: 'Aire Acondicionado Inverter Split',
                      descripcion: 'Climatización ecológica ultrasilenciosa',
                      categoria: 'Climatización',
                      estado: 'Excelente',
                  },
                  {
                      id: 3,
                      codigo: 'ACT-MNB-01',
                      nombre: 'Minibar Ejecutivo Térmico',
                      descripcion:
                          'Refrigerador personal para bebidas y aperitivos',
                      categoria: 'Confort',
                      estado: 'Excelente',
                  },
                  {
                      id: 4,
                      codigo: 'ACT-SAF-DIG',
                      nombre: 'Caja Fuerte Digital de Alta Seguridad',
                      descripcion:
                          'Bóveda electrónica para laptop y objetos de valor',
                      categoria: 'Seguridad',
                      estado: 'Excelente',
                  },
              ];

    return (
        <Card className="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-border/80 bg-card p-0 font-sans shadow-xs transition-all duration-300 hover:border-bugambilia-500/40 hover:shadow-lg">
            {/* Cabecera Principal con Código & Estado */}
            <div className="flex flex-wrap items-center justify-between gap-3 border-b border-border/50 bg-gradient-to-r from-muted/40 via-card to-background px-5 py-4 sm:px-6">
                <div className="flex items-center gap-3">
                    <div className="flex size-10 items-center justify-center rounded-2xl bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400">
                        <Receipt className="size-5" />
                    </div>
                    <div>
                        <div className="flex items-center gap-2">
                            <span className="text-[10px] font-extrabold tracking-wider text-muted-foreground uppercase">
                                Reserva:
                            </span>
                            <Link
                                href={`/portal/reserva/${reserva.id}?codigo=${encodeURIComponent(reserva.codigo_reserva)}`}
                                className="font-mono text-xs font-black text-bugambilia-600 hover:underline dark:text-bugambilia-400"
                            >
                                #{reserva.codigo_reserva}
                            </Link>
                        </div>
                        <Link
                            href={`/portal/reserva/${reserva.id}?codigo=${encodeURIComponent(reserva.codigo_reserva)}`}
                            className="text-sm font-black text-foreground transition-colors hover:text-bugambilia-600 sm:text-base"
                        >
                            {reserva.detalles}
                        </Link>
                    </div>
                </div>

                <div className="flex items-center gap-2">
                    {esActiva ? (
                        <Badge className="border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-emerald-600 dark:text-emerald-400">
                            <CheckCircle2 className="mr-1 size-3.5" />
                            {reserva.estado_label || 'Confirmada'}
                        </Badge>
                    ) : esCancelada ? (
                        <Badge
                            variant="outline"
                            className="border-rose-500/30 bg-rose-500/10 px-3 py-1 text-rose-600 dark:text-rose-400"
                        >
                            <X className="mr-1 size-3.5" />
                            {reserva.estado_label || 'Cancelada'}
                        </Badge>
                    ) : (
                        <Badge
                            variant="outline"
                            className="border-muted bg-muted/50 px-3 py-1 text-muted-foreground"
                        >
                            <Clock className="mr-1 size-3.5" />
                            {reserva.estado_label || 'Finalizada'}
                        </Badge>
                    )}
                </div>
            </div>

            {/* Pestañas Interactivas del Huésped */}
            <div className="border-b border-border/60 bg-muted/20 px-4 pt-2">
                <div className="no-scrollbar flex space-x-1 overflow-x-auto scroll-smooth">
                    <button
                        type="button"
                        onClick={() => setPestanaActiva('cuenta')}
                        className={`flex shrink-0 cursor-pointer items-center gap-1.5 border-b-2 px-3.5 py-2.5 text-xs font-extrabold transition-all ${
                            pestanaActiva === 'cuenta'
                                ? 'border-bugambilia-600 text-bugambilia-600 dark:border-bugambilia-400 dark:text-bugambilia-400'
                                : 'border-transparent text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        <CreditCard className="size-3.5" />
                        <span>Estado de Cuenta</span>
                    </button>

                    <button
                        type="button"
                        onClick={() => setPestanaActiva('huespedes')}
                        className={`flex shrink-0 cursor-pointer items-center gap-1.5 border-b-2 px-3.5 py-2.5 text-xs font-extrabold transition-all ${
                            pestanaActiva === 'huespedes'
                                ? 'border-bugambilia-600 text-bugambilia-600 dark:border-bugambilia-400 dark:text-bugambilia-400'
                                : 'border-transparent text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        <Users className="size-3.5" />
                        <span>Gestión de Huéspedes</span>
                    </button>

                    <button
                        type="button"
                        onClick={() => setPestanaActiva('activos')}
                        className={`flex shrink-0 cursor-pointer items-center gap-1.5 border-b-2 px-3.5 py-2.5 text-xs font-extrabold transition-all ${
                            pestanaActiva === 'activos'
                                ? 'border-bugambilia-600 text-bugambilia-600 dark:border-bugambilia-400 dark:text-bugambilia-400'
                                : 'border-transparent text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        <Box className="size-3.5" />
                        <span>
                            Habitación & Activos ({activosHabitacion.length})
                        </span>
                    </button>

                    <button
                        type="button"
                        onClick={() => setPestanaActiva('servicios')}
                        className={`flex shrink-0 cursor-pointer items-center gap-1.5 border-b-2 px-3.5 py-2.5 text-xs font-extrabold transition-all ${
                            pestanaActiva === 'servicios'
                                ? 'border-bugambilia-600 text-bugambilia-600 dark:border-bugambilia-400 dark:text-bugambilia-400'
                                : 'border-transparent text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        <Bell className="size-3.5" />
                        <span>Pedir Servicios</span>
                    </button>

                    <button
                        type="button"
                        onClick={() => setPestanaActiva('restaurante')}
                        className={`flex shrink-0 cursor-pointer items-center gap-1.5 border-b-2 px-3.5 py-2.5 text-xs font-extrabold transition-all ${
                            pestanaActiva === 'restaurante'
                                ? 'border-bugambilia-600 text-bugambilia-600 dark:border-bugambilia-400 dark:text-bugambilia-400'
                                : 'border-transparent text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        <Utensils className="size-3.5" />
                        <span>Restaurante</span>
                    </button>
                </div>
            </div>

            {/* Contenido Según Pestaña Activa */}
            <div className="grow space-y-4 p-5 sm:p-6">
                {/* 1. PESTAÑA: ESTADO DE CUENTA & CARGOS (ITEMIZED CHARGES) */}
                {pestanaActiva === 'cuenta' && (
                    <div className="space-y-4">
                        {/* Datos Básicos de Estancia */}
                        <div className="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                            <div className="flex items-center gap-2.5 rounded-2xl border border-border/60 bg-background p-3">
                                <Calendar className="size-4 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                                <div>
                                    <span className="block text-[10px] font-bold text-muted-foreground uppercase">
                                        Fechas de Estancia
                                    </span>
                                    <span className="text-xs font-extrabold text-foreground">
                                        {reserva.fecha_check_in}{' '}
                                        {reserva.fecha_check_out
                                            ? `— ${reserva.fecha_check_out}`
                                            : ''}
                                    </span>
                                </div>
                            </div>

                            <div className="flex items-center gap-2.5 rounded-2xl border border-border/60 bg-background p-3">
                                <Users className="size-4 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                                <div>
                                    <span className="block text-[10px] font-bold text-muted-foreground uppercase">
                                        Huéspedes Registrados
                                    </span>
                                    <span className="text-xs font-extrabold text-foreground">
                                        {reserva.adultos} Adulto(s){' '}
                                        {reserva.ninos > 0
                                            ? `, ${reserva.ninos} Niño(s)`
                                            : ''}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {/* Tabla de Cargos Detallados (Itemized Charges) */}
                        <div className="overflow-hidden rounded-2xl border border-border/80 bg-background shadow-2xs">
                            <div className="flex items-center justify-between border-b border-border/60 bg-muted/40 px-4 py-2.5">
                                <span className="text-xs font-black text-foreground">
                                    Desglose de Cargos & Consumos
                                </span>
                                <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                    Monto USD
                                </span>
                            </div>

                            <div className="divide-y divide-border/60 text-xs">
                                {estadoCuenta.cargos.map((cargo) => (
                                    <div
                                        key={cargo.id}
                                        className="flex items-center justify-between px-4 py-2.5 hover:bg-muted/20"
                                    >
                                        <div>
                                            <span className="block font-bold text-foreground">
                                                {cargo.descripcion}
                                            </span>
                                            <span className="block text-[10px] font-medium text-muted-foreground">
                                                {cargo.fecha} —{' '}
                                                {cargo.categoria}
                                            </span>
                                        </div>
                                        <span className="font-mono font-black text-foreground">
                                            ${formatearNumero(cargo.monto)}
                                        </span>
                                    </div>
                                ))}
                            </div>

                            {/* Resumen Financiero Footer */}
                            <div className="space-y-1.5 border-t border-border/80 bg-muted/30 p-4 text-xs">
                                <div className="flex justify-between font-medium text-muted-foreground">
                                    <span>Subtotal:</span>
                                    <span className="font-mono">
                                        $
                                        {formatearNumero(estadoCuenta.subtotal)}
                                    </span>
                                </div>
                                <div className="flex justify-between font-medium text-muted-foreground">
                                    <span>Impuestos & Tasas (15%):</span>
                                    <span className="font-mono">
                                        $
                                        {formatearNumero(
                                            estadoCuenta.impuestos,
                                        )}
                                    </span>
                                </div>
                                <div className="flex justify-between border-t border-border/50 pt-1 text-sm font-black text-foreground">
                                    <span>Monto Total:</span>
                                    <span className="font-mono text-foreground">
                                        ${formatearNumero(estadoCuenta.total)}
                                    </span>
                                </div>

                                {/* Saldo Pendiente Card estilo LuxeStay */}
                                <div className="mt-3 flex items-center justify-between rounded-2xl bg-slate-900 p-3.5 text-white dark:bg-slate-950">
                                    <div>
                                        <span className="block text-[10px] font-extrabold tracking-wider text-slate-400 uppercase">
                                            Saldo Pendiente
                                        </span>
                                        <span className="text-[11px] font-medium text-emerald-400">
                                            {estadoCuenta.saldo_pendiente > 0
                                                ? 'Pago pendiente en recepción'
                                                : '¡Totalmente Cancelado!'}
                                        </span>
                                    </div>
                                    <span className="font-mono text-lg font-black text-white">
                                        $
                                        {formatearNumero(
                                            estadoCuenta.saldo_pendiente,
                                        )}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* 2. PESTAÑA: GESTIÓN DE HUÉSPEDES & ACOMPAÑANTES */}
                {pestanaActiva === 'huespedes' && (
                    <div className="space-y-4">
                        <div className="rounded-2xl border border-bugambilia-500/30 bg-bugambilia-500/10 p-4 text-left">
                            <div className="flex items-center gap-2">
                                <Users className="size-4 text-bugambilia-600 dark:text-bugambilia-400" />
                                <h4 className="text-xs font-black text-bugambilia-700 dark:text-bugambilia-300">
                                    Nómina de Acompañantes Autorizada
                                </h4>
                            </div>
                            <p className="mt-1 text-xs font-medium text-muted-foreground">
                                Administre los datos e identificaciones de los
                                huéspedes acompañantes para agilizar el registro
                                en recepción.
                            </p>
                        </div>

                        <div className="flex flex-wrap items-center gap-3">
                            <Button
                                onClick={() => setModalHuespedesAbierto(true)}
                                className="rounded-full bg-bugambilia-600 font-extrabold text-white hover:bg-bugambilia-700 dark:bg-bugambilia-500"
                            >
                                <Users className="mr-1.5 size-4" />
                                Administrar Nómina de Huéspedes
                            </Button>

                            <Button
                                variant="outline"
                                onClick={() => setModalLlegadaAbierto(true)}
                                className="rounded-full border-border font-extrabold text-foreground hover:bg-muted"
                            >
                                <UserCheck className="mr-1.5 size-4 text-emerald-600 dark:text-emerald-400" />
                                Confirmar Hora de Llegada
                            </Button>
                        </div>
                    </div>
                )}

                {/* 3. PESTAÑA: MI HABITACIÓN & ACTIVOS / EQUIPAMIENTO */}
                {pestanaActiva === 'activos' && (
                    <div className="space-y-3">
                        <div className="flex items-center justify-between">
                            <h4 className="text-xs font-black tracking-wider text-foreground uppercase">
                                Equipamiento e Inventario Fijo Incluido
                            </h4>
                            <Badge variant="outline" className="text-[10px]">
                                Verificado por Recepción
                            </Badge>
                        </div>

                        <div className="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                            {activosHabitacion.map((activo) => (
                                <div
                                    key={activo.id}
                                    className="flex items-start gap-3 rounded-2xl border border-border/70 bg-background p-3.5 transition-all hover:border-bugambilia-500/30"
                                >
                                    <div className="flex size-9 shrink-0 items-center justify-center rounded-xl bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400">
                                        <Tv className="size-4.5" />
                                    </div>
                                    <div className="grow space-y-0.5">
                                        <div className="flex items-center justify-between gap-1">
                                            <span className="text-xs font-black text-foreground">
                                                {activo.nombre}
                                            </span>
                                            <span className="font-mono text-[9px] font-bold text-muted-foreground">
                                                {activo.codigo}
                                            </span>
                                        </div>
                                        <p className="line-clamp-1 text-[11px] font-medium text-muted-foreground">
                                            {activo.descripcion}
                                        </p>
                                        <div className="flex items-center gap-2 pt-1">
                                            <span className="rounded-md bg-emerald-500/10 px-1.5 py-0.5 text-[9px] font-bold text-emerald-600 dark:text-emerald-400">
                                                Estado: {activo.estado}
                                            </span>
                                            <span className="text-[9px] font-medium text-muted-foreground">
                                                {activo.categoria}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {/* 3. PESTAÑA: PEDIR SERVICIOS A LA HABITACIÓN */}
                {pestanaActiva === 'servicios' && (
                    <div className="space-y-4">
                        <div className="rounded-2xl border border-bugambilia-500/30 bg-bugambilia-500/10 p-4 text-left">
                            <div className="flex items-center gap-2">
                                <Sparkles className="size-4 text-bugambilia-600 dark:text-bugambilia-400" />
                                <h4 className="text-xs font-black text-bugambilia-700 dark:text-bugambilia-300">
                                    Atención Directa & Mucama Express
                                </h4>
                            </div>
                            <p className="mt-1 text-xs font-medium text-muted-foreground">
                                Solicite toallas adicionales, servicio de
                                limpieza, lavandería o asistencia técnica de
                                forma inmediata.
                            </p>
                        </div>

                        <div className="flex flex-wrap items-center gap-3">
                            <Button
                                onClick={() => setModalServicioAbierto(true)}
                                className="rounded-full bg-bugambilia-600 font-extrabold text-white hover:bg-bugambilia-700 dark:bg-bugambilia-500"
                            >
                                <Bell className="mr-1.5 size-4" />
                                Pedir Servicio a la Habitación
                            </Button>
                        </div>
                    </div>
                )}

                {/* 4. PESTAÑA: ORDENAR AL RESTAURANTE */}
                {pestanaActiva === 'restaurante' && (
                    <div className="space-y-4">
                        <div className="rounded-2xl border border-amber-500/30 bg-amber-500/10 p-4 text-left">
                            <div className="flex items-center gap-2">
                                <Utensils className="size-4 text-amber-600 dark:text-amber-400" />
                                <h4 className="text-xs font-black text-amber-700 dark:text-amber-300">
                                    Room Service & Comanda Digital
                                </h4>
                            </div>
                            <p className="mt-1 text-xs font-medium text-muted-foreground">
                                Disfrute del menú exclusivo del restaurante
                                Hotel Bugambilias directo en la comodidad de su
                                estancia.
                            </p>
                        </div>

                        <div className="flex flex-wrap items-center gap-3">
                            <Button
                                onClick={() => setModalRestauranteAbierto(true)}
                                className="rounded-full bg-amber-600 font-extrabold text-white hover:bg-amber-700 dark:bg-amber-500"
                            >
                                <Utensils className="mr-1.5 size-4" />
                                Menú & Pedidos al Restaurante
                            </Button>
                        </div>
                    </div>
                )}
            </div>

            {/* Pie de Acciones Principal (Auto Check-In, Voucher PDF, Cancelación) */}
            <div className="flex flex-wrap items-center justify-between gap-2.5 border-t border-border/60 bg-muted/20 px-3 py-3 sm:px-6">
                <div className="flex flex-wrap items-center gap-2">
                    {/* Auto Check-in Express CTA */}
                    {esActiva && (
                        <Link
                            href={`/reservas/check-in?codigo=${encodeURIComponent(reserva.codigo_reserva)}`}
                            className="inline-flex items-center gap-1.5 rounded-full bg-bugambilia-600 px-3.5 py-1.5 text-xs font-extrabold text-white shadow-2xs transition-colors hover:bg-bugambilia-700 dark:bg-bugambilia-500"
                        >
                            <UserPlus className="size-3.5" />
                            Auto Check-In Express
                        </Link>
                    )}

                    {/* Voucher PDF */}
                    <a
                        href={`/reservas/${reserva.id}/voucher?codigo=${encodeURIComponent(reserva.codigo_reserva)}`}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex items-center gap-1.5 rounded-full border border-border bg-background px-3 py-1.5 text-xs font-bold text-foreground transition-colors hover:bg-muted"
                    >
                        <FileText className="size-3.5 text-bugambilia-600 dark:text-bugambilia-400" />
                        <span>
                            <span className="hidden sm:inline">Invoice / </span>
                            Voucher PDF
                        </span>
                    </a>
                </div>

                {/* Botón Cancelar Reserva */}
                {esActiva && (
                    <button
                        type="button"
                        onClick={() => onSolicitarCancelacion(reserva)}
                        className="cursor-pointer text-xs font-extrabold text-rose-600 transition-colors hover:text-rose-700 hover:underline dark:text-rose-400"
                    >
                        Solicitar Cancelación
                    </button>
                )}
            </div>

            {/* Modales Interactivos */}
            <ModalSolicitarServicioHabitacion
                reserva={reserva}
                estaAbierto={modalServicioAbierto}
                alCerrar={() => setModalServicioAbierto(false)}
            />

            <ModalPedidoRestauranteHabitacion
                reserva={reserva}
                estaAbierto={modalRestauranteAbierto}
                alCerrar={() => setModalRestauranteAbierto(false)}
            />

            <ModalGestionHuespedesReserva
                reserva={reserva}
                estaAbierto={modalHuespedesAbierto}
                alCerrar={() => setModalHuespedesAbierto(false)}
            />

            <ModalConfirmarLlegadaReserva
                reserva={reserva}
                estaAbierto={modalLlegadaAbierto}
                alCerrar={() => setModalLlegadaAbierto(false)}
            />
        </Card>
    );
};

export default TarjetaReservaPortalItem;
