import { Link, router, usePage } from '@inertiajs/react';
import {
    Calendar,
    Users,
    Clock,
    Search,
    Plus,
    FileText,
    Trash2,
    CheckCircle2,
} from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/modules/shared/ui/boton';
import { Input } from '@/modules/shared/ui/entrada';
import { Badge } from '@/modules/shared/ui/insignia';
import { Tabs, TabsList, TabsTrigger } from '@/modules/shared/ui/pestanas';
import { Card, CardContent } from '@/modules/shared/ui/tarjeta';

interface AcompananteItem {
    nombre: string;
    identificacion?: string | null;
}

interface ReservaClienteProps {
    id: number;
    codigo_reserva: string;
    nombre_cliente: string;
    email_cliente: string | null;
    telefono_cliente: string | null;
    tipo_reserva: number;
    tipo_reserva_label: string;
    estado: number;
    estado_label: string;
    estado_color: string;
    adultos: number;
    ninos: number;
    total: string;
    fecha_check_in: string;
    fecha_check_out: string | null;
    hora_reserva: string | null;
    detalles: string;
    huespedes_count: number;
    acompanantes?: AcompananteItem[];
}

interface MisReservasInicioProps {
    reservas?: ReservaClienteProps[];
    hotel?: {
        name?: string;
    };
    flash?: {
        exito?: string;
    };
}

export const MisReservasInicio = ({
    reservas = [],
    hotel,
    flash,
}: MisReservasInicioProps) => {
    const { props } = usePage();
    const reservasProps = (props.reservas as ReservaClienteProps[]) || reservas;

    const [activeTab, setActiveTab] = useState<string>('all');
    const [searchTerm, setSearchTerm] = useState<string>('');

    const handleSearchCode = (e: React.FormEvent) => {
        e.preventDefault();
    };

    const handleCancelar = (reservaId: number) => {
        if (
            confirm(
                '¿Está seguro de que desea solicitar la cancelación de esta reservación?',
            )
        ) {
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
        const matchesTab =
            activeTab === 'all' || b.estado === Number(activeTab);
        const matchesSearch =
            !searchTerm ||
            b.codigo_reserva.toLowerCase().includes(searchTerm.toLowerCase()) ||
            b.nombre_cliente.toLowerCase().includes(searchTerm.toLowerCase()) ||
            b.detalles.toLowerCase().includes(searchTerm.toLowerCase());

        return matchesTab && matchesSearch;
    });

    return (
        <section className="min-h-screen bg-background py-8 font-sans text-foreground">
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
                            <h1 className="mb-1 text-3xl font-black tracking-tight text-foreground md:text-4xl">
                                Mis Reservaciones
                            </h1>
                            <p className="text-sm text-muted-foreground">
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
                                <Search className="absolute left-3 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Buscar por código (ej: RES-2026-0001)..."
                                    value={searchTerm}
                                    onChange={(e) =>
                                        setSearchTerm(e.target.value)
                                    }
                                    className="w-full rounded-2xl border-border bg-card pr-20 pl-10 text-xs font-semibold text-foreground sm:w-72"
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
                        <TabsList className="grid w-full grid-cols-3 rounded-2xl border border-border bg-card p-1 sm:grid-cols-5">
                            <TabsTrigger
                                value="all"
                                className="cursor-pointer font-bold"
                            >
                                Todas ({reservasProps.length})
                            </TabsTrigger>
                            <TabsTrigger
                                value="1"
                                className="cursor-pointer font-bold"
                            >
                                Pendientes (
                                {
                                    reservasProps.filter((b) => b.estado === 1)
                                        .length
                                }
                                )
                            </TabsTrigger>
                            <TabsTrigger
                                value="2"
                                className="cursor-pointer font-bold"
                            >
                                Confirmadas (
                                {
                                    reservasProps.filter((b) => b.estado === 2)
                                        .length
                                }
                                )
                            </TabsTrigger>
                            <TabsTrigger
                                value="3"
                                className="cursor-pointer font-bold"
                            >
                                Checked In (
                                {
                                    reservasProps.filter((b) => b.estado === 3)
                                        .length
                                }
                                )
                            </TabsTrigger>
                            <TabsTrigger
                                value="5"
                                className="cursor-pointer font-bold"
                            >
                                Canceladas (
                                {
                                    reservasProps.filter((b) => b.estado === 5)
                                        .length
                                }
                                )
                            </TabsTrigger>
                        </TabsList>
                    </Tabs>
                )}

                {/* Empty State */}
                {filteredBookings.length === 0 && (
                    <Card className="rounded-3xl border-border bg-card p-12 text-center">
                        <CardContent className="flex flex-col items-center justify-center p-0">
                            <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-muted">
                                <Calendar className="h-8 w-8 text-muted-foreground" />
                            </div>
                            <h3 className="mb-2 text-xl font-bold text-foreground">
                                No se encontraron reservaciones
                            </h3>
                            <p className="mb-6 max-w-md text-sm text-muted-foreground">
                                {searchTerm
                                    ? 'No hay resultados que coincidan con su búsqueda.'
                                    : 'Aún no tiene reservaciones en esta categoría.'}
                            </p>
                            <Button
                                className="rounded-2xl bg-bugambilia-600 font-bold text-white hover:bg-bugambilia-700"
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
                                className="overflow-hidden rounded-3xl border-border/80 bg-card transition-shadow hover:shadow-md"
                            >
                                <div className="p-6">
                                    <div className="mb-4 flex flex-col justify-between gap-4 border-b border-border/60 pb-4 sm:flex-row sm:items-center">
                                        <div>
                                            <div className="mb-1 flex items-center gap-3">
                                                <span className="text-lg font-black text-foreground">
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
                                            <p className="text-xs font-medium text-muted-foreground">
                                                Huésped: {b.nombre_cliente}
                                            </p>
                                        </div>

                                        <div className="text-left sm:text-right">
                                            <span className="block text-xs font-extrabold text-muted-foreground uppercase">
                                                Monto Total
                                            </span>
                                            <span className="text-xl font-black text-foreground">
                                                C$ {b.total}
                                            </span>
                                        </div>
                                    </div>

                                    <div className="mb-4 grid gap-4 text-xs font-semibold text-muted-foreground sm:grid-cols-3">
                                        <div>
                                            <span className="mb-1 block text-[10px] font-extrabold text-muted-foreground uppercase">
                                                Detalle del Reservable
                                            </span>
                                            <p className="font-bold text-foreground">
                                                {b.detalles ||
                                                    'Reserva General'}
                                            </p>
                                        </div>

                                        <div>
                                            <span className="mb-1 block text-[10px] font-extrabold text-muted-foreground uppercase">
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
                                            <span className="mb-1 block text-[10px] font-extrabold text-muted-foreground uppercase">
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
                                                    <div className="mt-2 space-y-1 rounded-xl border border-border bg-muted/40 p-2">
                                                        <span className="block text-[9px] font-extrabold text-muted-foreground uppercase">
                                                            Acompañantes:
                                                        </span>
                                                        {b.acompanantes.map(
                                                            (ac, idx) => (
                                                                <p
                                                                    key={idx}
                                                                    className="text-[11px] font-medium text-muted-foreground"
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
                                    {b.estado !== 5 && b.estado !== 4 && (
                                        <div className="flex justify-end gap-2 border-t border-border/60 pt-3">
                                            <a
                                                href={`/reservas/${b.id}/voucher`}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="inline-flex items-center gap-1.5 rounded-xl bg-bugambilia-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-bugambilia-700 dark:bg-bugambilia-500"
                                            >
                                                <FileText className="h-3.5 w-3.5" />
                                                Voucher PDF
                                            </a>
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                onClick={() =>
                                                    handleCancelar(b.id)
                                                }
                                                className="cursor-pointer rounded-xl border-red-200 text-xs font-bold text-red-600 hover:bg-red-50 dark:border-red-900/40 dark:text-red-400 dark:hover:bg-red-950/20"
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
            </div>
        </section>
    );
};

export default MisReservasInicio;
