import { User } from 'lucide-react';
import { Input } from '@/modulos/compartido/ui/entrada';

interface PropiedadesCamposPersonaNatural {
    primerNombre: string;
    primerApellido: string;
    onChangeNombre: (val: string) => void;
    onChangeApellido: (val: string) => void;
    errorNombre?: string;
    errorApellido?: string;
}

export const CamposPersonaNatural = ({
    primerNombre,
    primerApellido,
    onChangeNombre,
    onChangeApellido,
    errorNombre,
    errorApellido,
}: PropiedadesCamposPersonaNatural) => {
    return (
        <div className="grid grid-cols-2 gap-3">
            <div className="flex flex-col gap-1">
                <label className="text-xs font-extrabold tracking-wider text-foreground uppercase">
                    Nombre
                </label>
                <div className="relative">
                    <User className="absolute top-3 left-3.5 size-4 text-muted-foreground" />
                    <Input
                        type="text"
                        placeholder="Su nombre"
                        required
                        value={primerNombre}
                        onChange={(e) => onChangeNombre(e.target.value)}
                        className="pl-10"
                    />
                </div>
                {errorNombre && (
                    <p className="text-xs font-semibold text-rose-500">
                        {errorNombre}
                    </p>
                )}
            </div>

            <div className="flex flex-col gap-1">
                <label className="text-xs font-extrabold tracking-wider text-foreground uppercase">
                    Apellido
                </label>
                <div className="relative">
                    <User className="absolute top-3 left-3.5 size-4 text-muted-foreground" />
                    <Input
                        type="text"
                        placeholder="Su apellido"
                        required
                        value={primerApellido}
                        onChange={(e) => onChangeApellido(e.target.value)}
                        className="pl-10"
                    />
                </div>
                {errorApellido && (
                    <p className="text-xs font-semibold text-rose-500">
                        {errorApellido}
                    </p>
                )}
            </div>
        </div>
    );
};
