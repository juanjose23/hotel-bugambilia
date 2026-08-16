import { Building2 } from 'lucide-react';
import { Input } from '@/modulos/compartido/ui/entrada';

interface PropiedadesCamposPersonaJuridica {
    razonSocial: string;
    onChangeRazonSocial: (val: string) => void;
    errorRazonSocial?: string;
}

export const CamposPersonaJuridica = ({
    razonSocial,
    onChangeRazonSocial,
    errorRazonSocial,
}: PropiedadesCamposPersonaJuridica) => {
    return (
        <div className="flex flex-col gap-1">
            <label className="text-xs font-extrabold tracking-wider text-foreground uppercase">
                Razón Social / Empresa
            </label>
            <div className="relative">
                <Building2 className="absolute top-3 left-3.5 size-4 text-muted-foreground" />
                <Input
                    type="text"
                    placeholder="Nombre legal de la empresa"
                    required
                    value={razonSocial}
                    onChange={(e) => onChangeRazonSocial(e.target.value)}
                    className="pl-10"
                />
            </div>
            {errorRazonSocial && (
                <p className="text-xs font-semibold text-rose-500">
                    {errorRazonSocial}
                </p>
            )}
        </div>
    );
};
