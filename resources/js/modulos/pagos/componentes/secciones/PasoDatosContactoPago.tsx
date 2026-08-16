import { Briefcase } from 'lucide-react';
import { Input } from '@/modulos/compartido/ui/entrada';
import { Label } from '@/modulos/compartido/ui/etiqueta';
import type { DatosContactoPago } from '@/modulos/pagos/interfaces/pago';
interface PropiedadesPasoDatosContactoPago {
    datos: DatosContactoPago;
    alCambiar: <Campo extends keyof DatosContactoPago>(
        campo: Campo,
        valor: DatosContactoPago[Campo],
    ) => void;
}
export const PasoDatosContactoPago = ({
    datos,
    alCambiar,
}: PropiedadesPasoDatosContactoPago) => {
    return (
        <>
            <section className="shadow-airbnb rounded-[2.5rem] border border-gray-100 bg-white p-5 sm:p-8 md:p-10 dark:border-gray-800 dark:bg-gray-900">
                <h2 className="mb-8 text-xl font-black tracking-tight text-gray-900 dark:text-white">
                    Información de contacto
                </h2>
                <div className="grid gap-6 md:grid-cols-2">
                    <CampoContacto
                        etiqueta="Nombre"
                        valor={datos.nombre}
                        marcador="Juan"
                        alCambiar={(valor) => alCambiar('nombre', valor)}
                    />
                    <CampoContacto
                        etiqueta="Apellido"
                        valor={datos.apellido}
                        marcador="Rodríguez"
                        alCambiar={(valor) => alCambiar('apellido', valor)}
                    />
                    <CampoContacto
                        etiqueta="Correo electrónico"
                        valor={datos.correo}
                        marcador="juan@ejemplo.com"
                        tipo="email"
                        className="md:col-span-2"
                        alCambiar={(valor) => alCambiar('correo', valor)}
                    />
                    <CampoContacto
                        etiqueta="Teléfono"
                        valor={datos.telefono}
                        marcador="+505 0000 0000"
                        tipo="tel"
                        className="md:col-span-2"
                        alCambiar={(valor) => alCambiar('telefono', valor)}
                    />
                </div>
            </section>

            <section className="shadow-airbnb rounded-[2.5rem] border border-gray-100 bg-white p-5 sm:p-8 md:p-10 dark:border-gray-800 dark:bg-gray-900">
                <div className="mb-6 flex items-center gap-2">
                    <Briefcase className="h-5 w-5 text-gray-400" />
                    <h2 className="text-xl font-black tracking-tight text-gray-900 dark:text-white">
                        Peticiones especiales
                    </h2>
                </div>
                <p className="mb-6 border-l-2 border-bugambilia-600 py-1 pl-4 text-xs font-medium tracking-widest text-gray-400 uppercase">
                    ¿Llegas tarde? ¿Eres alérgico a algo? Cuéntanos.
                </p>
                <textarea
                    value={datos.peticiones}
                    onChange={(evento) =>
                        alCambiar('peticiones', evento.target.value)
                    }
                    className="transition-airbnb h-32 w-full resize-none rounded-3xl border-gray-100 bg-gray-50 p-6 text-sm placeholder:text-gray-300 focus:bg-white focus:ring-1 focus:ring-bugambilia-100 focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:placeholder:text-gray-700 dark:focus:bg-gray-800 dark:focus:ring-bugambilia-900/50"
                    placeholder="Ej. Prefiero una habitación en la planta alta..."
                />
            </section>
        </>
    );
};
interface PropiedadesCampoContacto {
    etiqueta: string;
    valor: string;
    marcador: string;
    tipo?: 'text' | 'email' | 'tel';
    className?: string;
    alCambiar: (valor: string) => void;
}
const CampoContacto = ({
    etiqueta,
    valor,
    marcador,
    tipo = 'text',
    className,
    alCambiar,
}: PropiedadesCampoContacto) => {
    return (
        <div className={`space-y-2.5 ${className ?? ''}`}>
            <Label className="ml-1 text-[10px] font-black tracking-widest text-gray-500 uppercase dark:text-gray-400">
                {etiqueta}
            </Label>
            <Input
                type={tipo}
                value={valor}
                onChange={(evento) => alCambiar(evento.target.value)}
                className="transition-airbnb h-14 rounded-2xl border-gray-100 bg-gray-50 placeholder:text-gray-300 focus:bg-white focus:ring-1 focus:ring-bugambilia-100 dark:border-gray-800 dark:bg-gray-950 dark:placeholder:text-gray-700 dark:focus:bg-gray-800 dark:focus:ring-bugambilia-900/50"
                placeholder={marcador}
            />
        </div>
    );
};
