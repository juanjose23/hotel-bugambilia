import { HelpCircle, Info, Shield, Star } from 'lucide-react';
import type { DatosReserva, ServicioExtra } from '@/modules/shared/types';

interface PropiedadesResumenPago {
    datosReserva: DatosReserva;
    serviciosExtras: ServicioExtra[];
    serviciosSeleccionados: string[];
    total: number;
    nombreHotel: string;
}

export const ResumenPago = ({
    datosReserva,
    serviciosExtras,
    serviciosSeleccionados,
    total,
    nombreHotel,
}: PropiedadesResumenPago) => {
    const extrasSeleccionados = serviciosSeleccionados
        .map((id) => serviciosExtras.find((servicio) => servicio.id === id))
        .filter((servicio): servicio is ServicioExtra => Boolean(servicio));

    return (
        <aside className="lg:col-span-5">
            <div className="animate-in fade-in slide-in-from-right-8 space-y-8 duration-700 lg:sticky lg:top-32">
                <div className="shadow-airbnb relative overflow-hidden rounded-[2.5rem] border border-gray-100 bg-white p-5 sm:p-8 md:p-10 dark:border-gray-800 dark:bg-gray-900">
                    <div className="xs:flex-row mb-10 flex flex-col gap-6 overflow-hidden">
                        <div className="xs:w-32 xs:aspect-square relative aspect-video w-full shrink-0 overflow-hidden rounded-3xl shadow-2xl">
                            <img
                                src={datosReserva.imagen}
                                alt={datosReserva.habitacion}
                                className="absolute inset-0 h-full w-full object-cover transition-transform duration-1000 group-hover:scale-110"
                            />
                        </div>
                        <div className="flex flex-col justify-center">
                            <div className="mb-2 flex items-center gap-1.5">
                                <Star className="h-3.5 w-3.5 fill-bugambilia-600 text-bugambilia-600" />
                                <span className="text-xs font-black tracking-tight">
                                    {datosReserva.calificacion}
                                </span>
                                <span className="text-[10px] font-bold text-gray-400">
                                    • Mejor estancia
                                </span>
                            </div>
                            <h4 className="mb-1 max-w-[200px] truncate text-xl leading-[1.1] font-black tracking-tighter text-gray-900 sm:max-w-none dark:text-white">
                                {datosReserva.habitacion}
                            </h4>
                            <p className="text-[10px] font-black tracking-widest text-gray-400 uppercase">
                                {datosReserva.ubicacion}
                            </p>
                        </div>
                    </div>

                    <div className="mb-10 space-y-5 border-t border-gray-50 pt-8 dark:border-gray-800">
                        <LineaResumen
                            etiqueta={`$${datosReserva.precioHabitacion.toFixed(2)} x ${datosReserva.noches} noches`}
                            valor={
                                datosReserva.precioHabitacion *
                                datosReserva.noches
                            }
                        />
                        <LineaResumen
                            etiqueta="Impuestos de hospitalidad"
                            valor={datosReserva.impuestos}
                        />
                        <LineaResumen
                            etiqueta="Configuración y servicio"
                            valor={datosReserva.tarifaServicio}
                            conInformacion
                        />
                        {extrasSeleccionados.map((servicio) => (
                            <LineaResumen
                                key={servicio.id}
                                etiqueta={servicio.nombre}
                                valor={servicio.precio}
                                resaltada
                            />
                        ))}
                    </div>

                    <div className="flex items-end justify-between border-t-2 border-dashed border-gray-100 pt-8 dark:border-gray-800">
                        <div>
                            <p className="mb-1 text-[10px] font-black tracking-[0.4em] text-gray-400 uppercase">
                                Total reserva
                            </p>
                            <p className="text-4xl font-black tracking-tighter text-gray-900 tabular-nums dark:text-white">
                                ${total.toFixed(2)}
                            </p>
                        </div>
                        <span className="mb-1 rounded-lg bg-bugambilia-50 px-3 py-1.5 text-[10px] font-black tracking-widest text-bugambilia-600 uppercase dark:bg-bugambilia-900/40">
                            USD
                        </span>
                    </div>
                </div>

                <div className="flex items-center gap-4 rounded-[2rem] border border-emerald-100 bg-emerald-50/50 p-8 shadow-sm dark:border-emerald-800 dark:bg-emerald-900/10">
                    <span className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white text-emerald-600 shadow-sm dark:bg-emerald-900/30">
                        <Shield className="h-6 w-6" />
                    </span>
                    <div>
                        <h5 className="mb-1 text-[10px] font-black tracking-widest text-emerald-900 uppercase dark:text-emerald-400">
                            Garantía {nombreHotel.replace('Hotel ', '')}
                        </h5>
                        <p className="text-[11px] leading-relaxed font-medium text-emerald-700/70 dark:text-emerald-500/80">
                            Mejor precio garantizado y soporte local 24/7
                            durante toda tu estancia.
                        </p>
                    </div>
                </div>

                <div className="text-center">
                    <button
                        type="button"
                        className="transition-airbnb group inline-flex items-center gap-2 text-[10px] font-black tracking-widest text-gray-400 uppercase hover:text-black dark:hover:text-white"
                    >
                        <HelpCircle className="h-4 w-4 transition-transform group-hover:rotate-12" />
                        Preguntas sobre tu reserva
                    </button>
                </div>
            </div>
        </aside>
    );
};

interface PropiedadesLineaResumen {
    etiqueta: string;
    valor: number;
    resaltada?: boolean;
    conInformacion?: boolean;
}

const LineaResumen = ({
    etiqueta,
    valor,
    resaltada = false,
    conInformacion = false,
}: PropiedadesLineaResumen) => {
    return (
        <div className="animate-in fade-in slide-in-from-right-4 flex items-center justify-between gap-4 text-sm">
            <span
                className={`flex items-center gap-1 font-medium underline decoration-2 underline-offset-4 ${
                    resaltada
                        ? 'text-bugambilia-600 decoration-bugambilia-100'
                        : 'text-gray-500 decoration-gray-100'
                }`}
            >
                {etiqueta}
                {conInformacion && <Info className="h-3 w-3 text-gray-300" />}
            </span>
            <span className="font-black text-gray-900 tabular-nums dark:text-white">
                ${valor.toFixed(2)}
            </span>
        </div>
    );
};
