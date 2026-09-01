import { Head, Link, router } from '@inertiajs/react';
import { Search, Calendar, BedDouble, ArrowRight, Loader2 } from 'lucide-react';
import { MisReservasCard } from '@/modules/reservas/components/MisReservasCard';
import { useMisReservasForm } from '@/modules/reservas/hooks/useMisReservasForm';
import type { ReservaPortalItem } from '@/modules/reservas/types';
import { MisReservasGridSkeleton } from '@/modules/shared/components/skeletons';
import { Button } from '@/modules/shared/components/ui/button';
import { Input } from '@/modules/shared/components/ui/input';

interface MisReservasPageProps {
    reservas?: ReservaPortalItem[];
    codigoBusqueda?: string;
}

export const MisReservas = ({
    reservas = [],
    codigoBusqueda = '',
}: MisReservasPageProps) => {
    const { register, handleSubmit, isSubmitting, errors } = useMisReservasForm(
        {
            codigoInicial: codigoBusqueda,
        },
    );

    const handleCancelarReserva = (
        reservaId: number,
        codigoReserva?: string,
    ) => {
        if (!confirm('¿Estás seguro de que deseas cancelar esta reserva?')) {
            return;
        }

        router.post(
            `/reservas/${reservaId}/cancelar`,
            {
                codigo: codigoReserva,
            },
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <>
            <Head>
                <title>Mis Reservas — Hotel Bugambilias Estelí</title>
                <meta
                    name="description"
                    content="Consulta y administra tus reservaciones en Hotel Bugambilias Estelí. Descarga tu comprobante y revisa los detalles de tu estancia."
                />
            </Head>

            <div className="mx-auto max-w-5xl px-4 py-8 sm:px-6 sm:py-12">
                {/* Cabecera */}
                <div className="text-center">
                    <span className="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1 text-xs font-black text-primary dark:bg-rose-950/60 dark:text-rose-400">
                        <Calendar className="size-3.5" />
                        <span>Portal de Huéspedes</span>
                    </span>
                    <h1 className="mt-3 text-2xl font-black tracking-tight text-foreground sm:text-4xl">
                        Mis Reservas & Estancias
                    </h1>
                    <p className="mx-auto mt-2 max-w-xl text-xs text-muted-foreground sm:text-sm">
                        Ingresa tu código de confirmación o consulta las
                        reservaciones asociadas a tu cuenta.
                    </p>
                </div>

                {/* Buscador de Reserva por Código */}
                <div className="mx-auto mt-8 max-w-xl">
                    <form
                        onSubmit={handleSubmit}
                        className="flex flex-col gap-2"
                    >
                        <div className="flex items-center gap-2 rounded-3xl border border-border bg-card p-2 shadow-lg">
                            <div className="relative flex-1">
                                <Search className="absolute top-1/2 left-4 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    type="text"
                                    {...register('codigo')}
                                    placeholder="Ej. RES-2026-1049"
                                    className="h-11 border-none bg-transparent pl-11 text-xs font-bold text-foreground uppercase focus-visible:ring-0"
                                />
                            </div>
                            <Button
                                type="submit"
                                disabled={isSubmitting}
                                className="h-11 rounded-2xl bg-primary px-5 text-xs font-black text-primary-foreground shadow-md hover:bg-primary/90"
                            >
                                {isSubmitting ? (
                                    <Loader2 className="size-4 animate-spin" />
                                ) : (
                                    <span>Buscar</span>
                                )}
                            </Button>
                        </div>
                        {errors.codigo && (
                            <p className="px-4 text-xs font-bold text-destructive">
                                {errors.codigo.message}
                            </p>
                        )}
                    </form>
                </div>

                {/* Lista de Reservas */}
                <div className="mt-10">
                    {isSubmitting ? (
                        <MisReservasGridSkeleton cantidad={2} />
                    ) : reservas.length === 0 ? (
                        <div className="rounded-3xl border border-dashed border-border bg-muted/20 p-8 text-center sm:p-12">
                            <div className="mx-auto flex size-14 items-center justify-center rounded-2xl bg-primary/10 text-primary dark:bg-rose-950/60 dark:text-rose-400">
                                <BedDouble className="size-7" />
                            </div>
                            <h3 className="mt-4 text-base font-black text-foreground sm:text-lg">
                                {codigoBusqueda
                                    ? `No encontramos reservaciones con el código "${codigoBusqueda}"`
                                    : 'No tienes reservaciones activas registradas'}
                            </h3>
                            <p className="mx-auto mt-1 max-w-md text-xs text-muted-foreground">
                                Explora nuestras elegantes habitaciones y suites
                                en Estelí para planificar tu próxima visita.
                            </p>
                            <div className="mt-6">
                                <Link
                                    href="/habitaciones"
                                    className="inline-flex h-11 items-center justify-center rounded-2xl bg-primary px-6 text-xs font-black text-primary-foreground shadow-lg hover:bg-primary/90"
                                >
                                    <span>Explorar Habitaciones</span>
                                    <ArrowRight className="ml-2 size-4" />
                                </Link>
                            </div>
                        </div>
                    ) : (
                        <div className="space-y-4">
                            <div className="flex items-center justify-between">
                                <h3 className="text-sm font-black text-foreground">
                                    {reservas.length}{' '}
                                    {reservas.length === 1
                                        ? 'reserva encontrada'
                                        : 'reservas encontradas'}
                                </h3>
                            </div>

                            <div className="grid grid-cols-1 gap-4">
                                {reservas.map((reserva) => (
                                    <MisReservasCard
                                        key={reserva.id}
                                        reserva={reserva}
                                        onCancelar={handleCancelarReserva}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
};

export default MisReservas;
