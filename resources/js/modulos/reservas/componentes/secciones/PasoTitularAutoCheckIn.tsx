import { Input } from '@/modulos/compartido/ui/entrada';

interface PropiedadesPasoTitularAutoCheckIn {
    titularNombre: string;
    titularIdentificacion: string;
    titularTelefono: string;
    titularEmail: string;
    onUpdate: (field: string, value: string) => void;
}

export const PasoTitularAutoCheckIn = ({
    titularNombre,
    titularIdentificacion,
    titularTelefono,
    titularEmail,
    onUpdate,
}: PropiedadesPasoTitularAutoCheckIn) => {
    return (
        <div className="flex flex-col gap-5 font-sans">
            <div className="border-b border-border/40 pb-3">
                <h3 className="text-lg font-black text-foreground">
                    Datos del Titular de la Reserva
                </h3>
                <p className="text-xs text-muted-foreground">
                    Verifique la información personal del responsable principal
                    de la estancia.
                </p>
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
                <div className="flex flex-col gap-1.5">
                    <label className="text-xs font-extrabold tracking-wider text-foreground uppercase">
                        Nombre Completo *
                    </label>
                    <Input
                        type="text"
                        value={titularNombre}
                        onChange={(e) =>
                            onUpdate('titularNombre', e.target.value)
                        }
                        className="rounded-2xl text-xs font-semibold"
                        required
                    />
                </div>

                <div className="flex flex-col gap-1.5">
                    <label className="text-xs font-extrabold tracking-wider text-foreground uppercase">
                        Cédula o Pasaporte *
                    </label>
                    <Input
                        type="text"
                        placeholder="Ej. 001-120590-0004W"
                        value={titularIdentificacion}
                        onChange={(e) =>
                            onUpdate('titularIdentificacion', e.target.value)
                        }
                        className="rounded-2xl text-xs font-semibold"
                        required
                    />
                </div>

                <div className="flex flex-col gap-1.5">
                    <label className="text-xs font-extrabold tracking-wider text-foreground uppercase">
                        Teléfono de Contacto *
                    </label>
                    <Input
                        type="tel"
                        value={titularTelefono}
                        onChange={(e) =>
                            onUpdate('titularTelefono', e.target.value)
                        }
                        className="rounded-2xl text-xs font-semibold"
                        required
                    />
                </div>

                <div className="flex flex-col gap-1.5">
                    <label className="text-xs font-extrabold tracking-wider text-foreground uppercase">
                        Correo Electrónico *
                    </label>
                    <Input
                        type="email"
                        value={titularEmail}
                        onChange={(e) =>
                            onUpdate('titularEmail', e.target.value)
                        }
                        className="rounded-2xl text-xs font-semibold"
                        required
                    />
                </div>
            </div>
        </div>
    );
};

export default PasoTitularAutoCheckIn;
