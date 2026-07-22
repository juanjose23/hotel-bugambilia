import { usePage, Link, router } from '@inertiajs/react';
import {
    Calendar,
    Users,
    Phone,
    Mail,
    Search,
    Plus,
    Trash2,
    CheckCircle2,
    Clock,
} from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/modules/shared/ui/badge';
import { Button } from '@/modules/shared/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/modules/shared/ui/card';
import { Input } from '@/modules/shared/ui/input';
import { Tabs, TabsList, TabsTrigger } from '@/modules/shared/ui/tabs';

interface Acompanante {
    nombre: string;
    identificacion?: string;
}

interface ReservaItem {
    id: number;
    codigo_reserva: string;
    nombre_cliente: string;
    tipo_reserva: string;
    tipo_reserva_label: string;
    estado: string;
    estado_label: string;
    estado_color: string;
    fecha_check_in: string;
    fecha_check_out?: string;
    hora_reserva?: string;
    adultos: number;
    ninos: number;
    total: number;
    detalles: string;
    notas?: string;
    acompanantes?: Acompanante[];
}

interface MyBookingsProps {
    reservasProps?: ReservaItem[];
    codigoBusqueda?: string;
}

export default function MyBookings({
    reservasProps = [],
    codigoBusqueda = '',
}: MyBookingsProps) {
    const { hotel, flash } = usePage().props as any;
    const [activeTab, setActiveTab] = useState('all');
    const [searchTerm, setSearchTerm] = useState(codigoBusqueda);

    const handleSearchCode = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            '/mis-reservas',
            { codigo: searchTerm },
            { preserveState: true, preserveScroll: true },
        );
    };

    const handleCancelar = (reservaId: number) => {
        if (confirm('¿Está seguro de que desea cancelar esta reserva?')) {
            router.post(
                `/reservas/${reservaId}/cancelar`,
                {},
                { preserveScroll: true },
            );
        }
    };

    const getBadgeColorClass = (color: string) => {
        switch (color) {
            case 'warning':
                return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 border-amber-200';
            case 'success':
                return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200';
            case 'info':
                return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200';
            case 'danger':
                return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 border-red-200';
            default:
                return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 border-gray-200';
        }
    };

    const filteredBookings = reservasProps.filter((b) => {
        const matchesTab = activeTab === 'all' || b.estado === activeTab;
        const matchesSearch =
            !searchTerm ||
            b.codigo_reserva.toLowerCase().includes(searchTerm.toLowerCase()) ||
            b.nombre_cliente.toLowerCase().includes(searchTerm.toLowerCase()) ||
            b.detalles.toLowerCase().includes(searchTerm.toLowerCase());

        return matchesTab && matchesSearch;
    });

    return (
        <section className="min-h-screen bg-gray-50 py-8 font-sans dark:bg-gray-900">
            <div className="container mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                {/* Flash Message */}
                {flash?.exito && (
                    <div className="mb-6 flex items-center gap-3 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm font-bold text-emerald-700 dark:text-emerald-300">
                        <CheckCircle2 className="h-5 w-5 shrink-0 text-emerald-500" />
                        <span>{flash.exito}</span>
                    </div>
                )}

                <div className="mb-8">
                    <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h1 className="mb-1 text-3xl font-black tracking-tight text-gray-900 md:text-4xl dark:text-white">
                                Mis Reservaciones
                            </h1>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                Consulte y gestione sus reservaciones de
                                habitación, restaurante o servicios en{' '}
                                {hotel?.name || 'Hotel Bugambilias'}.
                            </p>
                        </div>
                        <div className="flex flex-col gap-3 sm:flex-row">
                            <form
                                onSubmit={handleSearchCode}
                                className="relative flex items-center"
                            >
                                <Search className="absolute left-3 h-4 w-4 text-gray-400" />
                                <Input
                                    placeholder="Buscar por código (ej: RES-2026-0001)..."
                                    value={searchTerm}
                                    onChange={(e) =>
                                        setSearchTerm(e.target.value)
                                    }
                                    className="w-full rounded-2xl border-gray-300 bg-white pr-20 pl-10 text-xs font-semibold text-gray-900 sm:w-72 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                />
                                <Button
                                    type="submit"
                                    size="sm"
                                    className="absolute right-1 rounded-xl bg-bugambilia-600 text-xs font-bold text-white hover:bg-bugambilia-700"
                                >
                                    Buscar
                                </Button>
                            </form>
                            <Button
                                className="rounded-2xl bg-bugambilia-600 text-xs font-bold text-white hover:bg-bugambilia-700 dark:bg-bugambilia-500"
                                asChild
                            >
                                <Link href="/habitaciones">
                                    <Plus className="mr-2 h-4 w-4" />
                                    Nueva reserva
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>

                {/* Status Tabs */}
                {reservasProps.length > 0 && (
                    <Tabs
                        value={activeTab}
                        onValueChange={setActiveTab}
                        className="mb-6"
                    >
                        <TabsList className="grid w-full grid-cols-3 rounded-2xl border border-gray-200 bg-white p-1 sm:grid-cols-5 dark:border-gray-700 dark:bg-gray-800">
                            <TabsTrigger value="all">
                                Todas ({reservasProps.length})
                            </TabsTrigger>
                            <TabsTrigger value="pendiente">
                                Pendientes (
                                {
                                    reservasProps.filter(
                                        (b) => b.estado === 'pendiente',
                                    ).length
                                }
                                )
                            </TabsTrigger>
                            <TabsTrigger value="confirmada">
                                Confirmadas (
                                {
                                    reservasProps.filter(
                                        (b) => b.estado === 'confirmada',
                                    ).length
                                }
                                )
                            </TabsTrigger>
                            <TabsTrigger value="checked_in">
                                Checked In (
                                {
                                    reservasProps.filter(
                                        (b) => b.estado === 'checked_in',
                                    ).length
                                }
                                )
                            </TabsTrigger>
                            <TabsTrigger value="cancelada">
                                Canceladas (
                                {
                                    reservasProps.filter(
                                        (b) => b.estado === 'cancelada',
                                    ).length
                                }
                                )
                            </TabsTrigger>
                        </TabsList>
                    </Tabs>
                )}

                {/* Empty State */}
                {filteredBookings.length === 0 && (
                    <Card className="rounded-3xl border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <CardContent className="py-16 text-center">
                            <Calendar className="mx-auto mb-4 h-14 w-14 text-gray-400 dark:text-gray-500" />
                            <h3 className="mb-1 text-lg font-extrabold text-gray-900 dark:text-white">
                                No se encontraron reservaciones
                            </h3>
                            <p className="mx-auto mb-6 max-w-md text-xs text-gray-600 dark:text-gray-400">
                                {searchTerm
                                    ? `No se encontró ninguna reserva que coincida con el código o término "${searchTerm}".`
                                    : 'Aún no tiene reservaciones registradas. Explore nuestras opciones e inicie su reserva.'}
                            </p>
                            <Button
                                className="rounded-2xl bg-bugambilia-600 text-xs font-bold text-white hover:bg-bugambilia-700"
                                asChild
                            >
                                <Link href="/habitaciones">
                                    Explorar Habitaciones & Suites
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                )}

                {/* Bookings List */}
                {filteredBookings.length > 0 && (
                    <div className="space-y-4">
                        {filteredBookings.map((b) => (
                            <Card
                                key={b.id}
                                className="overflow-hidden rounded-3xl border-gray-200 bg-white transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-gray-800"
                            >
                                <div className="p-6">
                                    <div className="mb-4 flex flex-col justify-between gap-4 border-b border-gray-100 pb-4 sm:flex-row sm:items-center dark:border-gray-700">
                                        <div>
                                            <div className="mb-1 flex items-center gap-3">
                                                <span className="text-lg font-black text-gray-900 dark:text-white">
                                                    {b.codigo_reserva}
                                                </span>
                                                <Badge
                                                    className={`border px-3 py-0.5 text-xs font-bold ${getBadgeColorClass(b.estado_color)}`}
                                                >
                                                    {b.estado_label}
                                                </Badge>
                                                <span className="rounded-full bg-amber-500/10 px-2.5 py-0.5 text-xs font-extrabold tracking-wider text-amber-600 uppercase dark:text-amber-400">
                                                    {b.tipo_reserva_label}
                                                </span>
                                            </div>
                                            <p className="text-xs font-medium text-gray-500">
                                                Huésped: {b.nombre_cliente}
                                            </p>
                                        </div>

                                        <div className="text-left sm:text-right">
                                            <span className="block text-xs font-extrabold text-gray-400 uppercase">
                                                Monto Total
                                            </span>
                                            <span className="text-xl font-black text-gray-900 dark:text-white">
                                                C$ {b.total}
                                            </span>
                                        </div>
                                    </div>

                                    <div className="mb-4 grid gap-4 text-xs font-semibold text-gray-700 sm:grid-cols-3 dark:text-gray-300">
                                        <div>
                                            <span className="mb-1 block text-[10px] font-extrabold text-gray-400 uppercase">
                                                Detalle del Reservable
                                            </span>
                                            <p className="font-bold text-gray-900 dark:text-white">
                                                {b.detalles ||
                                                    'Reserva General'}
                                            </p>
                                        </div>

                                        <div>
                                            <span className="mb-1 block text-[10px] font-extrabold text-gray-400 uppercase">
                                                Fechas / Horarios
                                            </span>
                                            <p className="flex items-center gap-1">
                                                <Calendar className="h-3.5 w-3.5 text-amber-500" />
                                                Check-In: {b.fecha_check_in}
                                            </p>
                                            {b.fecha_check_out && (
                                                <p className="mt-0.5 flex items-center gap-1">
                                                    <Calendar className="h-3.5 w-3.5 text-amber-500" />
                                                    Check-Out:{' '}
                                                    {b.fecha_check_out}
                                                </p>
                                            )}
                                            {b.hora_reserva && (
                                                <p className="mt-0.5 flex items-center gap-1 text-amber-600 dark:text-amber-400">
                                                    <Clock className="h-3.5 w-3.5" />
                                                    Hora: {b.hora_reserva}
                                                </p>
                                            )}
                                        </div>

                                        <div>
                                            <span className="mb-1 block text-[10px] font-extrabold text-gray-400 uppercase">
                                                Personas & Acompañantes
                                            </span>
                                            <p className="flex items-center gap-1 font-bold">
                                                <Users className="h-3.5 w-3.5 text-amber-500" />
                                                {b.adultos} Adulto(s)
                                                {b.ninos > 0
                                                    ? `, ${b.ninos} Niño(s)`
                                                    : ''}
                                            </p>
                                            {b.acompanantes &&
                                                b.acompanantes.length > 0 && (
                                                    <div className="mt-2 space-y-1 rounded-xl border border-gray-100 bg-gray-50 p-2 dark:border-gray-700/60 dark:bg-gray-800/80">
                                                        <span className="block text-[9px] font-extrabold text-gray-400 uppercase">
                                                            Acompañantes:
                                                        </span>
                                                        {b.acompanantes.map(
                                                            (ac, idx) => (
                                                                <p
                                                                    key={idx}
                                                                    className="text-[11px] font-medium text-gray-600 dark:text-gray-300"
                                                                >
                                                                    •{' '}
                                                                    {ac.nombre}{' '}
                                                                    {ac.identificacion
                                                                        ? `(${ac.identificacion})`
                                                                        : ''}
                                                                </p>
                                                            ),
                                                        )}
                                                    </div>
                                                )}
                                        </div>
                                    </div>

                                    {/* Action buttons */}
                                    {b.estado !== 'cancelada' &&
                                        b.estado !== 'checked_out' && (
                                            <div className="flex justify-end gap-2 border-t border-gray-100 pt-3 dark:border-gray-700">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() =>
                                                        handleCancelar(b.id)
                                                    }
                                                    className="cursor-pointer rounded-xl border-red-200 text-xs font-bold text-red-600 hover:bg-red-50"
                                                >
                                                    <Trash2 className="mr-1.5 h-3.5 w-3.5" />
                                                    Cancelar Reserva
                                                </Button>
                                            </div>
                                        )}
                                </div>
                            </Card>
                        ))}
                    </div>
                )}

                {/* Contact Help */}
                <Card className="mt-8 rounded-3xl border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-800">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-base font-bold text-gray-900 dark:text-white">
                            <Phone className="h-4 w-4 text-bugambilia-600 dark:text-bugambilia-400" />
                            ¿Necesita asistencia con su reservación?
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-6 text-xs md:grid-cols-2">
                            <div>
                                <h4 className="mb-2 font-bold text-gray-900 dark:text-white">
                                    Recepción & Contacto Directo
                                </h4>
                                <div className="space-y-1.5 font-semibold text-gray-600 dark:text-gray-400">
                                    <p className="flex items-center gap-2">
                                        <Phone className="h-3.5 w-3.5 text-amber-500" />
                                        Teléfono:{' '}
                                        {hotel?.telefono || '+505 2713 0000'}
                                    </p>
                                    <p className="flex items-center gap-2">
                                        <Mail className="h-3.5 w-3.5 text-amber-500" />
                                        Correo:{' '}
                                        {hotel?.email_reservaciones ||
                                            'reservas@hotelbugambilias.com'}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <h4 className="mb-2 font-bold text-gray-900 dark:text-white">
                                    Horario de Atención
                                </h4>
                                <div className="space-y-1 font-semibold text-gray-600 dark:text-gray-400">
                                    <p>
                                        Recepción disponible las 24 horas del
                                        día.
                                    </p>
                                    <p>
                                        Atención inmediata vía WhatsApp y
                                        teléfono.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </section>
    );
}
