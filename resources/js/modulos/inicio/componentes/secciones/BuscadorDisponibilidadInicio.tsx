import { router } from '@inertiajs/react';
import { Search, Calendar, Users, Minus, Plus } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/modulos/compartido/ui/boton';
import { Input } from '@/modulos/compartido/ui/entrada';

export const BuscadorDisponibilidadInicio = () => {
    const [fechaCheckIn, setFechaCheckIn] = useState(
        () => new Date().toISOString().split('T')[0],
    );
    const [fechaCheckOut, setFechaCheckOut] = useState(
        () => new Date(Date.now() + 86400000).toISOString().split('T')[0],
    );
    const [adultos, setAdultos] = useState(2);
    const [ninos] = useState(0);

    const handleSearchReserva = (e: React.SubmitEvent) => {
        e.preventDefault();
        router.get('/habitaciones', {
            check_in: fechaCheckIn,
            check_out: fechaCheckOut,
            adultos,
            ninos,
            huespedes: adultos + ninos,
        });
    };

    return (
        <div className="mx-auto w-full max-w-4xl rounded-3xl border border-border/80 bg-card/95 p-4 shadow-2xl backdrop-blur-xl sm:p-6">
            <form
                onSubmit={handleSearchReserva}
                className="grid grid-cols-1 items-end gap-4 sm:grid-cols-2 lg:grid-cols-4"
            >
                <div className="flex flex-col gap-1.5">
                    <label className="flex items-center gap-1.5 text-[11px] font-extrabold tracking-wider text-muted-foreground uppercase">
                        <Calendar className="size-3.5 text-bugambilia-600 dark:text-bugambilia-400" />{' '}
                        Check-In
                    </label>
                    <Input
                        type="date"
                        value={fechaCheckIn}
                        onChange={(e) => setFechaCheckIn(e.target.value)}
                        className="rounded-2xl bg-background text-xs font-bold"
                    />
                </div>

                <div className="flex flex-col gap-1.5">
                    <label className="flex items-center gap-1.5 text-[11px] font-extrabold tracking-wider text-muted-foreground uppercase">
                        <Calendar className="size-3.5 text-bugambilia-600 dark:text-bugambilia-400" />{' '}
                        Check-Out
                    </label>
                    <Input
                        type="date"
                        value={fechaCheckOut}
                        onChange={(e) => setFechaCheckOut(e.target.value)}
                        className="rounded-2xl bg-background text-xs font-bold"
                    />
                </div>

                <div className="flex flex-col gap-1.5">
                    <label className="flex items-center gap-1.5 text-[11px] font-extrabold tracking-wider text-muted-foreground uppercase">
                        <Users className="size-3.5 text-bugambilia-600 dark:text-bugambilia-400" />{' '}
                        Huéspedes
                    </label>
                    <div className="flex items-center justify-between rounded-2xl border border-border bg-background px-3 py-1.5">
                        <div className="text-xs font-bold text-foreground">
                            {adultos} Ad. {ninos > 0 ? `• ${ninos} Niñ.` : ''}
                        </div>
                        <div className="flex items-center gap-1">
                            <button
                                type="button"
                                onClick={() =>
                                    setAdultos(Math.max(1, adultos - 1))
                                }
                                className="flex size-6 items-center justify-center rounded-full bg-muted text-foreground hover:bg-muted/80"
                            >
                                <Minus className="size-3" />
                            </button>
                            <button
                                type="button"
                                onClick={() => setAdultos(adultos + 1)}
                                className="flex size-6 items-center justify-center rounded-full bg-muted text-foreground hover:bg-muted/80"
                            >
                                <Plus className="size-3" />
                            </button>
                        </div>
                    </div>
                </div>

                <Button
                    type="submit"
                    size="lg"
                    className="w-full rounded-2xl bg-bugambilia-600 font-extrabold text-white hover:bg-bugambilia-700"
                >
                    <Search
                        className="mr-1.5 size-4"
                        data-icon="inline-start"
                    />
                    Buscar Disponibilidad
                </Button>
            </form>
        </div>
    );
};
