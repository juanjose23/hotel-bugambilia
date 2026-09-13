import { OCASIONES_RAPIDAS } from '../../constants';

interface RestauranteOcasionesRapidasProps {
    alAgregar: (tag: string) => void;
    variante?: 'pagina' | 'modal';
}

export const RestauranteOcasionesRapidas = ({
    alAgregar,
    variante = 'pagina',
}: RestauranteOcasionesRapidasProps) => {
    const esModal = variante === 'modal';

    return (
        <div className="mb-2 flex flex-wrap gap-1.5">
            {OCASIONES_RAPIDAS.map((tag) => (
                <button
                    key={tag}
                    type="button"
                    onClick={() => alAgregar(tag)}
                    className={`cursor-pointer rounded-lg border border-border/50 transition-colors ${
                        esModal
                            ? 'bg-muted/50 px-2 py-0.5 text-[10px] font-medium text-muted-foreground hover:bg-muted hover:text-foreground'
                            : 'bg-muted/60 px-2.5 py-1 text-[11px] font-semibold text-muted-foreground hover:bg-muted hover:text-foreground'
                    }`}
                >
                    {esModal && '+ '}
                    {tag}
                </button>
            ))}
        </div>
    );
};

export default RestauranteOcasionesRapidas;
