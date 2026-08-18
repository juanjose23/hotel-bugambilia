import {
    UserCheck,
    ChevronRight,
    ChevronLeft,
    CheckCircle2,
} from 'lucide-react';
import { Button } from '@/modulos/compartido/ui/boton';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';
import { useAutoCheckIn } from '../hooks/useAutoCheckIn';
import type { PropiedadesSeccionAutoCheckIn } from '../interfaces/autoCheckInInterfaces';
import { PasoAcompanantesAutoCheckIn } from './secciones/PasoAcompanantesAutoCheckIn';
import { PasoCuentaCreditoAutoCheckIn } from './secciones/PasoCuentaCreditoAutoCheckIn';
import { PasosIndicadorCheckIn } from './secciones/PasosIndicadorCheckIn';
import { PasoTerminosAutoCheckIn } from './secciones/PasoTerminosAutoCheckIn';
import { PasoTitularAutoCheckIn } from './secciones/PasoTitularAutoCheckIn';
import { ResumenQrCheckIn } from './secciones/ResumenQrCheckIn';

export const SeccionAutoCheckIn = ({
    reserva,
}: PropiedadesSeccionAutoCheckIn) => {
    const {
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
    } = useAutoCheckIn(reserva);

    if (completado) {
        return (
            <div className="min-h-screen bg-background py-10 font-sans md:py-16">
                <div className="container mx-auto max-w-3xl px-4">
                    <ResumenQrCheckIn
                        codigoReserva={reservaDatos.codigoReserva}
                        titularNombre={form.data.titularNombre}
                        habitacionNombre={reservaDatos.habitacionNombre}
                        fechaEntrada={reservaDatos.fechaEntrada}
                        fechaSalida={reservaDatos.fechaSalida}
                    />
                </div>
            </div>
        );
    }

    return (
        <section className="min-h-screen bg-background py-8 font-sans md:py-16">
            <div className="container mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                {/* Cabecera Principal */}
                <div className="mx-auto mb-8 max-w-2xl text-center">
                    <Badge
                        variant="outline"
                        className="mb-3 border-amber-500/20 bg-amber-500/10 text-amber-600 dark:text-amber-400"
                    >
                        <UserCheck
                            className="mr-1.5 size-3.5"
                            data-icon="inline-start"
                        />{' '}
                        Auto Check-in Digital
                    </Badge>
                    <h1 className="text-3xl font-black text-foreground sm:text-4xl">
                        Pre-Registro Rápido & Seguro
                    </h1>
                    <p className="mt-2 text-xs font-medium text-muted-foreground sm:text-sm">
                        Reserva:{' '}
                        <span className="font-extrabold text-foreground">
                            {reservaDatos.codigoReserva}
                        </span>{' '}
                        • {reservaDatos.habitacionNombre}
                    </p>
                </div>

                {/* Bar de Pasos */}
                <PasosIndicadorCheckIn
                    pasoActual={step}
                    alSeleccionarPaso={(p) => setStep(p)}
                />

                {/* Tarjeta del Formulario por Paso */}
                <Card className="rounded-3xl border border-border/80 bg-card p-6 font-sans shadow-md sm:p-10">
                    <CardContent className="p-0">
                        <form
                            onSubmit={finalizarCheckIn}
                            className="flex flex-col gap-6"
                        >
                            {/* PASO 1: Titular */}
                            {step === 1 && (
                                <PasoTitularAutoCheckIn
                                    titularNombre={form.data.titularNombre}
                                    titularIdentificacion={
                                        form.data.titularIdentificacion
                                    }
                                    titularTelefono={form.data.titularTelefono}
                                    titularEmail={form.data.titularEmail}
                                    onUpdate={(field, val) =>
                                        form.setData(
                                            field as keyof typeof form.data,
                                            val,
                                        )
                                    }
                                />
                            )}

                            {/* PASO 2: Acompañantes */}
                            {step === 2 && (
                                <PasoAcompanantesAutoCheckIn
                                    huespedes={form.data.huespedes}
                                    onAgregar={agregarHuesped}
                                    onEliminar={eliminarHuesped}
                                    onActualizar={actualizarHuesped}
                                />
                            )}

                            {/* PASO 3: Cuenta & Crédito */}
                            {step === 3 && (
                                <PasoCuentaCreditoAutoCheckIn
                                    solicitaCuenta={form.data.solicitaCuenta}
                                    limiteCuenta={form.data.limiteCuenta}
                                    onToggleSolicitaCuenta={(checked) =>
                                        form.setData('solicitaCuenta', checked)
                                    }
                                    onUpdateLimite={(val) =>
                                        form.setData('limiteCuenta', val)
                                    }
                                />
                            )}

                            {/* PASO 4: Términos & Políticas */}
                            {step === 4 && (
                                <PasoTerminosAutoCheckIn
                                    aceptaPoliticas={form.data.aceptaPoliticas}
                                    onToggleAceptaPoliticas={(checked) =>
                                        form.setData('aceptaPoliticas', checked)
                                    }
                                />
                            )}

                            {/* Botones de Navegación del Wizard */}
                            <div className="flex items-center justify-between border-t border-border/50 pt-6">
                                {step > 1 ? (
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        onClick={anteriorPaso}
                                        className="rounded-full font-bold"
                                    >
                                        <ChevronLeft className="mr-1 size-4" />{' '}
                                        Anterior
                                    </Button>
                                ) : (
                                    <div />
                                )}

                                {step < 4 ? (
                                    <Button
                                        type="button"
                                        onClick={siguientePaso}
                                        size="sm"
                                        className="rounded-full bg-amber-500 font-extrabold text-black hover:bg-amber-600"
                                    >
                                        Siguiente{' '}
                                        <ChevronRight className="ml-1 size-4" />
                                    </Button>
                                ) : (
                                    <Button
                                        type="submit"
                                        disabled={!form.data.aceptaPoliticas}
                                        size="sm"
                                        className="rounded-full bg-emerald-600 font-extrabold text-white hover:bg-emerald-700 disabled:opacity-50"
                                    >
                                        Completar Auto Check-in{' '}
                                        <CheckCircle2 className="ml-1.5 size-4" />
                                    </Button>
                                )}
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </section>
    );
};

export default SeccionAutoCheckIn;
