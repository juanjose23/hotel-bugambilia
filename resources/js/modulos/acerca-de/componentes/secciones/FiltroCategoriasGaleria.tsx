import { Button } from '@/modulos/compartido/ui/boton';

interface PropiedadesFiltroCategorias {
    categorias: readonly string[];
    categoriaActiva: string;
    onSeleccionarCategoria: (categoria: string) => void;
}

export function FiltroCategoriasGaleria({
    categorias,
    categoriaActiva,
    onSeleccionarCategoria,
}: PropiedadesFiltroCategorias) {
    return (
        <div className="mb-10 flex flex-wrap items-center justify-center gap-2">
            {categorias.map((categoria) => (
                <Button
                    key={categoria}
                    type="button"
                    variant={
                        categoriaActiva === categoria ? 'default' : 'outline'
                    }
                    size="sm"
                    onClick={() => onSeleccionarCategoria(categoria)}
                    className={
                        categoriaActiva === categoria
                            ? 'bg-bugambilia-600 font-bold text-white hover:bg-bugambilia-700'
                            : 'border-border/80 text-muted-foreground hover:text-foreground'
                    }
                >
                    {categoria}
                </Button>
            ))}
        </div>
    );
}
