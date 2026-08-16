import { UserRound, Building2 } from 'lucide-react';
import { Button } from '@/modulos/compartido/ui/boton';
import type { PropiedadesSelectorTipoPersona } from '../interfaces/autenticacionInterfaces';

export const SelectorTipoPersona = ({
    tipoPersona,
    onSeleccionar,
}: PropiedadesSelectorTipoPersona) => {
    const isNatural = tipoPersona === 'natural';

    return (
        <div className="mb-4 grid grid-cols-2 gap-2 rounded-2xl border border-border/40 bg-muted/50 p-1">
            <Button
                type="button"
                variant={isNatural ? 'default' : 'ghost'}
                size="sm"
                onClick={() => onSeleccionar('natural')}
                className={
                    isNatural
                        ? 'rounded-xl text-xs font-extrabold tracking-wider uppercase shadow-xs'
                        : 'rounded-xl text-xs font-extrabold tracking-wider text-muted-foreground uppercase hover:text-foreground'
                }
            >
                <UserRound
                    className="mr-1.5 size-3.5 text-primary"
                    data-icon="inline-start"
                />
                Persona Natural
            </Button>
            <Button
                type="button"
                variant={!isNatural ? 'default' : 'ghost'}
                size="sm"
                onClick={() => onSeleccionar('juridica')}
                className={
                    !isNatural
                        ? 'rounded-xl text-xs font-extrabold tracking-wider uppercase shadow-xs'
                        : 'rounded-xl text-xs font-extrabold tracking-wider text-muted-foreground uppercase hover:text-foreground'
                }
            >
                <Building2
                    className="mr-1.5 size-3.5 text-primary"
                    data-icon="inline-start"
                />
                Empresa
            </Button>
        </div>
    );
};
