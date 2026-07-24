import { Link } from '@inertiajs/react';
import { CheckCircle, Clock, CreditCard, Smartphone } from 'lucide-react';
import { useState } from 'react';
import type {
    DatosTarjetaPago,
    MetodoPago,
} from '@/modules/payment/types/pago';
import { Button } from '@/modules/shared/ui/boton';
import { Checkbox } from '@/modules/shared/ui/casilla';
import { Input } from '@/modules/shared/ui/entrada';
import { Label } from '@/modules/shared/ui/etiqueta';

interface PropiedadesPasoMetodoPago {
    metodo: MetodoPago;
    total: number;
    alCambiarMetodo: (metodo: MetodoPago) => void;
    alConfirmar: () => void;
}

const DATOS_TARJETA_INICIALES: DatosTarjetaPago = {
    titular: '',
    numero: '',
    expiracion: '',
    codigoSeguridad: '',
};

export const PasoMetodoPago = ({
    metodo,
    total,
    alCambiarMetodo,
    alConfirmar,
}: PropiedadesPasoMetodoPago) => {
    const [datosTarjeta, setDatosTarjeta] = useState(DATOS_TARJETA_INICIALES);
    const [terminosAceptados, setTerminosAceptados] = useState(false);

    const actualizarTarjeta = <Campo extends keyof DatosTarjetaPago>(
        campo: Campo,
        valor: DatosTarjetaPago[Campo],
    ) => {
        setDatosTarjeta((datosAnteriores) => ({
            ...datosAnteriores,
            [campo]: valor,
        }));
    };

    return (
        <div className="animate-in fade-in slide-in-from-bottom-6 duration-700">
            <header className="mb-12">
                <h1 className="mb-4 text-4xl leading-none font-black tracking-tighter text-gray-900 md:text-6xl dark:text-white">
                    Detalles del{' '}
                    <span className="text-bugambilia-gradient bg-clip-text text-transparent italic">
                        pago
                    </span>
                </h1>
                <p className="text-lg font-medium text-gray-500">
                    Selecciona cómo deseas confirmar la reserva.
                </p>
            </header>

            <div className="space-y-10">
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <SelectorMetodoPago
                        metodo="tarjeta"
                        activo={metodo === 'tarjeta'}
                        titulo="Tarjeta"
                        icono={CreditCard}
                        alSeleccionar={alCambiarMetodo}
                    />
                    <SelectorMetodoPago
                        metodo="paypal"
                        activo={metodo === 'paypal'}
                        titulo="PayPal / Digital"
                        icono={Smartphone}
                        alSeleccionar={alCambiarMetodo}
                    />
                </div>

                {metodo === 'tarjeta' && (
                    <FormularioTarjeta
                        datos={datosTarjeta}
                        alCambiar={actualizarTarjeta}
                    />
                )}

                <div className="rounded-3xl border border-bugambilia-100 bg-bugambilia-50/30 p-8 dark:border-bugambilia-800/50 dark:bg-bugambilia-900/10">
                    <div className="flex items-start gap-4">
                        <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-bugambilia-100 bg-white shadow-sm dark:bg-gray-800">
                            <Clock className="h-5 w-5 text-bugambilia-600" />
                        </span>
                        <div>
                            <h4 className="mb-1 text-sm font-black tracking-tighter text-gray-900 uppercase dark:text-white">
                                Cancelación gratuita
                            </h4>
                            <p className="text-xs leading-relaxed font-medium text-gray-500">
                                Puedes cancelar sin cargos hasta 24 horas antes
                                de tu llegada.
                            </p>
                        </div>
                    </div>
                </div>

                <div className="pt-6">
                    <div className="mb-10 flex items-start gap-4">
                        <Checkbox
                            id="terminos_aceptados"
                            checked={terminosAceptados}
                            onCheckedChange={(valor) =>
                                setTerminosAceptados(valor === true)
                            }
                            className="mt-1 border-gray-200"
                        />
                        <Label
                            htmlFor="terminos_aceptados"
                            className="cursor-pointer text-[10px] leading-relaxed font-black tracking-widest text-gray-400 uppercase select-none"
                        >
                            He leído y acepto los{' '}
                            <Link
                                href="#"
                                className="text-gray-900 underline dark:text-white"
                            >
                                términos de servicio
                            </Link>{' '}
                            y las políticas de privacidad.
                        </Label>
                    </div>

                    <Button
                        type="button"
                        disabled={!terminosAceptados}
                        onClick={alConfirmar}
                        className="bg-bugambilia-gradient transition-airbnb h-20 w-full rounded-[2rem] px-16 text-xs font-black tracking-[0.3em] text-white uppercase shadow-2xl hover:scale-105 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                    >
                        Confirmar reserva • ${total.toFixed(2)}
                    </Button>
                </div>
            </div>
        </div>
    );
};

interface PropiedadesSelectorMetodoPago {
    metodo: MetodoPago;
    activo: boolean;
    titulo: string;
    icono: typeof CreditCard;
    alSeleccionar: (metodo: MetodoPago) => void;
}

const SelectorMetodoPago = ({
    metodo,
    activo,
    titulo,
    icono: Icono,
    alSeleccionar,
}: PropiedadesSelectorMetodoPago) => {
    return (
        <button
            type="button"
            aria-pressed={activo}
            onClick={() => alSeleccionar(metodo)}
            className={`group transition-airbnb relative cursor-pointer overflow-hidden rounded-[2rem] border-2 p-5 text-left sm:p-8 ${
                activo
                    ? 'border-black bg-white shadow-lg dark:border-white dark:bg-gray-800'
                    : 'border-gray-100 bg-white/50 hover:border-gray-200 dark:border-gray-800 dark:bg-gray-900/60 dark:hover:border-gray-700'
            }`}
        >
            {activo && (
                <CheckCircle className="animate-in zoom-in absolute top-4 right-4 h-4 w-4 fill-current text-black dark:text-white" />
            )}
            <span
                className={`transition-airbnb mb-4 flex h-10 w-10 items-center justify-center rounded-xl ${
                    activo
                        ? 'bg-black text-white dark:bg-white dark:text-black'
                        : 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500'
                }`}
            >
                <Icono className="h-5 w-5" />
            </span>
            <span
                className={`text-[10px] font-black tracking-widest uppercase ${
                    activo
                        ? 'text-black dark:text-white'
                        : 'text-gray-400 dark:text-gray-500'
                }`}
            >
                {titulo}
            </span>
        </button>
    );
};

interface PropiedadesFormularioTarjeta {
    datos: DatosTarjetaPago;
    alCambiar: <Campo extends keyof DatosTarjetaPago>(
        campo: Campo,
        valor: DatosTarjetaPago[Campo],
    ) => void;
}

const FormularioTarjeta = ({
    datos,
    alCambiar,
}: PropiedadesFormularioTarjeta) => {
    return (
        <div className="shadow-airbnb rounded-[2.5rem] border border-gray-100 bg-white p-5 sm:p-8 md:p-10 dark:border-gray-800 dark:bg-gray-900">
            <div className="grid grid-cols-1 gap-6">
                <CampoTarjeta
                    etiqueta="Titular de la tarjeta"
                    valor={datos.titular}
                    marcador="JUAN PEREZ"
                    alCambiar={(valor) => alCambiar('titular', valor)}
                />
                <CampoTarjeta
                    etiqueta="Número de tarjeta"
                    valor={datos.numero}
                    marcador="0000 0000 0000 0000"
                    alCambiar={(valor) => alCambiar('numero', valor)}
                />
                <div className="grid grid-cols-2 gap-6">
                    <CampoTarjeta
                        etiqueta="Expiración"
                        valor={datos.expiracion}
                        marcador="MM / AA"
                        alCambiar={(valor) => alCambiar('expiracion', valor)}
                    />
                    <CampoTarjeta
                        etiqueta="Cód. seg."
                        valor={datos.codigoSeguridad}
                        marcador="123"
                        alCambiar={(valor) =>
                            alCambiar('codigoSeguridad', valor)
                        }
                    />
                </div>
            </div>
        </div>
    );
};

interface PropiedadesCampoTarjeta {
    etiqueta: string;
    valor: string;
    marcador: string;
    alCambiar: (valor: string) => void;
}

const CampoTarjeta = ({
    etiqueta,
    valor,
    marcador,
    alCambiar,
}: PropiedadesCampoTarjeta) => {
    return (
        <div className="space-y-2.5">
            <Label className="ml-1 text-[10px] font-black tracking-widest text-gray-500 uppercase dark:text-gray-400">
                {etiqueta}
            </Label>
            <Input
                value={valor}
                onChange={(evento) => alCambiar(evento.target.value)}
                className="transition-airbnb h-14 rounded-2xl border-gray-100 bg-gray-50 placeholder:text-gray-300 focus:bg-white focus:ring-1 focus:ring-bugambilia-100 dark:border-gray-800 dark:bg-gray-950 dark:placeholder:text-gray-700 dark:focus:bg-gray-800 dark:focus:ring-bugambilia-900/50"
                placeholder={marcador}
            />
        </div>
    );
};
